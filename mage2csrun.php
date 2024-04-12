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
$helper = $objectManager->get('MindArc\MiddlewareConnector\Helper\Data');
$product_data = array(
    'name' => 'RUGKL-APOLLO 200 x 300----',
    'sku' => 'RUGKL-APOLLO 200 x 300----',
    'store_code' => 'nz',
    'status' => 1,
    'attribute' => null,
    'attribute_type' => 'color',
    'associated' => [455,468]
);
$helper->createConfigProduct($product_data);
echo 'done';

// $quote = $objectManager->get('Magento\Checkout\Model\Session')->getQuote();

// $shippingAddress = $quote->getShippingAddress();
// $addressData = [
//     'country_id' => 'AU',
//     'postcode' => isset($_GET["pcode"])?$_GET["pcode"] : null,
//     'region_id' => isset($_GET["rid"])?$_GET["rid"] : 0,
//     'region' => isset($_GET["r"])?$_GET["r"] : null
// ];
// $shippingAddress->addData($addressData);
// $shippingAddress->setCollectShippingRates(true);

// $totalsCollector = $objectManager->create('\Magento\Quote\Model\Quote\TotalsCollector');
// $totalsCollector->collectAddressTotals($quote, $shippingAddress);
// $shippingRates = $shippingAddress->getGroupedAllShippingRates();
// foreach ($shippingRates as $carrierRates) {
//     foreach ($carrierRates as $rate) {
//     	echo "<pre>";
// 			var_dump($rate->toString()) ."</br>";
// 		echo "</pre>";
//     }
// }
// Read JSON file
$json = file_get_contents('./product.json');

//Decode JSON
$json_data = json_decode($json,true);

//Print data
// echo "<pre>";
// print_r($json_data);
  
// echo "</pre>";


$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
$connection = $resource->getConnection();
$productId = 3379;
foreach ($json_data as $option) {
    $optionCode = $option['optioncode'];
    $optionType = $option['type'];

    // catalog_product_option stuff
    $bind = array(
        'type' => $optionType,
        'optioncode' => $option['optioncode'],
        'sku' => isset($option['sku']) ? $option['sku'] : '',
        'is_require' =>  0,
        'optionimage' => isset($option['optionimage']) ? $option['optionimage'] : '',
        'optiondata' => isset($option['optiondata']) ? $serializer->serialize($option['optiondata']) : '',
    );

    $selectQuery = "SELECT option_id FROM catalog_product_option WHERE product_id = $productId AND type='$optionType' AND optioncode = '$optionCode'";
    $optionId = $connection->fetchOne($selectQuery);
    echo "$optionId\n";
    if ($optionId){
        $connection->update('catalog_product_option',$bind,"option_id = $optionId");
    }
    else{
        $bind['product_id'] = $productId;
        $connection->insert('catalog_product_option',$bind);
        $optionId = $connection->lastInsertId('catalog_product_option');
    }

    // catalog_product_option_title stuff
    $titleBind = array(
        'title' => $option['title'],
        'store_id' => 0,
        'option_id' => $optionId
    );
    $selectQuery = "SELECT option_title_id FROM catalog_product_option_title WHERE option_id = $optionId";
    $optionTitleId = $connection->fetchOne($selectQuery);
    if ($optionTitleId){
        $connection->update('catalog_product_option_title',$titleBind,"option_id = $optionId");
    }
    else{
        $connection->insert('catalog_product_option_title',$titleBind);
    }

    // catalog_product_option_price stuff
    if (isset($option['price'])){
        $priceBind = array(
            'price' => $option['price'],
            'price_type' => 'fixed',
            'store_id' => 0,
            'option_id' => $optionId
        );
        $selectQuery = "SELECT option_price_id FROM catalog_product_option_price WHERE option_id = $optionId";
        $optionPriceId = $connection->fetchOne($selectQuery);
        if ($optionPriceId){
            $connection->update('catalog_product_option_price',$priceBind,"option_id = $optionId");
        }
        else{
            $connection->insert('catalog_product_option_price',$priceBind);
        }
    }

    if (isset($option['type_option'])){
        foreach ($option['type_option'] as $value) {
            $optionValueCode = $value['optioncode'];
            $bind = array(
                'optioncode' => $value['optioncode'],
                'optionimage' => isset($value['optionimage']) ? $value['optionimage'] : '',
                'optiondata' => isset($value['optiondata']) ? $serializer->serialize($value['optiondata']) : '',
            );
            $selectQuery = "SELECT option_type_id FROM catalog_product_option_type_value WHERE option_id = $optionId AND optioncode = '$optionValueCode'";
            $optionTypeId = $connection->fetchOne($selectQuery);
            
            if ($optionTypeId){
                $connection->update('catalog_product_option_type_value',$bind,"option_id = $optionId");
            }
            else{
                $bind['option_id'] = $optionId;
                $connection->insert('catalog_product_option_type_value',$bind);
                $optionTypeId = $connection->lastInsertId('catalog_product_option_type_value');
            }

            $titleBind = array(
                'title' => $value['title'],
                'store_id' => 0,
                'option_type_id' => $optionTypeId
            );
            $selectQuery = "SELECT option_type_title_id FROM catalog_product_option_type_title WHERE option_type_id = $optionTypeId";
            $optionTypeTitleId = $connection->fetchOne($selectQuery);
            if ($optionTypeTitleId){
                $connection->update('catalog_product_option_type_title',$titleBind,"option_type_id = $optionTypeId");
            }
            else{
                $connection->insert('catalog_product_option_type_title',$titleBind);
            }

            $priceBind = array(
                'price' => $value['price'],
                'price_type' => 'fixed',
                'store_id' => 0,
                'option_type_id' => $optionTypeId
            );
            $selectQuery = "SELECT option_type_price_id FROM catalog_product_option_type_price WHERE option_type_id = $optionTypeId";
            $optionPriceId = $connection->fetchOne($selectQuery);
            if ($optionPriceId){
                $connection->update('catalog_product_option_type_price',$priceBind,"option_type_id = $optionTypeId");
            }
            else{
                $connection->insert('catalog_product_option_type_price',$priceBind);
            }
        }
    }
}
