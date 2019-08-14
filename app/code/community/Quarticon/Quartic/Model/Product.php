<?php

// TODO: $product->getFinalPrice() zapycha pamięć.

class Quarticon_Quartic_Model_Product extends Mage_Core_Model_Abstract
{

    const ITERATION_STEP_DEFAULT = 250;

    protected $default_attribute_set_id = null;
    protected $_categories = array();
    protected $_imagesUrl = null;
    protected $_minQty = false;
    protected $_config = null;
    protected $_collectedConfigurablePrices = array();
    protected $_collectedGroupedPrices = array();
    protected $joinType = 'inner';
    protected $iterationStep = null;
    protected $mapping = null;

    protected function getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = Mage::getModel('quartic/config');
        }
        return $this->_config;
    }

    /**
     * Number of products per feed query
     * @param integer $store_id
     */
    public function getIterationStep($store_id = null)
    {
        if (is_null($this->iterationStep)) {
            $this->iterationStep = (int) Mage::getStoreConfig('quartic/config/feed/product/iteration_step', $store_id);
        }
        if (empty($this->iterationStep)) {
            $this->iterationStep = self::ITERATION_STEP_DEFAULT;
        }
        return $this->iterationStep;
    }

    /**
     * Get products
     * @param bool $simple simple visibility filter rules?
     * @return mixed
     */
    protected function _getCollection($simple = false)
    {
        $_product = Mage::getModel('catalog/product');
        $storeId = $this->_getStoreId();

        $collection = $_product
            ->setStoreId($storeId)
            ->getCollection()
            ->addStoreFilter($storeId)
        ;
        $updated = Mage::app()->getRequest()->getParam('updated');
        if ($updated) {
            $collection->addAttributeToFilter('updated_at', array('gteq' => $updated));
        }
        $productIds = Mage::app()->getRequest()->getParam('product');
        if ($productIds) {
            $collection->addAttributeToFilter('entity_id', explode(',',$productIds));
        }

        $collection = $this->_addQtyField($collection);
        if (!$simple) {
            $collection = $this->_addDisableFilters($collection);
        } else {
            $collection
                ->addAttributeToFilter('type_id', array(
                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                    Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
                    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
                    'downloadable'
                ))
                ->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        }
		return $collection;
    }

    /**
     * Define visibility filters from config settings
     * @param $collection
     * @return mixed
     */
    protected function _addDisableFilters($collection)
    {
        if (!$this->getConfig()->showDisabledProducts()) {
            $collection->addFieldToFilter('status', array('eq' => 1));
        }

        // $allowVisibility = $this->getConfig()->getVisibility();
        // $collection->addAttributeToFilter('visibility',array('in' => $allowVisibility));

        return $collection;
    }

    /**
     * If "min_qty" config value is greater than zero, qty is added to the products collection.
     */
    protected function _addQtyField($collection)
    {
		$storeId = $this->_getStoreId();
		$websiteId = ($storeId == 0) ? 1 : Mage::app()->getStore($storeId)->getWebsiteId();
        if ($this->getConfig()->getMinQty()) {
            $collection->joinField(
                'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', null, 'left'
            );
        }
        $collection->joinField('stock_status','cataloginventory/stock_status','stock_status',
            'product_id=entity_id', array(
                'stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK,
                'website_id' => $websiteId
            ));
        return $collection;
    }

    /**
     * Get collection items count
     * @param bool $simple simple visibility filter rules?
     * @return mixed
     */
    public function getCollectionCount($simple = false)
    {
        return $this->_getCollection($simple)->getSize();
    }
    
    /*
     * Get's product collection with attribute mapping
     */
    protected function _getFinalCollection($page_num = 1, $page_size = 10, $product_id = false)
    {
        $joinType = $this->joinType;

        $additional_attributes = $this->getMapping();

        $collection = $this->_getCollection()
            ->setPage($page_num, $page_size)
            ->addAttributeToSelect('price', $joinType)
            ->addAttributeToSelect('special_price', $joinType)
            ->addAttributeToSelect('name', $joinType)
            ->addAttributeToSelect('category_ids', $joinType)
            ->addAttributeToSelect('visibility', $joinType)
            ->addAttributeToSelect('status', $joinType)
            ->addAttributeToSelect('url_path', $joinType)
            ->addAttributeToSelect('sku', $joinType);
        foreach ($additional_attributes as $code => $option) {
            //Join type does not work here
            $collection->addAttributeToSelect($option);
        }
        if ($product_id) {
            $collection->addFieldToFilter('entity_id', $product_id);
        }
        if ($this->getConfig()->addThumbs()) {
            $collection->addAttributeToSelect('image', $joinType);
        }
        $collection = $this->_addAdditionalAttributes($collection);
        $collection = $this->addImageAttributeToCollection($collection);
		
        return $collection;
    }

    public function getAll($page_num = 1, $page_size = 10)
    {
        $additional_attributes = $this->getMapping();
        $collection = $this->_getFinalCollection($page_num, $page_size, false);
        $offers = array();
        $product_items = $collection->getItems();

        $showConfigurableChilds = $this->getConfig()->getShowConfigurableChilds();
        $configurableChildPrice = $this->getConfig()->getConfigurableChildPrice();
        $configurableChildImage = $this->getConfig()->getConfigurableChildImage();
        $configurableChildRedirect = $this->getConfig()->getConfigurableChildRedirect();
        $map = $this->getMapping();
        $groupedChildPrice = $this->getConfig()->getGroupedChildPrice();
        $groupedChildImage = $this->getConfig()->getGroupedChildImage();
        $groupedChildRedirect = $this->getConfig()->getGroupedChildRedirect();
        foreach ($product_items as $id => $product) {
			if($product->getTypeId() == 'simple') {
				$configurable_id = $this->getConfigurableIdByChildId($product->getId());
				if ($configurable_id !== 0) {
					if($showConfigurableChilds == 0) { //$showConfigurableChilds == 0 - do not show child products of configurable
						continue;
					} elseif($configurableChildPrice == 1) { //$configurableChildPrice == 1 - get child product price from parent config
						if(!isset($this->_collectedConfigurablePrices[$configurable_id])) {
							$configurableProduct = Mage::getModel('catalog/product')->load($configurable_id);
							$this->_collectedConfigurablePrices[$configurable_id] = $this->getFinalPriceIncludingTax($configurableProduct,true);
						}
						// temporary force price for simple product that should show price from it's parent configurable
						$product->setFinalPrice($this->_collectedConfigurablePrices[$configurable_id]);
						$product->setSpecialPrice(null);
						$product->setData($map['old_price'],null);
					}
					if($configurableChildImage == 1) {
						if(!isset($configurableProduct)) $configurableProduct = Mage::getModel('catalog/product')->load($configurable_id);
						// temporary force image for simple product that should show image from it's parent configurable
						$product->setData('image',$configurableProduct->getData('image'));
					}
					if($configurableChildRedirect == 1) {
						if(!isset($configurableProduct)) $configurableProduct = Mage::getModel('catalog/product')->load($configurable_id);
						// temporary force url for simple product that should be redirected to it's parent configurable
						$product->setData('url',$configurableProduct->getProductUrl());
					}
				}
				$grouped_id = $this->getGroupedIdByChildId($product->getId());
				if ($grouped_id !== 0) {
					if($groupedChildPrice == 1) { //$groupedChildPrice == 1 - get child product price from parent config
						if(!isset($this->_collectedGroupedPrices[$grouped_id])) {
							$groupedProduct = Mage::getModel('catalog/product')->load($grouped_id);
							$this->_collectedGroupedPrices[$grouped_id] = $this->getFinalPriceIncludingTax($groupedProduct,true);
						}
						// temporary force price for simple product that should show price from it's parent configurable
						$product->setFinalPrice($this->_collectedGroupedPrices[$grouped_id][$product->getId()]);
						$product->setSpecialPrice(null);
						$product->setData($map['old_price'],null);
					}
					if($groupedChildImage == 1) {
						// temporary force image for simple product that should show image from it's parent configurable
						$product->setData('image',$groupedProduct->getData('image'));
					}
					if($groupedChildRedirect == 1) {
						// temporary force url for simple product that should be redirected to it's parent grouped
						$product->setData('url',$groupedProduct->getProductUrl());
					}
				}
			}
            $offer_item = $this->handleProductToGetOffer($product, $additional_attributes);
            if($offer_item) $offers[] = $offer_item;
        }
        $collection->clear();
        unset($collection);
        unset($media);
        return $offers;
    }

    /**
     * Get simple (id, url) products list array
     * @param int $page_num
     * @param int $page_size
     * @return array
     */
    public function getSimpleProductList($page_num = 1, $page_size = 10) {
        $collection = $this->_getCollection(true)
            ->setPage($page_num, $page_size)
            ->addAttributeToSelect('url_path', $this->joinType);
        $product_items = $collection->getItems();

        $feed = array();
        $model = Mage::getModel('catalog/product');
        $helper = Mage::helper('quartic');
        foreach ($product_items as $item) {
            $id = $helper->getProduct($item);
            $product = $model->load($id);
            if($product->getData('quarticon_exclude') == 1) {
                continue;
            }
            $feed[] = array(
                'id' => $id,
                'link' => $item->getProductUrl()
            );
        }
        return $feed;
    }
    
    protected function _getSingleProduct($product_id)
    {
        $collection = $this->_getFinalCollection(1, 1, $product_id);
        $product = $collection->getFirstItem();
        if ($product->getId()) {
            return $product;
        }
        return false;
    }

    /**
     * Method that prepares one product to be appended to $offers table
     * 
     * $product - product to be processed
     * $images - object with media data
     * $additional_attributes - definitions of additional product attributes
     * $rewrite_url - URL that rewrites standard product URL. If null then no rewrite is executed
     */
    protected function handleProductToGetOffer($product, $additional_attributes = array())
    {
        if($product->getData('quarticon_exclude') == 1) return false;

        $category_ids = array_slice($product->getCategoryIds(), 0, 6);
        $map = $this->getMapping();

        $price = $product->getData($map['price']);
        $product->setPrice($price); // for proper getFinalPrice calculation

        /**
         * Map fields
         */
        $offer = array(
            'id' => Mage::helper('quartic')->getProduct($product),
            'title' => !empty($map['title']) ? $product->getData($map['title']) : '',
            'price' => $this->getFinalPriceIncludingTax($product),
            'old_price' => !empty($map['old_price']) ? $product->getData($map['old_price']) : '',
            'link' => $product->getProductUrl(),
            'categories' => $this->getCategories($category_ids),
            'status' => $this->_getStatus($product)
        );
        /**
         * Map custom fields
         */
        $i = 1;
        while ($i <= 5) {
            $key = 'custom' . $i;
            if (!empty($map[$key])) {
                $offer[$key] = $product->getData($map[$key]);
            }
            $i++;
        }
        $special_price = $product->getSpecialPrice();
        if ($special_price) {
            $offer['old_price'] = $this->getPriceIncludingTax($product);
        }
        if ($this->getConfig()->addThumbs()) {
            try {
                $offer['thumb'] = Mage::helper('catalog/image')->init($product, 'image',$product->getData('image'))->__toString();
            } catch (Exception $e) {
                
            }
        }

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
        if (version_compare(Mage::getVersion(), '1.5.1.0', '>='))	{
            $product->clearInstance();
        }
        unset($product);
        return $offer;
    }

    /**
     * get attributes map for product feed via handleProductToGetOffer method
     * 
     * @return Array
     * 
     */
    public function getMapping()
    {
        if (is_null($this->mapping)) {
			$mapping = array();
			$collection = Mage::getModel('quartic/maps')->getCollection();
			foreach ($collection as $element) {
				$attr = $element->getData('magento_attribute');
				if (!empty($attr)) {
					$mapping[$element->getData('quartic_attribute')] = $attr;
				}
			}
            $this->mapping = $mapping;
        }
        return $this->mapping;
    }

    public function getFinalPriceIncludingTax($product,$collect = false)
    {
        if($product->getTypeId() == 'configurable') {
            $configurablePriceType = $this->getConfig()->getConfigurablePrice();
            if($configurablePriceType > 0) { // get price from configurable product
                if(!isset($this->_collectedConfigurablePrices[$product->getId()])) { // configurable product prices could be already collected
                    $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                    $pricesByAttributeValues = array();
                    $basePrice = $product->getFinalPrice();
                    foreach ($attributes as $attribute){
                        $prices = $attribute->getPrices();
                        foreach ($prices as $price){
                            if ($price['is_percent']){ //if the price is specified in percents
                                $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                            }
                            else { //if the price is absolute value
                                $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                            }
                        }
                    }

                    $totalPrices = array();
                    $simple = $product->getTypeInstance()->getUsedProducts();
                    //loop through the products
                    foreach ($simple as $sProduct){
                        $optionsPrice = $basePrice;
                        //loop through the configurable attributes
                        foreach ($attributes as $attribute){
                            $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
                            if (isset($pricesByAttributeValues[$value])){
                                $optionsPrice += $pricesByAttributeValues[$value];
                            }
                        }
                        $totalPrices[$sProduct->getId()] = $optionsPrice;
                    }
                } else {
                    $totalPrices = $this->_collectedConfigurablePrices[$product->getId()];
                }

                $minPrice = Mage::helper('tax')->getPrice($product, min($totalPrices), 2);
                $maxPrice = Mage::helper('tax')->getPrice($product, max($totalPrices), 2);
            }

            if(!$collect) {
                switch($configurablePriceType) {
                    case 0:
                        return Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 2);
                        break;
                    case 1:
                        return $minPrice;
                        break;
                    case 2:
                        return $maxPrice;
                        break;
                    case 3:
                        if($maxPrice - $minPrice) {
                            return Mage::helper('tax')->getPrice($product, $minPrice, 2) . ' - ' . Mage::helper('tax')->getPrice($product, $maxPrice, 2);
                        } else {
                            return $maxPrice;
                        }
                        break;
                }
            } else { //just collect all prices and return it as associative array
                $result = $totalPrices;
                $result['minPrice'] = $minPrice;
                $result['maxPrice'] = $maxPrice;
                return $result;
            }
        } elseif($product->getTypeId() == 'grouped') {
            $groupedPriceType = $this->getConfig()->getGroupedChildPrice();
            $totalPrices = array();
            $simple = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($simple as $sProduct){
                $totalPrices[$sProduct->getId()] = $sProduct->getFinalPrice();
            }

            $minPrice = Mage::helper('tax')->getPrice($product, min($totalPrices), 2);
            $maxPrice = Mage::helper('tax')->getPrice($product, max($totalPrices), 2);

            if(!$collect) {
                switch($groupedPriceType) {
                    case 0:
                        return $minPrice;
                        break;
                    case 1:
                        return $maxPrice;
                        break;
                }
            } else { //just collect all prices and return it as associative array
                $result = $totalPrices;
                $result['minPrice'] = $minPrice;
                $result['maxPrice'] = $maxPrice;
                return $result;
            }
        } else {
            return Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 2);
        }
    }

    public function getPriceIncludingTax($product)
    {
        return Mage::helper('tax')->getPrice($product, $product->getPrice(), 2);
    }

    protected function _addAdditionalAttributes($collection)
    {
        return $collection;
    }

    /**
     * Gets "min_qty" value from config.
     *
     * @return int
     */
    protected function _getMinQty()
    {
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
     * @deprecated deprecated since version 0.5.6 with status attribute mapping
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _getStatus($product)
    {
        if($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped') {
            return $product->isVisibleInSiteVisibility() && $product->isVisibleInCatalog();
        } else {
			return $product->isVisibleInSiteVisibility() && $product->isVisibleInCatalog() && ($this->_getMinQty() ? $product->getQty() >= $this->_getMinQty() : true);
		}
    }

    /**
     * Gets id of a configurable product for specified child product
     *
     * @param Mage_Catalog_Model_Product $child
     * @return int
     */
    public function getConfigurableIdByChildId($childId)
    {
        $ids = Mage::getSingleton('catalog/product_type_configurable')->getParentIdsByChild($childId);
        if (!empty($ids)) {
            return (int) array_shift($ids);
        }
        return 0;
    }

    /**
     * Gets id of a grouped product for specified child product
     *
     * @param int $childId
     * @return int
     */
    public function getGroupedIdByChildId($childId)
    {
        $ids = Mage::getSingleton('catalog/product_type_grouped')->getParentIdsByChild($childId);
        if (!empty($ids)) {
            return (int) array_shift($ids);
        }
        return 0;
    }

    protected function getCategories($categoryIds)
    {
        $joinType = $this->joinType;
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
                ->addAttributeToSelect('name', $joinType)
                ->addAttributeToFilter('entity_id', $categoryIdsToAdd)
                ->setPage(0, count($categoryIdsToAdd));
            foreach ($collection as $category) {
                $this->_categories[$category->getId()] = $category->getName();
                $categories[$category->getId()] = $category->getName();
            }
        }
        return $categories;
    }


    public function addImageAttributeToCollection($_productCollection) {
        $_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'image')->getAttributeId();
        $core_resource = Mage::getSingleton('core/resource');
        $_read = $core_resource->getConnection('core_read');

        $count = count($_productCollection->getItems());
        if(!$count) {
            return $_productCollection;
        }

        $all_ids = array();
        foreach($_productCollection->getItems() as $item) {
            $all_ids[] = $item->getId();
        }

        $query = 'SELECT `entity_id`,`value` FROM `' . $core_resource->getTableName('catalog_product_entity_varchar') . '` WHERE `attribute_id` = ' . $_read->quote($_mediaGalleryAttributeId) . ' AND `entity_id` IN (' . $_read->quote($all_ids) . ');';
        $_imagesData = $_read->fetchAll($query);

        $images = array();
        foreach($_imagesData as $_imageData) {
            $images[$_imageData['entity_id']] = $_imageData['value'];
        }

        foreach ($_productCollection->getItems() as $_product) {
            $_productId = $_product->getData('entity_id');
            if (!empty($images[$_productId])) {
                $_product->setData('image', $images[$_productId]);
            }
        }
        unset($_imagesData);

        return $_productCollection;
    }

    /**
     * Get store id (from request parameter or detect)
     * @return int
     */
    private function _getStoreId()
    {
        $params = Mage::app()->getRequest()->getParams();
        if(isset($params['store'])) {
            $storeId = (is_numeric($params['store'])) ? (int)$params['store'] : Mage::getModel('core/store')->load($params['store'], 'code')->getId();
        } else {
            $storeId = Mage::app()->getStore()->getId();
        }

        return $storeId;
    }
}
