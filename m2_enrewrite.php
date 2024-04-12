<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Created by PhpStorm.
 * User: thanhd
 * Date: 4/08/2016
 * Time: 12:10 PM
 */

use \Magento\Framework\App\Bootstrap;
use \Magento\Store\Model\Store;
use \Magento\Store\Model\StoreManager;
use \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use \Magento\UrlRewrite\Service\V1\Data\UrlRewrite;


require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');

// $product = $objectManager->get('Magento\Catalog\Model\Product')->load(32193);

// get product collection
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/url_rewrite_error.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);


/* 2. Generate URL for PRODUCT */
for ($storeId =2; $storeId <= 8; $storeId++) { 
    if ($storeId == 6) 
        continue;
    $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
    $productCollection->addAttributeToSelect(['name','url_path', 'url_key','visibility']);
    // $productCollection->addAttributeToFilter('sku', 'BELFIX-PKG 6 CUST V2------' );
    // $productCollection->addAttributeToFilter('status', array('eq' => 1) );
    // $productCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
    $list = $productCollection->load();
    $total = count($list);
    $update = 0;
    echo "$storeId  -> Total ". $total."\n";
    $i = 1;
    foreach($list as $product)
    {
        $productNew = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface')->getById($product->getId(),false,$storeId);
        // $UrlRewriteCollection = $objectManager->get('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()
        //                                     ->addFieldToFilter('request_path', $productNew->getUrlKey())
        //                                     ->addFieldToFilter('entity_id', $productNew->getId())
        //                                     ->addFieldToFilter('entity_type', ProductUrlRewriteGenerator::ENTITY_TYPE)
        //                                     ->addFieldToFilter('store_id', $storeId);
        $UrlRewriteCollectionMeta = $objectManager->get('Magento\UrlRewrite\Model\UrlRewrite')->getCollection()
                                            ->addFieldToFilter('entity_id', $productNew->getId())
                                            ->addFieldToFilter('entity_type', ProductUrlRewriteGenerator::ENTITY_TYPE)
                                            ->addFieldToFilter('store_id', $storeId);                     
        if ( $productNew->getStatus() != 1 || count($UrlRewriteCollectionMeta) > 0 || empty($productNew->getUrlKey()) || $productNew->getVisibility() == 1) {
            // echo $i . ' / ' . $total."\n";
            $i++;
            continue;
        }
        echo "--------------------------------------"."\n";
        echo "Regenerate url for ". $productNew->getId()."\n";
        dump($productNew->getVisibility());
        

        $objectManager->get('Magento\UrlRewrite\Model\UrlPersistInterface')->deleteByData([
        UrlRewrite::ENTITY_ID => $product->getId(),
        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
        UrlRewrite::REDIRECT_TYPE => 0,
        UrlRewrite::STORE_ID => $storeId
        ]);

        try {
            $product->setStoreId($storeId);
            $product->setUrlKey($productNew->getUrlKey());
            $objectManager->get('Magento\UrlRewrite\Model\UrlPersistInterface')->replace(
                $objectManager->get('Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator')->generate($productNew)
            );

            echo "-->GENERATE url ". $productNew->getUrlKey()."\n";
            $update++;
        } catch (\Exception $e) {
            echo $product->getUrlKey()."\n";
            echo $e->getMessage()."\n";
            $logger->info('Error url for '. $product->getId());
            $logger->info('Error url is '. $product->getUrlKey());
            $logger->info($e->getMessage());
            // exit;
        }
        echo $i . ' / ' . $total."\n";
        $i++;
    }
    echo "Generate URL: $update products for store $storeId \n";
}


?>
