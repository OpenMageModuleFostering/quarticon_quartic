<?php

class Quarticon_Quartic_FeedController extends Mage_Core_Controller_Front_Action {

    protected function getConfig() {
        return Mage::getModel('quartic/config');
    }

    protected function _startXML() {
        $hash = $this->getRequest()->getParam('hash');
        if ($hash == $this->getConfig()->getHash() && $this->getConfig()->isActive()) {
            return true;
        } else {
            return false;
        }
    }

    public function productsAction() {
        if ($this->_startXML()) {
            header("Content-Type:text/xml");
            $writer = new XMLWriter();
            $writer->openUri('php://output');
            $writer->startDocument('1.0', 'UTF-8');
            $writer->setIndent(true);
            $mem_writer = new XMLWriter();
            $mem_writer->openMemory();
            $mem_writer->setIndent(true);
            $writer->startElement('products');
            $_product = Mage::getModel('quartic/product');
            $count = $_product->getCollectionCount();
            $steps = ceil($count / Quarticon_Quartic_Model_Product::ITERATION_STEP);
            for ($step = 1; $step <= $steps; $step++) {
                $collection = $_product->getAll($step, Quarticon_Quartic_Model_Product::ITERATION_STEP);
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
                    $mem_writer->writeElement('status', $p['status'] ? '1' : '0');
                    $i = 0;
                    foreach ($p['categories'] as $categoryId => $categoryName) {
                        $i++;
                        $mem_writer->startElement('category_' . $i);
                        $mem_writer->writeAttribute('id', $categoryId); 
                        $mem_writer->writeRaw($categoryName);
                        $mem_writer->endElement();
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
            die();
        } else {
            $this->_redirect('/');
        }
    }

    public function ordersAction() {
        if ($this->_startXML()) {
            header("Content-Type:text/xml");
            $writer = new XMLWriter();
            $writer->openUri('php://output');
            $writer->startDocument('1.0', 'UTF-8');
            $writer->setIndent(true);
            $mem_writer = new XMLWriter();
            $mem_writer->openMemory();
            $mem_writer->setIndent(true);
            $writer->startElement('orders');
            $_order = Mage::getModel('quartic/order');
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
            die();
        } else {
            $this->_redirect('/');
        }
    }

}