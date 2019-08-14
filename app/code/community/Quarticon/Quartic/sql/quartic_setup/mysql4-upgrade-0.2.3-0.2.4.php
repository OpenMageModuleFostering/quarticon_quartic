<?php
$installer = $this;
$installer->startSetup();

$use_sku = 0;
Mage::getModel('core/config')->saveConfig('quartic/config/use_sku', $use_sku, 'default', 0);
$add_thumbs = 1;
Mage::getModel('core/config')->saveConfig('quartic/config/add_thumbs', $add_thumbs, 'default', 0);

@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic 0.2.4',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
