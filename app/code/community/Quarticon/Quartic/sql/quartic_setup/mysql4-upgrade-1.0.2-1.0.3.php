<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

if ($connection->tableColumnExists($this->getTable('quartic_placements'), 'api_name') === false) {
	$installer->run("
		ALTER TABLE {$this->getTable('quartic_placements')} 
		ADD `api_name` varchar(255) NOT NULL AFTER `id`;
	");
}

// category attribute
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'quarticon_exclude', array(
		'group'                     => 'General',
		'input'                     => 'select',
		'type'                      => 'int',
		'label'                     => 'Exclude from Quarticon feed',
		'source'                    => 'eav/entity_attribute_source_boolean',
		'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'                   => 1,
		'required'                  => 0,
		'visible_on_front'          => 0,
		'is_html_allowed_on_front'  => 0,
		'is_configurable'           => 0,
		'searchable'                => 0,
		'filterable'                => 0,
		'comparable'                => 0,
		'unique'                    => false,
		'user_defined'              => true,
		'default'                   => '1',
		'is_user_defined'           => true,
		'used_in_product_listing'   => false
    ));

@mail(
    'contact@quarticon.com',
    '[Upgrade] Quartic 1.0.3',
    "IP: " . $_SERVER['SERVER_ADDR'] . "\r\nHost: " . gethostbyaddr($_SERVER['SERVER_ADDR']),
    "From: " . (
        Mage::getStoreConfig('general/store_information/email_address') ?
            Mage::getStoreConfig('general/store_information/email_address') :
            'contact@quarticon.com'
        )
);

$installer->endSetup();
