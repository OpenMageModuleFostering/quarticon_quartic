<?php

class Quarticon_Quartic_Adminhtml_ApiController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        return $this;
    }

    public function placementsAction()
    {		
		$stores = Mage::app()->getStores();
        
        /* @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');

        try {
            /* @var $frames Quarticon_Quartic_Model_Placement */
            $frames = Mage::getModel('quartic/placement');
			
			$deleteResult = $frames->deleteAll();
			if($deleteResult) {
				$loaded = $frames->apiLoad(0); // for default config value
				foreach($stores as $store) {
					$loaded = $frames->apiLoad($store->getId());
				}
				
				$session->addSuccess(Mage::helper('quartic')->__('The placements have been synchronised.'));
			} else {
                $session->addError($message);
			}
        } catch (Exception $e) {
            foreach (explode("\n", $e->getMessage()) as $message) {
                $session->addError($deleteResult['message']);
            }
        }
        return $this->_redirectReferer();
    }

    /**
     * Send orders feed action
     */
    public function prepareordersAction()
    {
        $helper = Mage::helper('quartic');

        /* @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');

        /* @var $api Quarticon_Quartic_Model_Client_Api */
        $api = Mage::getModel('quartic/client_api');
		
		$storeCode = $this->getRequest()->getParam('store');
		$websiteCode = $this->getRequest()->getParam('website');
		$storeId = $this->_getStoreId();

        try {
            $_order = Mage::getModel('quartic/order');
            $writer = new Quarticon_Quartic_Model_Adapter_Writer();
            $uri = $_order->getFilePath();
            touch($uri);
            $writer->openUri(realpath($uri));
            //$writer->openUri('php://output');
            $writer->startDocument('1.0', 'UTF-8');
            $writer->setIndent(true);
            $mem_writer = new Quarticon_Quartic_Model_Adapter_Writer();
            $mem_writer->openMemory();
            $mem_writer->setIndent(true);
            $writer->startElement('orders');
            $count = $_order->getCollectionCount();
            $steps = ceil($count / Quarticon_Quartic_Model_Order::ITERATION_STEP);
            for ($step = 1; $step <= $steps; $step++) {
                $collection = $_order->getAll($step, Quarticon_Quartic_Model_Order::ITERATION_STEP);
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

            $hash = Mage::getStoreConfig("quartic/config/hash", $storeId);
            $data = array(
                'url' => Mage::getUrl('quartic/feed/orders', array('hash' => $hash, 'store' => $storeId)),
            );
            $helper->log('POST transactions');
            $helper->log(var_export(array('data' => $data), true));
            $new_trans = $api->post('transactions', array('data' => $data));
            $helper->log(var_export($new_trans, true));

            $session->addSuccess(Mage::helper('quartic')->__('Transaction feed is ready for sync.'));
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        return $this->_redirect('adminhtml/system_config/edit/section/quartic',array('store'=>$storeCode,'website'=>$websiteCode));
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

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/quartic');
    }
}
