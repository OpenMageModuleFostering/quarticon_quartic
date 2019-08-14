<?php
$installer = $this;
$installer->startSetup();

$sql = <<<SQLTEXT
DELETE FROM {$this->getTable('core_config_data')} 
WHERE path LIKE 'quartic/frames%';
SQLTEXT;

$this->run($sql);

$cache = Mage::app()->getCacheInstance();
$cache->cleanType('config');

@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic 0.3.2',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
