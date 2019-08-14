<?php

class Quarticon_Quartic_Model_Placement_Product extends Quarticon_Quartic_Model_Placement
{

    /**
     * Return collection for toOptionArray function
     *
     * @return array
     */
    protected function prepareOptionArray()
    {
        //$inserts = Mage::getModel('quartic/insert_product')->getData('places');
        //$limit = array_map('ucfirst', array_keys($inserts));
        $options = $this->getCollection()
                ->addFieldToSelect('*')
                ->addFilter('parent_name', array('eq' => 'ProductPage'))
                //->addFieldToFilter('name', array('in' => $limit))
                ->loadData()
                ->toOptionArray(false);
        return $options;
    }
}
