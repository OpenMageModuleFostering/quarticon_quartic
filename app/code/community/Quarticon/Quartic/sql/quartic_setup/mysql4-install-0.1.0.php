<?php
$installer = $this;
$installer->startSetup();

Mage::getModel('quartic/config')->saveHash();

@mail(
    'contact@quarticon.com',
    '[Install] Quartic 0.1.0',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
