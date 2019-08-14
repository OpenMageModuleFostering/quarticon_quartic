<?php

class Quarticon_Quartic_Model_Mysql4_Placement_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('quartic/placement');
    }

    /**
     * Convert to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = array();

        foreach ($this as $item) {
            $res[] = array(
                'value' => strtolower($item->getData('id')),
                'label' => $item->getLabel(),
            );
        }
        return $res;
    }
}
