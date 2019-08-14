<?php

class Quarticon_Quartic_Model_Placement_Home extends Quarticon_Quartic_Model_Placement
{

    /**
     * Return collection for toOptionArray function
     *
     * @return array
     */
    protected function prepareOptionArray()
    {
        //$inserts = Mage::getModel('quartic/insert_home')->getData('places');
        //$limit = array_map('ucfirst', array_keys($inserts));
        $options = $this->getCollection()
                ->addFieldToSelect('*')
                ->addFilter('parent_name', array('eq' => 'HomePage'))
                //->addFieldToFilter('name', array('in' => $limit))
                ->loadData()
                ->toOptionArray(false);
        return $options;
    }
}
