<?php
$installer = $this;
$installer->startSetup();

Mage::helper('quartic')->sendUpgradeEvent();

$installer->endSetup();
