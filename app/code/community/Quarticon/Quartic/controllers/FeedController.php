<?php

class Quarticon_Quartic_FeedController extends Mage_Core_Controller_Front_Action
{

    /**
     * log file
     */
    const FEED_PROD_LOG = 'qon_prodfeed.log';

    /**
     * Helper
     * @var Quarticon_Quartic_Helper_Data
     */
    private $helper;

    /**
     * Log text
     * @param $txt
     */
    private function _log($txt)
    {
        $this->helper->log($txt, static::FEED_PROD_LOG);
    }

    /**
     * @return mixed
     */
    protected function getConfig()
    {
        return Mage::getModel('quartic/config');
    }

    protected function _startXML()
    {
        $hash = $this->getRequest()->getParam('hash');
        if ($hash == $this->getConfig()->getHash() && $this->getConfig()->isActive()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * View product feed action (if hash and config are active)
     */
    public function productsAction()
    {
        $storeId = $this->_getStoreId();
        $filename = 'xmlOutput_' . $storeId . '.xml';
        $filepath = Mage::getBaseDir('var') . "/quartic/" . $filename;
        $olderThan = 24 * 3600; //24h

        if(file_exists($filepath) && (time() - filemtime($filepath) < $olderThan)) { // not older than 24h
            header("Content-Type:text/xml");
            $contents = file_get_contents($filepath);
            echo $contents;
            $this->logHistory();
            die();
        } elseif(file_exists($filepath) && (time() - filemtime($filepath) >= $olderThan)) { // file too old
            header("Error: 512 File Too old - " . date('c',filemtime($filepath)),true,512);
            $this->logHistory();
            die();
        } elseif(!file_exists($filepath)) { //file not exists
            header("Error: 513 File not exists",true,513);
            $this->logHistory();
            die();
        } else {
            header("Error: 514 Something is wrong",true,514); // nobody will never see this... that's sad
            $this->logHistory();
            die();
        }
    }

    /**
     * View product feed action (if hash and config are active)
     */
    public function csvAction()
    {
        $storeId = $this->_getStoreId();
        $filename = 'csvFeed_' . $storeId . '.csv';
        $filepath = Mage::getBaseDir('var') . "/quartic/" . $filename;
        $olderThan = 24 * 3600; //24h
        if(file_exists($filepath) && (time() - filemtime($filepath) < $olderThan)) {
            // not older than 24h
            $this->outputFile($filepath);
            $this->_log('file older than 24h');
            $this->logHistory();
            die();
        } elseif(file_exists($filepath) && (time() - filemtime($filepath) >= $olderThan)) {
            // file too old
            $this->_log('file too old - generating');
            $feed = Mage::getModel('quartic/feed');
            $feed->refreshProductsFeed($storeId);
            $this->outputFile($filepath);
            $this->logHistory();
            die();
        } elseif(!file_exists($filepath)) {
            //file not exists
            $this->_log('file not exists, generating..');

            $feed = Mage::getModel('quartic/feed');
            $feed->refreshProductsFeed($storeId);

            $this->outputFile($filepath);
            $this->logHistory();
            die();
        } else {
            header("Error: 514 Something is wrong", true, 514); // nobody will never see this... that's sad
            $this->_log('unexpected error');
            $this->logHistory();
            die();
        }
    }

    /**
     * Print file
     * @param $filepath
     */
    private function outputFile($filepath) {
        header("Content-Type:text/csv");
        $contents = file_get_contents($filepath);
        echo $contents;
    }

    /**
     * View orders feed action (if hash and config are active)
     */
    public function ordersAction()
    {
        $storeId = $this->_getStoreId();
        header("Content-Type:text/xml");
        $writer = new Quarticon_Quartic_Model_Adapter_Writer();
        $writer->openUri('php://output');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);
        $mem_writer = new Quarticon_Quartic_Model_Adapter_Writer();
        $mem_writer->openMemory();
        $mem_writer->setIndent(true);
        $writer->startElement('orders');
        $writer->writeAttribute('xmlns', "http://cp.quarticon.com/docs/transactions/1.1/schema");
        $writer->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $writer->writeAttribute('xsi:schemaLocation', "http://quartic.pl/catalog/1.1/transactions
http://cp.quarticon.com/docs/transactions/1.1/schema/quartic_transactions_1.1
.xsd");
        $_order = Mage::getModel('quartic/order');
        $count = $_order->getCollectionCount($storeId);
        $steps = ceil($count / Quarticon_Quartic_Model_Order::ITERATION_STEP);
        for ($step = 1; $step <= $steps; $step++) {
            $collection = $_order->getAll($step, Quarticon_Quartic_Model_Order::ITERATION_STEP, $storeId);
            foreach ($collection as $o) {
                $mem_writer->startElement('order');
                $mem_writer->writeElement('transaction', $o['id']);
                $mem_writer->writeElement('timestamp', $o['timestamp']);
                $mem_writer->writeElement('user', $o['user']);
                $mem_writer->writeElement('product', $o['product']);
                $mem_writer->writeElement('quantity', $o['quantity']);
                $mem_writer->writeElement('price', $o['price']);
                $mem_writer->endElement();
                unset($o);
            }
            $batch_xml_string = $mem_writer->outputMemory(true);
            $writer->writeRaw($batch_xml_string);
            unset($collection);
        }
        unset($mem_writer);
        $writer->endElement();
        $writer->endDocument();
        die();
    }

    /**
     * Log feed action to quartic_history model (since 0.3.4)
     */
    public function logHistory()
    {
        $model = Mage::getModel('quartic/history');
        $storeCode = $this->getRequest()->getParam('___store');
        $store = Mage::getModel("core/store")->load($storeCode);
        $data = array('store_id' => $store->getId());
        $model->setData($data);
        $model->save();
    }

    /**
     * Get store id from request
     * @return int
     */
    private function _getStoreId() {
        $this->helper = Mage::helper('quartic');
        return $this->helper->getStoreId();
    }
}
