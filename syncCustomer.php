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

$storeMap = [
//   'au' => [2,2],
  'sg' => [4,4],
  'nz' => [3,3],
  'my' => [5,5],
  'ca' => [7,6]
];
$eavAttributeMap = array(
  194 => 'varchar', // king_living_id
  329 => 'varchar', // card_code
  330 => 'int',     // subscription_sms
  331 => 'int',     // subscription_post
  332 => 'int',     // subscription_email
  380 => 'varchar', // customer_type
  381 => 'int',     // customer_type_status
);

foreach ($storeMap as $storeCode => $id) {
  $storeId = $id[0];
  $websiteId = $id[1];
  $tableCustomer20 = $resource->getTableName('customer_entity_'.$storeCode);
  $tableCustomer = $resource->getTableName('customer_entity');
  
$sql = "SELECT * FROM " . $tableCustomer20;
  $result = $connection->fetchAll($sql); 
  $total = count($result);
  foreach ($result as $i =>  $row) { 
      echo "-------------------\n";
      echo "$i/$total\n";
      $email = $row["email"];
      echo "Sync customer $email for store $storeId\n";
      // should be reset storeId and website ID
      $row["website_id"] = $websiteId;
      $row["store_id"] = $storeId;
      $entityId20 = $row["entity_id"];
      $hasCustomer = "SELECT entity_id FROM ". $tableCustomer ." WHERE email=:email AND website_id='$websiteId'";
      $entityId = $connection->fetchOne($hasCustomer,[':email' => $email]);
      if($entityId) {
          echo "HAVE\n";
          $row['entity_id'] = $entityId;
          $connection->update($tableCustomer,$row,"entity_id = $entityId");
          echo "Update records Customer successfully\n";
      } else {
          echo "DONT\n";
          $row['entity_id'] = null;
          $row["failures_num"] = '';
          $row["first_failure"] = '';
          $row["lock_expires"] = '';
          $connection->insert($tableCustomer,$row);
          $entityId = $connection->lastInsertId($tableCustomer);
          echo "New records created Customer successfully\n";
      }
      
      //Entity-Id-20 
      foreach ($eavAttributeMap as $aId => $type) { 
          switch($type) { 
              case 'varchar':
                  $table = "customer_entity_varchar";
                  $table20 = "customer_entity_varchar_".$storeCode;
                  break;
              case 'int':
                  $table = "customer_entity_int";
                  $table20 = "customer_entity_int_".$storeCode;
                  break;
          }
          $query = "SELECT value FROM " . $table20 . " WHERE entity_id='$entityId20' AND attribute_id='$aId'";
          $value = $connection->fetchOne($query);
          if ($value !== false){
              echo "Attribute ID $aId has a value\n";
              $query = "SELECT value_id FROM " . $table . " WHERE entity_id='$entityId' AND attribute_id='$aId'";
              $valueId = $connection->fetchOne($query);
              if ($valueId){
                  echo "The value was updated by entityId $entityId \n";
                  $connection->update($table,["value" => $value],"value_id = $valueId");
              }else{
                echo "The value was inserted by entityId $entityId \n";
                  $data = [
                      "value" => $value,
                      "attribute_id" => $aId,
                      "entity_id" => $entityId
                  ];
                  $connection->insert($table,$data);
              }
          }
      }
  
      // Check has address
      $tableAddress20 = $resource->getTableName('customer_address_entity_'.$storeCode);
      $tableAddress = $resource->getTableName('customer_address_entity');
      
      $hasCustomerAddress = "SELECT * FROM ". $tableAddress20 ." WHERE parent_id='$entityId20'";
      $address = $connection->fetchAll($hasCustomerAddress);
      if (count($address) > 0){ 
          foreach ($address as $row) {
              $row['entity_id'] = null;
              $row['parent_id'] = $entityId;
              $connection->insert($tableAddress,$row);
              echo "New records created Customer Address successfully\n";
          }
      }
  }
}

