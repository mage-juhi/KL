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

$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();

$tableCategoryUrl  = $resource->getTableName('catalog_category_entity_varchar');

$queryCategoryUrl   = "SELECT value_id,value, row_id FROM ". $tableCategoryUrl ." WHERE attribute_id = 125 AND store_id = 0";

$contentCategoryUrl = $connection->fetchAll($queryCategoryUrl);

foreach ($contentCategoryUrl as $key => $value) {
    $value0 = $value["value"];
    $id = $value["value_id"];
    $rowId = $value["row_id"];
    $queryCategoryUrlNew   = "SELECT value FROM ". $tableCategoryUrl ." WHERE attribute_id = 125 AND store_id IN (1,2,3,4,5,6,7,8) AND row_id = " . $rowId . " LIMIT 1 ";
    $contentCategoryUrlNew = $connection->fetchAll($queryCategoryUrlNew);
    if($contentCategoryUrlNew) {
        $value1 = $contentCategoryUrlNew[0]['value'];

        echo $value0;
        echo "\n==========\n";
        echo $value1;
        echo "\n==========\n";
        if($value0 != $value1) {
            $connection->update($tableCategoryUrl,["value" => $value1],"value_id = $id");
            echo "\n=====UPDATED VALUE=====\n";
        } 
    }
}
