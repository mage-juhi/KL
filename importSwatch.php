<?php
use \Magento\Framework\App\Bootstrap;
use \Magento\Store\Model\Store;
use \Magento\Store\Model\StoreManager;
use Magento\Framework\App\Filesystem\DirectoryList;

require __DIR__ . '/app/bootstrap.php';
$_SERVER[StoreManager::PARAM_RUN_CODE] = 'default';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();

function getDataInFolder($gfg_folderpath){
    $imageFormat = [
        'jpg',
        'jpeg',
        'png',
        'gif'
    ];
    $dataImport = [];
    if (is_dir($gfg_folderpath)) {
        $files = opendir($gfg_folderpath); {
            if ($files) {
                while (($gfg_subfolder = readdir($files)) !== FALSE) {
                    if ($gfg_subfolder != '.' && $gfg_subfolder != '..') {
                        $dirpath = $gfg_folderpath . $gfg_subfolder . "/";
                        if (is_dir($dirpath)) {
                            $file = opendir($dirpath); {
                                if ($file) {
                                    while (($gfg_filename = readdir($file)) !== FALSE) {
                                        if ($gfg_filename != '.' && $gfg_filename != '..') {
                                            $extension = pathinfo($gfg_filename, PATHINFO_EXTENSION);
                                            if(in_array($extension, $imageFormat)){
                                                $labelArr = explode("-", $gfg_filename);
                                                // $label = (isset($labelArr[0]) && isset($labelArr[1])) ? $labelArr[0] . " " . $labelArr[1] : "";
                                                $label = str_replace(' Square' , '', str_replace(' Folded.jpg' , '', implode(" ", $labelArr)));
                                                $dataImport[$label] = [
                                                    'label' => $label,
                                                    'folder' => $dirpath.$gfg_filename,
                                                    'filename' => $gfg_filename
                                                ];
                                            }  
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    ksort($dataImport);
    return array_values($dataImport);
}

function generateImage($nameImage, $folder, $destinationFolder){
    // get name image and extension
    $info = pathinfo($nameImage);
    $imageCorrectName = strtolower(preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', basename($nameImage,'.'.$info['extension']))); 
    $imageCorrectFile = $imageCorrectName . '.' . $info['extension'];

    // create folder
    $nameImageArr = str_split($imageCorrectName);
    if(!is_dir($destinationFolder.$nameImageArr[0])){
        mkdir($destinationFolder.$nameImageArr[0]);
    }
    if(!is_dir($destinationFolder.$nameImageArr[0].'/'.$nameImageArr[1])){
        mkdir($destinationFolder.$nameImageArr[0].'/'.$nameImageArr[1]);
    }

    // check file is exit
    $newFolderImage = $destinationFolder.$nameImageArr[0].'/'.$nameImageArr[1].'/'.$imageCorrectFile;
    $url = '/'.$nameImageArr[0].'/'.$nameImageArr[1].'/'.$imageCorrectFile;
    $index = 1;
    while(file_exists($newFolderImage)){
        $k = $index++;
        $newFolderImage = $destinationFolder.$nameImageArr[0].'/'.$nameImageArr[1].'/'.$imageCorrectName. '_' . $k . '.' . $info['extension'];
        $url = '/'.$nameImageArr[0].'/'.$nameImageArr[1].'/'.$imageCorrectName. '_' . $k . '.' . $info['extension'];
    }

    // copy image
    if(copy($folder, $newFolderImage)){
        return $url;
    }
    return false;
}

// EXECUTE
$data = getDataInFolder("/var/www/html/PREPROD/var/import/swatches/Outdoor 20x20px Square Folded Images/");
$destinationFolder = '/media/attribute/swatch/';
$attributeId = 493;
$storeName = ['admin' => 0, 'default_store_view' => 1];
// Get max of sort order
$eavAttributeOption = $resource->getTableName('eav_attribute_option');
$sortOrders = $connection->fetchAll("SELECT sort_order FROM eav_attribute_option WHERE attribute_id = ".$attributeId); 
$sortMax = [];
foreach($sortOrders as $row){
    $sortMax[] = $row['sort_order'];
}

foreach($data as $key => $row){
    // Insert eav_attribute_option
    $dataEAO = [];
    $dataEAO['attribute_id'] = $attributeId;
    $dataEAO['sort_order'] = ((count($sortMax) > 0) ? max($sortMax) : 0) + $key + 1;
    $connection->insert('eav_attribute_option', $dataEAO);
    $optionId = $connection->lastInsertId('eav_attribute_option');
    // Insert eav_attribute_option_value
    foreach($storeName as $storeKey => $storeId){
        $dataEAOV = [];
        $dataEAOV['option_id'] = $optionId;
        $dataEAOV['store_id'] = $storeId;
        $dataEAOV['value'] = $row['label'];
        $connection->insert('eav_attribute_option_value', $dataEAOV);
    }
    // Insert eav_attribute_option_swatch
    $dataEAOW = [];
    $dataEAOW['option_id'] = $optionId;
    $dataEAOW['store_id'] = 0;
    $dataEAOW['type'] = 2;
    $dataEAOW['value'] = generateImage($row["filename"], $row["folder"], $destinationFolder);
    $connection->insert('eav_attribute_option_swatch', $dataEAOW);
    echo "Updated id $optionId\n";
}
echo "Done\n";


?> 