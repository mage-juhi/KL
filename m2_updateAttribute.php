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

// objectManager
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

// Logger
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/atest.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);


$directory = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList')->getPath('media');
$fileCsv = $objectManager->get('Magento\Framework\File\Csv');
$file = $directory . '/import' . '/kl1622.csv';

if (file_exists($file)) {
    $data = $fileCsv->getData($file);
    $logger->info('Load file csv: '.(count($data)-1)); 
	echo (count($data)-1). " \n";
    for($i=0; $i<count($data); $i++) {
		$productData = array();
		if (!isset($data[$i][2]))
			continue;
		$sku = $data[$i][2];
		try {
			$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
			$product = $productRepository->get($sku);
			$product->addAttributeUpdate('special_to_date', '2021-11-08 00:00:00' , 0);
			$product->addAttributeUpdate('special_to_date', '2021-11-08 00:00:00' , 2);
			echo "---Synced product $sku \n";
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e){
			// insert your error handling here
		}

    }
}

?>
