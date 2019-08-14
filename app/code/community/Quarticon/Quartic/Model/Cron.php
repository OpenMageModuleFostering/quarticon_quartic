<?php

class Quarticon_Quartic_Model_Cron {

	public function refreshProductsFeed()
	{
		$log = false;
		if($log) Mage::log('Start',null,'quartic_cron.log',true);
		
		$stores = $this->getStoresFeedEnabled();
		foreach($stores as $storeId) {
			if($log) Mage::log('$storeId: ' . $storeId,null,'quartic_cron.log',true);
			$filename = 'xmlOutput_' . $storeId . '.xml';
			$filepath = Mage::getBaseDir('var') . "/quartic/" . $filename;
			
			ob_start();
			
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
			
			$time = microtime(true);
			$products = array();
			
			$data = array();
			// for proper products data in collection set current store by param
			Mage::app()->setCurrentStore($storeId);
			/**
			 * @var Quarticon_Quartic_Model_Product $_product
			 */
			$_product = Mage::getModel('quartic/product');
			$count = $_product->getCollectionCount();
			if($log) Mage::log('$count: ' . $count,null,'quartic_cron.log',true);
			
			$steps = ceil($count / $_product->getIterationStep($storeId));
			if($log) Mage::log('$steps: ' . $steps,null,'quartic_cron.log',true);
			for ($step = 1; $step <= $steps; $step++) {
				if($log) Mage::log('$step1: ' . $step,null,'quartic_cron.log',true);
				$products[$step] = $_product->getAll($step, $_product->getIterationStep($storeId));
			}
			$data['steps'] = $steps;
			$data['products'] = $products;
			$data['time'] = $time;
			
			$cache = $data;
			
				
			$steps = $cache['steps'];
			$products = $cache['products'];
			for ($step = 1; $step <= $steps; $step++) {
				if($log) Mage::log('$step2: ' . $step,null,'quartic_cron.log',true);
				$collection = $products[$step];
				foreach ($collection as &$p) {
					if($log) Mage::log('$productId: ' .  $p['id'],null,'quartic_cron.log',true);
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
		
			
			$xmlContent = ob_get_contents();
			ob_end_clean();
			if($log) Mage::log('przed zapisem',null,'quartic_cron.log',true);
			file_put_contents($filepath,$xmlContent);
			if($log) Mage::log('po zapisie',null,'quartic_cron.log',true);
		}
	}
	
	public function getStoresFeedEnabled()
	{
		$core_resource = Mage::getSingleton('core/resource');
		$tablename = $core_resource->getTableName('core_config_data');
		$connection = $core_resource->getConnection('core_read');
		$select = $connection->select()->from($tablename, array('scope', 'scope_id', 'path', 'value'));
		$select->where('path="quartic/config/active"');

		$result = array();
		$rowset = $connection->fetchAll($select);
		foreach($rowset as $row) {
			if($row['value'] == 1) {
				
				switch ($row['scope']) {
					case 'default':
						$result[] = Mage::app()->getDefaultStoreView()->getStoreId();
						break;

					case 'websites':
						$result[] = Mage::app()->getWebsite($row['scope_id'])->getDefaultGroup()->getDefaultStoreId();
						break;
						
                    default:
						$result[] = $row['scope_id'];
						break;
				}
			}
		}
		$result = array_unique($result);
		return $result;
	}

}