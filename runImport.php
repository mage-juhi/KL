<?php
/**
 * Created by PhpStorm.
 * User: thanhd
 * Date: 4/08/2016
 * Time: 12:10 PM
 */

// from KL -474, steps are crucial here:
//1/ Catalog -> Importer -> Itoris: Create new importer and upload the csv file => got the importer Id
//2/ Add the id to my script to run with command line. It is too large file so we can't run on WEB GUI.

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

$model = $objectManager->create('Playhouse\Importer\Model\Itoris')->load(288);

$helper = $objectManager->create('Playhouse\Importer\Helper\Data');

$inputFile = $helper->getMediaImporterDir() . DIRECTORY_SEPARATOR . $model->getFilename();

echo "Dump $inputFile to DB\n";

$result = $helper->importCombinedCsv($inputFile);

var_dump($result);
