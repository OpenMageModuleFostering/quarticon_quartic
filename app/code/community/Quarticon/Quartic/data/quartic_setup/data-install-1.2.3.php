<?php
$installer = $this;
$installer->startSetup();
$secret = Mage::getStoreConfig('quartic/config/secret');
$storeName = Mage::getStoreConfig('quartic/config/storeName');
Mage::getModel('core/config')->saveConfig('quartic/config/iframe_link', 'https://shopapi.quarticon.com/pluginMagento/admin', 'default', 0);
Mage::getModel('core/config')->saveConfig('quartic/config/webhook_link', 'https://shopapi.quarticon.com/pluginMagento/webhook', 'default', 0);
Mage::getModel('core/config')->saveConfig('quartic/config/clientData_link', 'https://shopapi.quarticon.com/pluginMagento/getClientData', 'default', 0);
if (!$secret && !$storeName) {
    $storeName = Mage::getModel('quartic/config')->savePluginStoreName();
    $secret = Mage::getModel('quartic/config')->saveSecret();
}

Mage::app()->getStore()->resetConfig();

// send config to quartic webhook
Mage::helper('quartic')->sendEventStoreInit($storeName, $secret);

$installer->endSetup();
