<?php
$installer = $this;
$installer->startSetup();

$sql = <<<SQLTEXT
TRUNCATE TABLE {$this->getTable('quartic_maps')};
SQLTEXT;

$this->run($sql);

$sql = <<<SQLTEXT
        
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'price', 'price');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'old_price', 'old_price');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'title', 'name');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'custom1', '');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'custom2', '');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'custom3', '');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'custom4', '');
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'custom5', '');

SQLTEXT;

$this->run($sql);

@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic 0.2.3',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
