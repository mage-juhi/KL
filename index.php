<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication('Magento\Framework\App\Http');
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
$_SERVER['HTTPS']='on';

$maintenanceFile =  __DIR__ . '/var/.maintenance_mindarc.flag';
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']; //check which provides the CLIENT IP
    $ips = explode(',', $ip);
    $clientIp = $ips[0];
}
elseif (isset($_SERVER['REMOTE_ADDR'])) {
    $ip = $_SERVER['REMOTE_ADDR']; //check which provides the CLIENT IP
    $ips = explode(',', $ip);
    $clientIp = $ips[0];
}

$allowed = array(
    '203.219.109.34',
    '210.10.222.226',
    '1.129.104.221',
    '117.3.46.25',
    '180.150.4.246',
    '114.73.238.32',
    '61.68.97.99',
    '1.173.87.87',
    '60.240.93.156',
    '58.84.201.51',
    '117.3.46.25',
    '125.209.151.191',
    '103.217.166.108',
    '112.199.118.220',
    '36.237.109.51',
    '114.74.171.15',
    '103.217.166.43',
    '112.199.118.220',
    '60.241.93.130',
    '103.28.128.32',
    '103.217.166.43',
    '112.199.118.220',
    '60.240.93.156',
    '13.54.147.138',
    '60.241.44.76',
    '14.200.10.118',
    '171.244.219.162',
    '1.173.85.121',
    '103.217.166.44',
    '114.73.50.55',
    '103.28.128.32',
    '110.175.83.192', // vincent home
    '112.199.118.220',
    '58.84.178.221',
    '149.167.63.92',
    '60.241.241.194',
    '159.196.160.80',
    '49.181.219.228',
    '203.166.229.65',
    '220.240.36.128',
    '149.167.133.179', //owais
    '122.106.68.52',
    '125.209.166.92',
    '123.25.115.82',
    '58.84.197.231',
    '220.233.116.74',
    '60.241.241.194',
    '203.219.16.214',
    '1.129.108.149',
    '49.195.107.197',
    '89.238.183.238', // KL Jess
    '159.196.169.123', // KL Shannen
    '103.141.202.99', // KL Rona & Amelia
    '119.18.0.78', // KL Tim
    '60.240.93.156', // KL Sandy & Isobel
    '1.43.145.144', // KL Vicky
    '130.105.195.24', // kl Vanja
    '47.72.224.168', // Brittany
    '113.22.101.75', // Thanh Lai
    '58.84.192.94', // Juhi
    '116.105.226.58',
    '122.106.68.52',
    '52.65.40.243',
    '119.18.1.154',
    '58.186.124.90',
    '103.246.103.171',
    '2405:4802:60dd:2630:4d66:36f3:c773:658',
    '122.106.68.52'

);
if (file_exists($maintenanceFile) && !in_array($clientIp, $allowed)) {
//    include_once dirname(__FILE__) . '/pub/errors/503.php';
    header("Location: https://www.kingliving.co.nz/pub/errors/503.php");
}

//if ($clientIp == '210.10.222.226' && stripos($_SERVER['REQUEST_URI'],'addupdate') )
//    die('Sap Blocked');

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */

$app = $bootstrap->createApplication('Magento\Framework\App\Http');
$bootstrap->run($app);
