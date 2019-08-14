<?php
$installer = $this;
$installer->startSetup();

Mage::getModel('core/config')->saveConfig('quartic/config/iframe_link', 'https://shopapi.quarticon.com/pluginMagento/admin', 'default', 0);
Mage::getModel('core/config')->saveConfig('quartic/config/webhook_link', 'https://shopapi.quarticon.com/pluginMagento/webhook', 'default', 0);
Mage::getModel('core/config')->saveConfig('quartic/config/clientData_link', 'https://shopapi.quarticon.com/pluginMagento/getClientData', 'default', 0);
$name = Mage::getModel('quartic/config')->savePluginStoreName();
$secret = Mage::getModel('quartic/config')->saveSecret();

Mage::app()->getStore()->resetConfig();

// send config to quartic webhook
Mage::helper('quartic')->sendEventStoreInit($name, $secret);

$installer->endSetup();
