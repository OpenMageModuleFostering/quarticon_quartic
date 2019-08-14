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
    public function productsAction()
    {
        //$this->getResponse()->setBody($responseAjax->toJson());
        if ($this->_startXML()) {
            require_once('Zend/Log.php');
			
			$storeId = $this->_getStoreId();
			$filename = 'xmlOutput_' . $storeId . '.xml';
			$filepath = Mage::getBaseDir('var') . "/quartic/" . $filename;
			$olderThan = 24 * 3600; //24h
			
			if(file_exists($filepath) && (time() - filemtime($filepath) < $olderThan)) { // not older than 24h
				header("Content-Type:text/xml");
				$contents = file_get_contents($filepath);
				echo $contents;
				$this->log();
				die();
			} elseif(file_exists($filepath) && (time() - filemtime($filepath) >= $olderThan)) { // file too old
				header("Error: 512 File Too old - " . date('c',filemtime($filepath)),true,512);
				$this->log();
				die();
			} elseif(!file_exists($filepath)) { //file not exists
				header("Error: 513 File not exists",true,513);
				$this->log();
				die();
			} else {
				header("Error: 514 Something is wrong",true,514); // nobody will never see this... that's sad
				$this->log();
				die();
			}
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
