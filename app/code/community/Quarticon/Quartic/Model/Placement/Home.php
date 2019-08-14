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
        $storeId = $this->getStoreId();
        $apiName = Mage::getStoreConfig("quartic/config/customer", $storeId);
        
        $options = $this->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('api_name', $apiName)
                ->addFilter('parent_name', array('eq' => 'HomePage'))
                ->loadData()
                ->toOptionArray(false);
        return $options;
    }
}
