<?php
/**
 * Created by PhpStorm.
 * User: thanhd
 * Date: 4/08/2016
 * Time: 12:10 PM
 */

use \Magento\Framework\App\Bootstrap;
use \Magento\Store\Model\Store;
use \Magento\Store\Model\StoreManager;

require __DIR__ . '/app/bootstrap.php';
$_SERVER[StoreManager::PARAM_RUN_CODE] = 'default';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

$starttime = microtime(true);
$helper = $objectManager->create('Playhouse\Importer\Helper\Data');
//changed to latest date
//$inputFile = $helper->getMediaImporterDir() . DIRECTORY_SEPARATOR . 'import25_mar2019.csv';
$inputFile = $helper->getMediaImporterDir() . DIRECTORY_SEPARATOR . 'zazaimport.csv';
echo $inputFile;
$result = $helper->importCombinedCsv($inputFile);
var_dump($result);
?>
