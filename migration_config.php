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

$selectQuery = "SELECT * FROM core_config_data_copy1";
$storeId = 7;
$myData = $connection->fetchAll($selectQuery);
foreach ($myData as $config) {
    print_r("---------------------\n");
    print_r("Init Data\n");
    print_r($config);
    // print_r("e\n");
    // exit;
    $path = $config['path'];
    $scope = $config['scope'];
    $scopeId = $config['scope_id'];

    switch ($scope) {
        case 'default':
            $value = checkExist($connection,$path,$scope,$scopeId);
            if ($value === false){
                // Insert Default config
                $bind = array(
                    'path' => $path,
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'value' =>  $config['value']
                );
                $connection->insert('core_config_data',$bind);
                print_r("- > Insert Data to default config\n");
                print_r($bind );
            }else{
                // has default config update websites config
                if ($value != $config['value']){
                    $valueWeb = checkExist($connection,$path,'websites',6);
                    if ($valueWeb === false){
                        $bind = array(
                            'path' => $path,
                            'scope' => 'websites',
                            'scope_id' => 6,
                            'value' =>  $config['value']
                        );
                        $connection->insert('core_config_data',$bind);
                        print_r("- > Insert Data to website config\n");
                        print_r($bind );
                    }else{
                        if ($valueWeb != $config['value']){
                            $bind = array(
                                'value' => $config['value']
                            );
                            $connection->update('core_config_data',$bind,"scope_id = 6 AND path ='$path' AND scope = 'websites'");
                            print_r("- > Update Data to website config\n");
                            print_r($bind );
                        } 
                    }
                }
            }
            break;
        case 'websites':
                if ($scopeId == 2 || $scopeId == 6)
                {
                    $valueWeb = checkExist($connection,$path,'websites',6);
                    if ($valueWeb === false){
                        $bind = array(
                            'path' => $path,
                            'scope' => 'websites',
                            'scope_id' => 6,
                            'value' =>  $config['value']
                        );
                        $connection->insert('core_config_data',$bind);
                        print_r("- > Insert Data to website config\n");
                        print_r($bind );
                    }else{
                        if ($valueWeb != $config['value']){
                            $bind = array(
                                'value' => $config['value']
                            );
                            $connection->update('core_config_data',$bind,"scope_id = 6 AND path ='$path' AND scope = 'websites'");
                            print_r("- > Update Data to website config\n");
                            print_r($bind );
                        } 
                    }
                }
                break;
            case 'stores':
                    if ($scopeId == 2 || $scopeId == $storeId)
                    {
                        $valueWeb = checkExist($connection,$path,'stores',$storeId);
                        if ($valueWeb === false){
                            $bind = array(
                                'path' => $path,
                                'scope' => 'stores',
                                'scope_id' => $storeId,
                                'value' =>  $config['value']
                            );
                            $connection->insert('core_config_data',$bind);
                            print_r("- > Insert Data to stores config\n");
                            print_r($bind );
                        }else{
                            if ($valueWeb != $config['value']){
                                $bind = array(
                                    'value' => $config['value']
                                );
                                $connection->update('core_config_data',$bind,"scope_id = $storeId AND path ='$path' AND scope = 'stores'");
                                print_r("- > Update Data to stores config\n");
                                print_r($bind );
                            } 
                        }
                    }
                    break;
        
        default:
            break;
    }
}

function checkExist($connection,$path,$scope,$scopeId){
    $selectQuery = "SELECT value FROM core_config_data WHERE scope_id = $scopeId AND path ='$path' AND scope = '$scope'";
    $value = $connection->fetchOne($selectQuery);
    return $value;
}
exit;