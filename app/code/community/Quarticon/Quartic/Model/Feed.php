<?php

class Quarticon_Quartic_Model_Feed
{
    /**
     * helper
     * @var Quarticon_Quartic_Helper_Data
     */
    private $helper;

    /**
     * Log text
     * @var $txt
     */
    private function log($txt)
    {
        $this->helper->log($txt);
    }

    /**
     * Generate store product feed
     * @param $storeId
     */
    public function refreshProductsFeed($storeId)
    {
        $this->helper = Mage::helper('quartic');
        $this->log('Start, storeId: ' . $storeId);

        $dir = Mage::getBaseDir('var') . '/quartic';
        $filename = 'csvFeed_' . $storeId . '.csv';
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        $filepath = $dir . '/' . $filename;
        $products = array();
        file_put_contents($filepath, '"id";"link"');

        // for proper products data in collection set current store by param
        Mage::app()->setCurrentStore($storeId);
        $_product = Mage::getModel('quartic/product');
        $count = $_product->getCollectionCount(true);
        $this->log('collection count: ' . $count);

        $steps = ceil($count / $_product->getIterationStep($storeId));
        $this->log('steps: ' . $steps);

        // pobierz produkty paczkami
        $this->log('iteration step: ' . $_product->getIterationStep($storeId));
        for ($step = 1; $step <= $steps; $step++) {
            $this->log('step: ' . $step);
            $products[$step] = $_product->getSimpleProductList($step, $_product->getIterationStep($storeId));

            // po pobraniu paczki wypisz do pliku
            $collection = $products[$step];
            foreach ($collection as &$p) {
                $this->log('productId: ' . $p['id']);
                file_put_contents($filepath, "\n" . '"' . $p['id'] . '";"' . $p['link'] . '"', FILE_APPEND);
                unset($p);
            }
            unset($collection);
        }

        $this->log('Koniec');
    }

    /**
     * Send event "products catalog is ready" to quartic
     * @param bool|int $storeId
     */
    public function sendCatalogEvent($storeId = false)
    {
        $helper = Mage::helper('quartic');
        $api = Mage::getModel('quartic/client_api');
        if (!$storeId) {
            $storeId = 0;
        }
        $helper->log('sendCatalogEvent, storeId: ' . $storeId);
        if ($helper->getStoreScopeData($storeId, 'catalog')) {
            $this->log('Catalog url already sent: ' . $storeId);
            return;
        }

        // get catalog type
        $catalogTypeId = $api->catalogType('Crawler (csv)');
        $helper->log('Detected catalog type id: ' . $catalogTypeId);

//        $hash = Mage::getStoreConfig("quartic/config/hash", $storeId);
        $data = array(
            'name' => 'Magento crawler url',
            'url' => Mage::getUrl('quartic/feed/csv', array('store' => $storeId)),
            'typeId' => $catalogTypeId,
        );

        // send catalog url
        try {
            $result = $api->post('catalogs', array('data' => $data));
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $helper->setStoreScopeData($storeId, 'catalog', true, true);
    }
}