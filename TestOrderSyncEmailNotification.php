<?php
/**
 * Created by PhpStorm.
 * User: thanhd
 * Date: 4/08/2016
 * Time: 12:10 PM
 */

use \Magento\Framework\App\Bootstrap;
use \Magento\Store\Model\StoreManager;

require __DIR__ . '/app/bootstrap.php';


$_SERVER[StoreManager::PARAM_RUN_CODE] = 'default';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();


$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$area = $objectManager->get('Magento\Framework\App\State');

$area->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);


$helper = $objectManager->get('MindArc\SAPIntegration\Helper\Order');
echo $helper->testEmailNotification();
die();
