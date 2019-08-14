<?php
$installer = $this;
$installer->startSetup();
@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic ' . Mage::getConfig()->getModuleConfig("Quarticon_Quartic")->version,
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
