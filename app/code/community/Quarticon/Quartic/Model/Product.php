<?php

// TODO: $product->getFinalPrice() zapycha pamięć.

class Quarticon_Quartic_Model_Product extends Mage_Core_Model_Abstract {

    const ITERATION_STEP = 25;
    
    protected $default_attribute_set_id = null;
    protected $_categories = array();
    protected $_imagesUrl = null;
    protected $_minQty = false;

    protected function getConfig() {
        return Mage::getModel('quartic/config');
    }
    
    protected function _getCollection() {
        $_product = Mage::getModel('catalog/product');
        $store = Mage::app()->getStore();
        $collection = $_product->getCollection()
                ->addStoreFilter($store->getStoreId());
        return $this->_addDisableFilters($collection);;
    }
    
    protected function _addDisableFilters($collection) {
        if (!$this->getConfig()->showDisabledProducts()) {
            $collection->addFieldToFilter('status', array('eq' => 1));
        }
        return $collection;
    }
    
    /**
     * If "min_qty" config value is greater than zero, qty is added to the products collection.
     */
    protected function _addQtyField($collection) {
        if ($this->getConfig()->getMinQty()) {
            $collection->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                null,
                'left'
            );
        }
        return $collection;
    }
    
    public function getCollectionCount() {
        return $this->_getCollection()->getSize();
    }

	protected function _getFinalCollection($page_num = 1, $page_size = 10, $product_id = false) {
		$collection = $this->_getCollection()
                ->setPage($page_num, $page_size)
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('category_ids')
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('sku');
        foreach ($additional_attributes as $code => $options) {
            $collection->addAttributeToSelect($code);
        }
		if ($product_id) {
			$collection->addFieldToFilter('entity_id', $product_id);
		}
        $collection = $this->_addAdditionalAttributes($collection);
        $collection = $this->_addQtyField($collection);
		return $collection;
	}
	
    public function getAll($page_num = 1, $page_size = 10) {
        $author_mapping = $this->getConfig()->getAuthorMapping();
        $_attribute = Mage::getModel('quartic/attribute');
        $additional_attributes = array();
        if (!empty($author_mapping)) {
            $additional_attributes[$author_mapping] = $_attribute->getOptionsByCode($author_mapping);
        }
        $collection = $this->_getFinalCollection($page_num, $page_size, false);
        $offers = array();
        $media = $this->getConfig()->addThumbs() ? $this->getMediaData($collection) : array();
        if (!empty($media)) {
            $this->_imagesUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
        }
		$product_items = $collection->getItems();
        foreach ($product_items as $id => $product) {
			$images = isset($media[$product->getId()]) ? $media[$product->getId()] : array();
			$configurable_id = $this->getConfigurableIdByChildId($product->getId());
			if ($configurable_id !== 0) {
				// Do not show configurable product children
				continue;
			}
			$offers[] = $this->handleProductToGetOffer($product, $images, $additional_attributes);
        }
        $collection->clear();
        unset($collection);
        unset($media);
        return $offers;
    }
	
	protected function _getSingleProduct($product_id) {
		$collection = $this->_getFinalCollection(1, 1, $product_id);
		$product = $collection->getFirstItem();
		if ($product->getId()) {
			return $product;
		}
		return false;
	}
	
	/*
     * Method that prepares one product to be appended to $offers table
     * 
     * $product - product to be processed
     * $images - object with media data
	 * $additional_attributes - definitions of additional product attributes
     * $rewrite_url - URL that rewrites standard product URL. If null then no rewrite is executed
     */
    protected function handleProductToGetOffer($product, $images, $additional_attributes = array()) {
		$category_ids = array_slice($product->getCategoryIds(), 0, 6);
		$offer = array(
			'id' => Mage::helper('quartic')->getProduct($product),
			'title' => $product->getName(),
			'price' => $this->getFinalPriceIncludingTax($product),
			'link' => $product->getProductUrl(),
			'categories' => $this->getCategories($category_ids),
			'status' => $this->_getStatus($product)
		);
		$special_price = $product->getSpecialPrice();
		if ($special_price) {
			$offer['old_price'] = $this->getPriceIncludingTax($product);
		}
		foreach ($images as $image) {
			$disabled = (bool) (($image['disabled'] !== null) ? $image['disabled'] : $image['disabled_default']);
			if (!$disabled) {
				$offer['thumb'] = $this->_imagesUrl . $image['file'];
				break;
			}
		}
		unset($images);
		if (!empty($author_mapping)) {
			$value = $product->getData($author_mapping);
			if (!empty($value)) {
				$options = $additional_attributes[$author_mapping];
				if (!empty($options)) {
					$offer['author'] = $options[$value];
				} else {
					$offer['author'] = $value;
				}
			}
			unset($value);
		}
		$product->clearInstance();
		unset($product);
        return $offer;
    }
    
    public function getMediaData($_productCollection) {
		if (is_array($_productCollection)) {
			$all_ids = $_productCollection;
		} else {
			$all_ids = $_productCollection->getAllIds();
		}
        $_mediaGalleryByProductId = array();
        if (!empty($all_ids)) {
            $_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
            $_read = Mage::getSingleton('core/resource')->getConnection('catalog_read');
            $_mediaGalleryData = $_read->fetchAll('
                    SELECT
                            main.entity_id, `main`.`value_id`, `main`.`value` AS `file`,
                            `value`.`position`, `value`.`disabled`,
                            `default_value`.`position` AS `position_default`,
                            `default_value`.`disabled` AS `disabled_default`
                    FROM `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery') . '` AS `main`
                            LEFT JOIN `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value') . '` AS `value`
                                    ON main.value_id=value.value_id AND value.store_id=' . Mage::app()->getStore()->getId() . '
                            LEFT JOIN `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value') . '` AS `default_value`
                                    ON main.value_id=default_value.value_id AND default_value.store_id=0
                    WHERE (
                            main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ') 
                            AND (main.entity_id IN (' . $_read->quote($all_ids) . '))
                            AND (value.disabled = 0 OR (value.disabled IS NULL AND `default_value`.`disabled` = 0))
		    GROUP BY main.entity_id

                    ORDER BY IF(value.position IS NULL, default_value.position, value.position) ASC    
            ');
            foreach ($_mediaGalleryData as $_galleryImage) {
                $k = $_galleryImage['entity_id'];
                unset($_galleryImage['entity_id']);
                if (!isset($_mediaGalleryByProductId[$k])) {
                    $_mediaGalleryByProductId[$k] = array();
                }
                $_mediaGalleryByProductId[$k][] = $_galleryImage;
            }
            unset($_mediaGalleryData);
        }
        return $_mediaGalleryByProductId;
    }

    public function getFinalPriceIncludingTax($product) {
        return Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 2);
    }

    public function getPriceIncludingTax($product) {
        return Mage::helper('tax')->getPrice($product, $product->getPrice(), 2);
    }

    protected function _addAdditionalAttributes($collection) {
        return $collection;
    }
    
    /**
     * Gets "min_qty" value from config.
     * 
     * @return int
     */
    protected function _getMinQty() {
        if ($this->_minQty === false) {
            $this->_minQty = $this->getConfig()->getMinQty();
        }
        return $this->_minQty;
    }

    /**
     * Gets status for product in XML feed.
     * If product is invisible, status equals 0.
     * If product stock qty is lower than "min_qty" config value, status equals 0.
     * Else, status equals 1.
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _getStatus($product) {
        return $product->isVisibleInSiteVisibility() && $product->isVisibleInCatalog() && ($this->_getMinQty() ? $product->getQty() >= $this->_getMinQty() : true);
    }
	
	/**
     * Gets id of a configurable product for specified child product
     * 
     * @param Mage_Catalog_Model_Product $child
     * @return int
     */
    public function getConfigurableIdByChildId($childId) {
        $ids = Mage::getSingleton('catalog/product_type_configurable')->getParentIdsByChild($childId);
        if (!empty($ids)) {
            return (int) array_shift($ids);
        }
        return 0;
    }
    
    protected function getCategories($categoryIds) {
        $categories = array();
        $categoryIdsToAdd = array();
        foreach ($categoryIds as $categoryId) {
            if (!isset($this->_categories[$categoryId])) {
                $categoryIdsToAdd[] = $categoryId;
            } else {
                $categories[$categoryId] = $this->_categories[$categoryId];
            }
        }
        if (!empty($categoryIdsToAdd)) {
            $collection = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToFilter('entity_id', $categoryIdsToAdd)
                    ->setPage(0, count($categoryIdsToAdd));
            foreach ($collection as $category) {
                $this->_categories[$category->getId()] = $category->getName();
                $categories[$category->getId()] = $category->getName();
            }
        }
        return $categories;
    }

}