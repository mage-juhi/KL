<?php
/**
 * Created by PhpStorm.
 * User: thanhd
 * Date: 4/08/2016
 * Time: 12:10 PM
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

use \Magento\Framework\App\Bootstrap;
use \Magento\Store\Model\Store;
use \Magento\Store\Model\StoreManager;

require __DIR__ . '/app/bootstrap.php';
$_SERVER[StoreManager::PARAM_RUN_CODE] = 'default';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');


$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$helper = $objectManager->get('MindArc\SAPIntegration\Model\OrderSync');

var_dump($helper->buildPayloadTest('CA002023261'));