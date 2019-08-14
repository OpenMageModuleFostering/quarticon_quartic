<?php

class Quarticon_Quartic_Model_Placement_Cart extends Quarticon_Quartic_Model_Placement
{

    /**
     * Return collection for toOptionArray function
     *
     * @return array
     */
    protected function prepareOptionArray()
    {
        //$inserts = Mage::getModel('quartic/insert_cart')->getData('places');
        //$limit = array_map('ucfirst', array_keys($inserts));
        $options = $this->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('parent_name', array('eq' => 'CartPage'))
                //->addFieldToFilter('name', array('in' => $limit))
                ->loadData()
                ->toOptionArray(false);
        return $options;
    }
}
