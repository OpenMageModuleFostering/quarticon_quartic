<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('quartic_placements')} 
    ADD `parent_name` VARCHAR(255) NOT NULL AFTER `qon_parent_id`;
");

@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic 0.2.2',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
