<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

// Change current directory to the directory of current script
chdir(dirname(__FILE__));

require '../app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

if(Zero1_Crondoctor_Model_Observer::shouldStop()){
    echo 'Stop Flag Found'.PHP_EOL;
    exit;
}

umask(0);

$disabledFuncs = explode(',', ini_get('disable_functions'));
$isShellDisabled = is_array($disabledFuncs) ? in_array('shell_exec', $disabledFuncs) : true;
$isShellDisabled = (stripos(PHP_OS, 'win') === false) ? $isShellDisabled : true;

$cronModes = array(
    'always' => 'always',
    'default' => 'default',
    'schedule' => 'schedule',
    'zombie' => 'zombie',
);

try {
    if (stripos(PHP_OS, 'win') === false) {
        $options = getopt('m::');
        if (isset($options['m'])) {

            if(isset($cronModes[$options['m']])){
                $cronMode = $cronModes[$options['m']];
            }else{
                Mage::throwException('Unrecognized cron mode was defined');
            }

        } else if (!$isShellDisabled) {
            $fileName = basename(__FILE__);
            $baseDir = dirname(__FILE__);

            foreach($cronModes as $key => $mode){
                shell_exec("/bin/sh $baseDir/cron.sh $fileName -m".$key." 1 > /dev/null 2>&1 &");
            }
            exit;
        }
    }

    Mage::getConfig()->init()->loadEventObservers('crontab');
    Mage::app()->addEventArea('crontab');
    if ($isShellDisabled) {
        Mage::dispatchEvent('always');
        Mage::dispatchEvent('default');
    } else {
        Mage::dispatchEvent($cronMode);
    }
} catch (Exception $e) {
    Mage::printException($e);
    exit(1);
}