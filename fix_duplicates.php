<?php

$conn = mysqli_connect("kingliving-twothree-prod.cbc90ujoasmv.ap-southeast-2.rds.amazonaws.com", "mindarc", "VKFBS6v7vmt54tzS", "kingliving_prod_nz");

if (!$conn) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

/* change character set to utf8 */
if (!$conn->set_charset("utf8")) {
    exit();
}

$attributeId = 233; // Your Attribute Code!
$stores = array(3); // Your STORE IDs

foreach ($stores as $store) {
    clearEmptyAttributes($conn, $attributeId, $store);
    fixDuplicates($conn, $attributeId, $store);
}

mysqli_close($conn);


############################## FUNCTION ENVIROMENT ##############################


/**
 * Fixing duplicates
 *
 * @param $conn
 * @param $attributeId
 * @param int $storeId
 */
function fixDuplicates($conn, $attributeId, $storeId=0){

    echo "<h3>Fixing the duplicates</h3>";
    $recordsToDelete = array();
    $result = $conn->query("SELECT * FROM `catalog_product_entity_varchar` where attribute_id = $attributeId and store_id = $storeId order by row_id");
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $hash = $row['row_id'] . "-" . $row['attribute_id'] . "-" . $row['store_id'] ."-". $row['value'];
            $recordsToDelete[$hash][] = $row['value_id'];
        }
        echo 'hit';
        print_r($recordsToDelete,true);
    } else {
        echo "0 results";
    }

    $recordsToDelete = saveLastElementAndPrepareSQLDataToDelete($recordsToDelete);
    if($recordsToDelete){
        deleteDuplicates($conn, $recordsToDelete);
        return true;
    }

    return false;
}

/**
 * @param $data
 * @return mixed
 */
function saveLastElementAndPrepareSQLDataToDelete($data){
    foreach ($data as $key => $item){
        if(count($item) > 1){
            array_pop($item);
            $data[$key] = implode(",", $item);
        }else{
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Delete duplicates records from Magento
 *
 * @param $conn
 * @param $attributeId
 */
function deleteDuplicates($conn, $data){

    echo "<h3>Delete Duplicate Entities</h3>";

    foreach ($data as $key => $items) {

        $sql = "DELETE FROM catalog_product_entity_varchar WHERE value_id IN ($items) ";

        if ($conn->query($sql) === TRUE) {
            echo "$items have been deleted! <br/>";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}

/**
 * Delete unuseful records from Magento
 *
 * @param $conn
 * @param $attributeId
 */
function clearEmptyAttributes($conn, $attributeId, $storeId=0){

    $sql = "DELETE FROM catalog_product_entity_varchar WHERE attribute_id = $attributeId AND store_id = $storeId AND value IS NULL ";

    if ($conn->query($sql) === TRUE) {
        echo "Empty attributes cleared!";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}