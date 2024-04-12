<?php
use \Magento\Framework\App\Bootstrap;
use \Magento\Catalog\Model\Product;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);

$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gallery.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);

$objectManager = $bootstrap->getObjectManager();

$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();

$collectionFactory = $objectManager->get(CollectionFactory::class);
$collection = $collectionFactory->create();
$collection->addAttributeToSelect('*');
$collection->load();
$storeIds = [0,1,2,3,4,5,7];
foreach ($collection as $product) {
    $logger->info(json_encode($product->getData('entity_id')));
    foreach($storeIds as $i) {
        $product = $objectManager->create('\Magento\Catalog\Model\ProductRepository')->getById($product->getData('entity_id'), false, $i);
        $images = $product->getMediaGalleryEntries();
        foreach($images as $child){
            if (in_array('small_image',$child->getData('types'))) {
                if($i != 1){
                    $logger->info(json_encode($child->getData()));
                    $valuesTable = $resource->getTableName('catalog_product_entity_media_gallery_value');
                    $connection->update(
                        $valuesTable,
                        ['disabled' => 1],
                        ['store_id = ?' => $i, 'value_id IN (?)' => $child->getData('id')]
                    );
                } else {
                    $data = [
                        'value_id' => $child->getData('id'),
                        'store_id' => $i,
                        'position' => 0,
                        'disabled' => 0,
                        'row_id' => $product->getData('row_id')
                    ];
                    $connection->query("DELETE FROM catalog_product_entity_media_gallery_value WHERE `value_id` = ".$data['value_id']." AND `store_id` = ".$data['store_id']." AND `row_id` = ".$data['row_id']);
                    $connection->insert('catalog_product_entity_media_gallery_value', $data);

                }
            }
        }
    }
}

?>
