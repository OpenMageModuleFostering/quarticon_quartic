<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

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
		'default'                   => '0',
		'is_user_defined'           => true,
		'used_in_product_listing'   => false
    ));

///*
// * Remove price from mapping
// */
//$sql = <<<SQLTEXT
//DELETE FROM {$this->getTable('quartic_maps')} WHERE quartic_attribute = 'price' OR quartic_attribute = 'old_price';
//SQLTEXT;
//$this->run($sql);

/*
 * Fix some old versioning
 */
$sql = <<<SQLTEXT
update `core_resource` set version = '1.0.8', data_version = '1.0.8' where code = 'quartic_setup';
SQLTEXT;
$this->run($sql);


$installer->endSetup();
