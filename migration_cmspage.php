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

$selectQuery = "SELECT distinct cms_page_my.*
                FROM cms_page_store_my
                LEFT JOIN cms_page_my ON cms_page_store_my.page_id = cms_page_my.page_id
                WHERE cms_page_store_my.store_id IN (0, 2, 5)";
$curRow = 10064;
$myData = $connection->fetchAll($selectQuery);
foreach ($myData as $page) {
    echo "-----------------------\n";
    $identifier = $page['identifier'];
    echo "Page $identifier \n";
    $content = $page['content'];
    $content = $page['content'];
    $content_heading = $page['content_heading'];
    $meta_keywords = $page['meta_keywords'];
    $meta_description = $page['meta_description'];
    $myPage = [
        'identifier' => $page['identifier'],
        'content' => $page['content'],
        'content_heading' => $page['content_heading'],
        'meta_keywords' => $page['meta_keywords'],
        'meta_description' => $page['meta_description'],
    ];

    $selectQuery = "SELECT distinct cms_page.page_id,cms_page.identifier,cms_page.content,cms_page.content_heading,cms_page.meta_keywords,cms_page.meta_description
                    FROM cms_page_store LEFT JOIN cms_page ON cms_page_store.row_id = cms_page.row_id
                    WHERE (cms_page_store.store_id = 5 OR cms_page_store.store_id = 0) AND cms_page.identifier = '$identifier'";
    $value = $connection->fetchRow($selectQuery);
    if ($value === false){
        // Insert this page to 
        unset($page['published_revision_id']);
        unset($page['under_version_control']);
        echo $curRow . "\n";
        $curRow = $curRow + 1;
        echo $curRow . "\n";
        $page['page_id'] = $curRow;
        $page['row_id'] = $curRow;
        $connection->insert('cms_page',$page);
        
        $page_id = $connection->lastInsertId('cms_page');
        $selectQuery = "SELECT distinct cms_page.row_id FROM cms_page WHERE cms_page.page_id = $page_id";
        $insertPage = $connection->fetchRow($selectQuery);
        $page_store = [
            'row_id' => $insertPage['row_id'],
            'store_id' => 5
        ];
        $connection->insert('cms_page_store',$page_store);
        echo " - > Insert the page \n";
    }else{
        $page_id = $value['page_id'];
        unset($value['page_id']);
        if ($myPage == $value)
            echo " - > The same page here \n";
        else{
            $connection->update('cms_page',$myPage,"page_id = $page_id");
            echo " - > Update the page \n";
        }
    }
    // $pageIds[] = $page["page_id"];
    // var_dump($myPage);
    // var_dump($value);
    // exit
}

