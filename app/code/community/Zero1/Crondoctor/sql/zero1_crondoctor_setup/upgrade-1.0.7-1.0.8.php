<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Change columns
 */
$installer->getConnection()->addColumn(
    $installer->getTable('cron/schedule'),
    'updated_at',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable'  => true,
        'comment'   => 'Updated At'
    )
);

$installer->endSetup();