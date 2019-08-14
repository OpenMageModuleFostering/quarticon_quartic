<?php

class Quarticon_Quartic_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init("quartic/history", "id");
    }
}
