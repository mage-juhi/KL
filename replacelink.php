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

$tableCmsBlock   = $resource->getTableName('cms_block');
$tableCmsPage    = $resource->getTableName('cms_page');
$tableMenu    = $resource->getTableName('magestore_megamenu_megamenu');
$queryCmsBlock   = "SELECT block_id,content FROM ". $tableCmsBlock;
$queryCmsPage    = "SELECT page_id,content FROM ". $tableCmsPage;
// $contentCmsBlock = $connection->fetchAll($queryCmsBlock);
// $contentCmsPage  = $connection->fetchAll($queryCmsPage);


$queryMenu    = "SELECT megamenu_id,stores,link,main_content,footer FROM ". $tableMenu . " WHERE status = 1";
$contentMenu = $connection->fetchAll($queryMenu);
//=============== REPLACE MENU LINK ==================
foreach ($contentMenu as $key => $value) {
    $word = 'href="/';
    $idMenu = $value['megamenu_id'];
    $string = $value['main_content'];
    $footer = $value['footer'];
    $stores = $value['stores'];
    $link   = $value['link'];
    $parsed = [];
    switch ($stores) {
        case '2':
            $storeCode = '/au';
            break;
        case '3':
            $storeCode = '/nz';
            break;
        case '4':
            $storeCode = '/sg';
            break;
        case '5':
            $storeCode = '/my';
            break;
        case '7':
            $storeCode = '/ca';
            break;
        default:
            $storeCode = null;
            break;
    }
    if(strpos($string, $word) !== false){
        $parsed = str_between_all($value['main_content'] , 'href="/' , '"');
        foreach ($parsed as $key1 => $value) {
            $oldString = 'href="/'.$value.'"';
            $value  = '/'.$value;
            if($storeCode){
                $subDirectories = substr($value, 0, 4);
                if(strcmp($subDirectories, $storeCode.'/') != 0){
                    $newString = 'href="'.$storeCode.$value.'"';
                }else{
                    $newString = 'href="'.$value.'"';
                }
                echo $oldString." --->";
                echo $newString."\n";
                $string = str_replace($oldString,$newString,$string);
            }
        }
    }
    $connection->update($tableMenu,["main_content" => $string],"megamenu_id = $idMenu");
    if(strpos($footer, $word) !== false ){
        $parsed = str_between_all($footer , 'href="/' , '"');
        foreach ($parsed as $key1 => $value) {
            $oldString = 'href="/'.$value.'"';
            $value  = '/'.$value;
            if($storeCode){
                $subDirectories = substr($value, 0, 4);
                if(strcmp($subDirectories, $storeCode.'/') != 0){
                    $newString = 'href="'.$storeCode.$value.'"';
                }else{
                    $newString = 'href="'.$value.'"';
                }
                echo $oldString." --->";
                echo $newString."\n";
                $footer = str_replace($oldString,$newString,$footer);
            }
        }
    }
    $connection->update($tableMenu,["footer" => $footer],"megamenu_id = $idMenu");
    if($storeCode){
        $subDirectoriesLink = substr($link, 0, 4);
        if(strcmp($subDirectoriesLink, $storeCode.'/') != 0){
            $newLinkString = $storeCode . $link;
            echo $link." --->";
            echo $newLinkString."\n";
            $connection->update($tableMenu,["link" => $newLinkString],"megamenu_id = $idMenu");
        }else{
            $newLinkString = $link;
        }
    }
    echo "=====UPDATED MENU LINK=====\n";
}
//=============== REPLACE BLOCK LINK ==================
// foreach ($contentCmsBlock as $key => $value) {
//     $word = 'href="/';
//     $string = $value['content'];
//     $idBlock =  $value['block_id'];
//     echo "ID BOCK:======".$idBlock."======\n";
//     $parsed = [];
//     if(strpos($string, $word) !== false){
//         $parsed = str_between_all($value['content'] , 'href="/' , '"');
//         foreach ($parsed as $key1 => $value) {
//             $oldString = 'href="/'.$value.'"';
//             $newString = 'href="{{store url="'.$value.'"}}"';
//             echo $oldString."\n";
//             echo $newString."\n";
//             $string = str_replace($oldString,$newString,$string);
//         }
//     }
//     $connection->update($tableCmsBlock,["content" => $string],"block_id = $idBlock");
//     echo "=====UPDATED BLOCK LINK=====\n";
// }

// =============== REPLACE PAGE LINK ==================
// foreach ($contentCmsPage as $key => $value) {
//     $word = 'href="/';
//     $string = $value['content'];
//     $idPage =  $value['page_id'];
//     echo "ID PAGE:======".$idPage."======\n";
//     $parsed = [];
//     if(strpos($string, $word) !== false){
//         $parsed = str_between_all($value['content'] , 'href="/' , '"');
//         foreach ($parsed as $key1 => $value) {
//             $oldString = 'href="/'.$value.'"';
//             $newString = 'href="{{store url="'.$value.'"}}"';
//             echo $oldString."\n";
//             echo $newString."\n";
//             $string = str_replace($oldString,$newString,$string);
//         }
//     }
//     $connection->update($tableCmsPage,["content" => $string],"page_id = $idPage");
//     echo "=====UPDATED PAGE LINK=====\n";
// }


function str_between_all(string $string, string $start, string $end, bool $includeDelimiters = false, int &$offset = 0): ?array
{
    $strings = [];
    $length = strlen($string);

    while ($offset < $length)
    {
        $found = str_between($string, $start, $end, $includeDelimiters, $offset);
        if ($found === null) break;

        $strings[] = $found;
        $offset += strlen($includeDelimiters ? $found : $start . $found . $end); // move offset to the end of the newfound string
    }

    return $strings;
}
function str_between(string $string, string $start, string $end, bool $includeDelimiters = false, int &$offset = 0): ?string
{
    if ($string === '' || $start === '' || $end === '') return null;

    $startLength = strlen($start);
    $endLength = strlen($end);

    $startPos = strpos($string, $start, $offset);
    if ($startPos === false) return null;

    $endPos = strpos($string, $end, $startPos + $startLength);
    if ($endPos === false) return null;

    $length = $endPos - $startPos + ($includeDelimiters ? $endLength : -$startLength);
    if (!$length) return '';

    $offset = $startPos + ($includeDelimiters ? 0 : $startLength);

    $result = substr($string, $offset, $length);

    return ($result !== false ? $result : null);
}