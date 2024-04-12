
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
$_SERVER[StoreManager::PARAM_RUN_CODE] = 'au';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$objectManager      = \Magento\Framework\App\ObjectManager::getInstance();

$helper             = $objectManager->get('MindArc\SAPIntegration\Helper\Data');


$series = '12';
$customer_id = '92311';

        $customer_factory = $objectManager->create('\Magento\Customer\Model\CustomerFactory')->create();
        $customer_factory->setWebsiteId(0);
        $customer = $customer_factory->load($customer_id);

$post_data = array();
$objectManager      = \Magento\Framework\App\ObjectManager::getInstance();

$company = '';
$telephone = '';


    $billing_address_id = $customer->getDefaultBilling();



$shipping_address_id = $customer->getDefaultShipping();
$billing_address = null;
$shipping_address = null;

if ($billing_address_id) {
$billing_address = $objectManager->get('\Magento\Customer\Model\AddressFactory')->create()->load($billing_address_id);


$company = $billing_address->getCompany();
$telephone = $billing_address->getTelephone();
}
if ($shipping_address_id) {
$shipping_address = $objectManager->get('\Magento\Customer\Model\AddressFactory')->create()->load($shipping_address_id);
}

if ($company == ''){
$company = $customer->getFirstname() . ' ' . $customer->getLastname();
}

$post_data['CardName']  = $company;
$post_data['EmailAddress']    = $customer->getEmail();

if($telephone) {
    $post_data['Phone1'] = $telephone;
    $post_data['Cellular'] = $telephone;
}
$post_data['Series'] = $series;

$post_data['Currency']      = 'AUD';

if ($billing_address) {
// Billing Address
$street = $billing_address->getStreet();

$region = $objectManager->create('Magento\Directory\Model\Region')
->load($billing_address->getRegionId());

$post_data['Address']   = $street[0];
$post_data['City']      = $billing_address->getCity();
$post_data['BillToState']    = $region->getCode();
$post_data['Country']   = $billing_address->getCountryId();
$post_data['ZipCode']   = $billing_address->getPostcode();
}

if ($shipping_address) {
// Shipping Address
$street = $shipping_address->getStreet();

$post_data['MailAddress']      = $street[0];
$post_data['MailCity']      = $shipping_address->getCity();
$post_data['MailCounty']    = $shipping_address->getCountryId();
$post_data['MailZipCode']    = $shipping_address->getPostcode();
}
$comms_pref = [];
if ($customer->getSubscriptionEmail())
$comms_pref[] = 'Email';
if ($customer->getSubscriptionSms())
$comms_pref[] = 'SMS';
if ($customer->getSubscriptionPost())
$comms_pref[] = 'Post';
if (count($comms_pref) > 0)
$post_data['comms_pref'] = $comms_pref;

echo '<pre>' . print_r($post_data,true) . '</pre>' ;