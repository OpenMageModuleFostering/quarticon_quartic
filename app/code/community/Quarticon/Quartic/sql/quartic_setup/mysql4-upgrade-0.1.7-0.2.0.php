<?php
$this->startSetup();

/*
 * Feeds history
 */

$sql = <<<SQLTEXT

DROP TABLE IF EXISTS {$this->getTable('quartic_history')};

create table {$this->getTable('quartic_history')}( 
      id int not null auto_increment,
      store_id int,
      user int,
      date timestamp,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SQLTEXT;

$this->run($sql);

/*
 * Install additional product feed mapping attributes
 */

$sql = <<<SQLTEXT

DROP TABLE IF EXISTS {$this->getTable('quartic_maps')};

CREATE TABLE {$this->getTable('quartic_maps')}( 
      id int not null auto_increment,
      quartic_attribute varchar(255),
      magento_attribute varchar(255),
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'title', 'name');

SQLTEXT;

$this->run($sql);

$sql = <<<SQLTEXT
        
INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'id', 'sku');

INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'price', 'price');

INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'old_price', 'old_price');

INSERT INTO {$this->getTable('quartic_maps')} VALUES  
        (NULL, 'status', 'status');

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

$this->endSetup();
