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
        $storeId = $this->getStoreId();
        $apiName = Mage::getStoreConfig("quartic/config/customer", $storeId);

        $options = $this->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('api_name', $apiName)
                ->addFieldToFilter('parent_name', array('eq' => 'CartPage'))
                ->loadData()
                ->toOptionArray(false);
        return $options;
    }
}
