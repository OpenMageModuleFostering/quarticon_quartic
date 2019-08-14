<?php
$installer = $this;
$installer->startSetup();

$sql = <<<SQLTEXT
        
UPDATE {$this->getTable('quartic_maps')} SET
    quartic_attribute = 'custom_1'
    WHERE quartic_attribute = 'custom1';
UPDATE {$this->getTable('quartic_maps')} SET
    quartic_attribute = 'custom_2'
    WHERE quartic_attribute = 'custom2';
UPDATE {$this->getTable('quartic_maps')} SET
    quartic_attribute = 'custom_3'
    WHERE quartic_attribute = 'custom3';
UPDATE {$this->getTable('quartic_maps')} SET
    quartic_attribute = 'custom_4'
    WHERE quartic_attribute = 'custom4';
UPDATE {$this->getTable('quartic_maps')} SET
    quartic_attribute = 'custom_5'
    WHERE quartic_attribute = 'custom5';

SQLTEXT;

$this->run($sql);

@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic 0.2.6',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
