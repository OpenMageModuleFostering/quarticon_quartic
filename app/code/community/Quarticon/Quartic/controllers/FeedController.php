<?php

class Quarticon_Quartic_FeedController extends Mage_Core_Controller_Front_Action
{

    const FEED_PROD_LOG = 'qon_prodfeed.log';

    protected $logFeed = true;

    protected function _log($message)
    {
        if ($this->logFeed) {
            Mage::log($message, null, self::FEED_PROD_LOG);
        }
    }

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
    public function cacheproductsAction($refresh = false)
    {
		$storeId = $this->_getStoreId();
        $currentStoreId = Mage::app()->getStore()->getId();

        $cache = Mage::app()->getCacheInstance();
        if ($refresh) {
            $cache->cleanType('quartic-products-' . $storeId);
        }
        $temp = $cache->load('quartic-products-' . $storeId);
        $time = microtime(true);
        $products = array();
        if ($temp !== false) {
            $cache = unserialize($temp);
        } else {
            $data = array();
            // for proper products data in collection set current store by param
            // except for param storeId = 0 which mean it's admin area
            if($currentStoreId != $storeId && $storeId != 0) Mage::app()->setCurrentStore($storeId);
            /**
             * @var Quarticon_Quartic_Model_Product $_product
             */
            $_product = Mage::getModel('quartic/product');
            $count = $_product->getCollectionCount();
            $steps = ceil($count / $_product->getIterationStep($storeId));
            $this->_log("Feed start: {$count} products in {$steps} slices");
            for ($step = 1; $step <= $steps; $step++) {
                $this->_log("Feed slice: {$step} of {$steps}");
                $products[$step] = $_product->getAll($step, $_product->getIterationStep($storeId));
            }
            $data['steps'] = $steps;
            $data['products'] = $products;
            $data['time'] = $time;
            $cache->save(serialize($data), 'quartic-products-' . $storeId, array(), 3600);
            $cache = $data;
            if($currentStoreId != $storeId && $storeId != 0) Mage::app()->setCurrentStore($currentStoreId);
        }

        return $cache;
    }

    /**
     * View product feed action (if hash and config are active)
     */
    public function productsAction()
    {
        //$this->getResponse()->setBody($responseAjax->toJson());
        if ($this->_startXML()) {
            require_once('Zend/Log.php');

            header("Content-Type:text/xml");
			
			if($this->getConfig()->getUseFileCache()) {
				$storeId = $this->_getStoreId();
				$filename = 'xmlOutput_' . $storeId . '.xml';
				$filepath = Mage::getBaseDir('var') . "/quartic/" . $filename;
				
				if(file_exists($filepath) && (time() - filemtime($filepath) < 24 * 3600)) { // not older than 24h
					$contents = file_get_contents($filepath);
					header("Feed-From:cachefile");
					echo $contents;
					die();
				}
			}
			
			
            $writer = new Quarticon_Quartic_Model_Adapter_Writer();
            $writer->openUri('php://output');
            $writer->startDocument('1.0', 'UTF-8');
            $writer->setIndent(true);
            $mem_writer = new Quarticon_Quartic_Model_Adapter_Writer();
            $mem_writer->openMemory();
            $mem_writer->setIndent(true);
            $writer->startElement('products');
            $writer->writeAttribute('xmlns', "http://alpha.quarticon.com/docs/catalog/1.0/schema");
            $writer->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
            $writer->writeAttribute('xsi:schemaLocation', "http://quartic.pl/catalog/1.0/schema http://alpha.quarticon.com/docs/catalog/1.0/schema/quartic_catalog_1.0.xsd");
            if ($this->getRequest()->getParam('refresh') == 1) {
                $cache = $this->cacheproductsAction('refresh');
            } else {
                $cache = $this->cacheproductsAction();
            }
            $steps = $cache['steps'];
            $products = $cache['products'];
            for ($step = 1; $step <= $steps; $step++) {
                $collection = $products[$step];
                foreach ($collection as &$p) {
                    $mem_writer->startElement('product');
                    $mem_writer->writeElement('id', $p['id']);
                    $mem_writer->startElement('title');
                    $mem_writer->writeCData($p['title']);
                    $mem_writer->endElement();
                    $mem_writer->startElement('link');
                    $mem_writer->writeCData($p['link']);
                    $mem_writer->endElement();
                    if (isset($p['thumb'])) {
                        $mem_writer->startElement('thumb');
                        $mem_writer->writeCData($p['thumb']);
                        $mem_writer->endElement();
                    }
                    $mem_writer->writeElement('price', $p['price']);
                    if (isset($p['old_price'])) {
                        $mem_writer->writeElement('old_price', $p['old_price']);
                    }
                    if (isset($p['author'])) {
                        $mem_writer->startElement('author');
                        $mem_writer->writeCData($p['author']);
                        $mem_writer->endElement();
                    }
                    $mem_writer->writeElement('status', !empty($p['status']) ? '1' : '0');
                    $i = 0;
                    foreach ($p['categories'] as $categoryId => $categoryName) {
                        $i++;
                        $mem_writer->startElement('category_' . $i);
                        $mem_writer->writeAttribute('id', $categoryId);
                        $mem_writer->writeRaw($categoryName);
                        $mem_writer->endElement();
                    }

                    $i = 1;
                    while ($i <= 5) {
                        if (isset($p['custom' . $i])) {
                            $mem_writer->writeElement('custom' . $i, $p['custom' . $i]);
                        }
                        $i++;
                    }

                    $mem_writer->endElement();
                    unset($p);
                }

                $batch_xml_string = $mem_writer->outputMemory(true);
                $writer->writeRaw($batch_xml_string);
                unset($collection);
            }

            unset($mem_writer);
            $writer->endElement();
            $writer->endDocument();
            $this->log();
            die();
        } else {
            $this->_redirect('/');
        }
    }

    /**
     * View orders feed action (if hash and config are active)
     */
    public function ordersAction()
    {

        require_once('Zend/Log.php');

        if ($this->_startXML()) {
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
        } else {
            $this->_redirect('/');
        }
    }

    /**
     * Log feed action to quartic_history model (since 0.3.4)
     */
    public function log()
    {
        $model = Mage::getModel('quartic/history');
        $storeCode = $this->getRequest()->getParam('___store');
        $store = Mage::getModel("core/store")->load($storeCode);
        $data = array('store_id' => $store->getId());
        $model->setData($data);
        $model->save();
    }
	
	private function _getStoreId()
	{
		$params = Mage::app()->getRequest()->getParams();
		if(isset($params['store'])) {
			$storeId = (is_numeric($params['store'])) ? (int)$params['store'] : Mage::getModel('core/store')->load($params['store'], 'code')->getId();
		} else {
			$storeId = Mage::app()->getStore()->getId();
		}
		
		if($storeId == 0) return Mage::app()->getDefaultStoreView()->getStoreId();
		
		return $storeId;
	}
}
