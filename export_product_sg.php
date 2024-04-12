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
$state->setAreaCode('adminhtml');
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->register('isSecureArea', true);
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$tableTaxClass = $resource->getTableName('tax_class');
//Store id of exported products, This is useful when we have multiple stores. 
$store_id = 4;

$fp = fopen("export-product-sg.csv","w+");
$csvHeader = array(
    "Product Name", 
    "SKU",
    "Shipping Options", 
    "Deposit Allowed", 
    "Quantity Enabled",
    "Cylindo Data",
    "Product Card URL",
    "PDP Contents",
    "Display Product Disclaimer",
    "Offer Fabric Samples",
    "Customise Your",
    "Feature Overview Content",
    "Shipping & Delivery Content",
    "Warranty Content",
    "Product Support Content",
    "Access Check Required",
    "Description Contents",
    "Short Description Contents",
    "Category Label Contents",
    "Meta Title",
    "Meta Description",
    "Product Discontinued",
    "Tax Class"
);
fputcsv( $fp, $csvHeader,",");
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
/** Apply filters here */

$collection = $productCollection->addAttributeToSelect('*')->addStoreFilter($store_id)->load();

foreach ($collection as $product)
{
    $classId = $product->getData('tax_class_id');
    $queryTax = "SELECT class_name FROM ". $tableTaxClass ." WHERE class_id='$classId'";
    $resultClassTax = $connection->fetchOne($queryTax);
    $data = array();
    $data[] = $product->getName();
    $data[] = $product->getSku();
    $data[] = $product->getData('shipping_options');
    $data[] = $product->getData('deposit_allowed');
    $data[] = $product->getData('enable_quantity');
    $data[] = $product->getData('cylindo_data');
    $data[] = $product->getData('show_download_product_card');
    $data[] = $product->getData('product_content');
    $data[] = $product->getData('product_disclaimer');
    $data[] = $product->getData('offer_fabric_leather_samples');
    $data[] = $product->getData('customise_your');
    $data[] = $product->getData('feature_overview');
    $data[] = $product->getData('product_shipping_tab');
    $data[] = $product->getData('product_waranty_tab');
    $data[] = $product->getData('product_support_tab');
    $data[] = $product->getData('access_check_required');
    $data[] = $product->getData('description');
    $data[] = $product->getData('short_description');
    $data[] = $product->getData('category_custom_label');
    $data[] = $product->getData('meta_title');
    $data[] = $product->getData('meta_description');
    $data[] = $product->getData('product_discontinued');
    $data[] = $resultClassTax;
    fputcsv($fp, $data);  
}
fclose($fp);
echo "Export SG Product Successfully! \n";