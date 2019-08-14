<?php

class Quarticon_Quartic_ActiveController extends Mage_Core_Controller_Front_Action
{
    /**
     * Return plugin and store version
     */
    public function checkAction()
    {
        // sprawdz czy jest powiazanie z customerem
        $helper = Mage::helper('quartic');
        $storeId = $helper->getStoreId();

        // przygotuj dane klienta - m. in. wysyla katalog i historie przez API
        $helper->prepareCustomerData();

        $arr = array(
            'name' => 'Quartic plugin',
            'pluginVersion' => reset(Mage::getConfig()->getModuleConfig("Quarticon_Quartic")->version),
            'storeVersion' => Mage::getVersion(),
            'storeId' => $storeId
        );
        header('Content-Type: application/json');
        die(json_encode($arr));
    }
}
