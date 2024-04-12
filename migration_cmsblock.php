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
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
$connection = $resource->getConnection();

// get list MY cms page 
$pageIds = [];

$selectQuery = "SELECT distinct cms_block_my.*
                FROM cms_block_store_my
                LEFT JOIN cms_block_my ON cms_block_store_my.block_id = cms_block_my.block_id
                WHERE cms_block_store_my.store_id IN (0, 2, 5)";
$curRow = 10104;
$myData = $connection->fetchAll($selectQuery);
foreach ($myData as $page) {
    echo "-----------------------\n";
    $identifier = $page['identifier'];
    echo "Page $identifier \n";
    $content = $page['content'];
    $title = $page['title'];
    $myPage = [
        'identifier' => $page['identifier'],
        'content' => $page['content'],
        'title' => $page['title']
    ];

    $selectQuery = "SELECT distinct cms_block.block_id,cms_block.identifier,cms_block.content,cms_block.title
                    FROM cms_block_store LEFT JOIN cms_block ON cms_block_store.row_id = cms_block.row_id
                    WHERE (cms_block_store.store_id = 5 OR cms_block_store.store_id = 0) AND cms_block.identifier = '$identifier'";
    $value = $connection->fetchRow($selectQuery);
    if ($value === false){
        // Insert this page to 
        $curRow = $curRow + 1;
        echo $curRow . "\n";
        $page['block_id'] = $curRow;
        $page['row_id'] = $curRow;
        $connection->insert('sequence_cms_block',['sequence_value' => $curRow]);
        $connection->insert('cms_block',$page);
        
        $block_id = $connection->lastInsertId('cms_block');
        $selectQuery = "SELECT distinct cms_block.row_id FROM cms_block WHERE cms_block.block_id = $block_id";
        $insertPage = $connection->fetchRow($selectQuery);
        $page_store = [
            'row_id' => $insertPage['row_id'],
            'store_id' => 5
        ];
        $connection->insert('cms_block_store',$page_store);
        echo " - > Insert the page \n";
    }else{
        $block_id = $value['block_id'];
        unset($value['block_id']);
        if ($myPage == $value)
            echo " - > The same page here \n";
        else{
            var_dump($block_id);
            var_dump($myPage);
            $connection->update('cms_block',$myPage,"block_id = $block_id");
            echo " - > Update the page \n";
        }
    }
}

