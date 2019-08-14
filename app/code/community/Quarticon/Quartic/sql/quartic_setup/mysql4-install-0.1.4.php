<?php
$installer = $this;
$installer->startSetup();

Mage::getModel('quartic/config')->saveHash();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('quartic_placements')} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `qon_id` int(11) NOT NULL,
      `qon_parent_id` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `div_id` varchar(255) NOT NULL,
      `snippet` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `qon_id` (`qon_id`),
      KEY `qon_parent_id` (`qon_parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

@mail(
    'contact@quarticon.com',
    '[Install] Quartic 0.1.4',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
