<?php

/**
 * Block used to debug api results.
 * Do not use in release code.
 */
class Quarticon_Quartic_Block_Adminhtml_Api_Dump extends Mage_Adminhtml_Block_Widget_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quartic/api/dump.phtml');
    }

    public function getResult()
    {
        return array();

        //$status_array = Mage::getStoreConfig("quartic/frames", Mage::app()->getStore());
        /* @var $frames Quarticon_Quartic_Model_Placement */
        //$frames = Mage::getModel('quartic/placement');
        //return $frames->apiLoad();
    }
}
