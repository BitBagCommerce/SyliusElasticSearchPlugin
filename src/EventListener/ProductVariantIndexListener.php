<?php

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\EventListener;

use FOS\ElasticaBundle\Persister\ObjectPersister;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProductVariantIndexListener implements EventSubscriberInterface
{
    /**
     * @var ObjectPersister
     */
    private $objectPersister;

    public function __construct(ObjectPersister $objectPersister)
    {
        $this->objectPersister = $objectPersister;
    }

    public function createProductInIndex(ResourceControllerEvent $event)
    {
        $variant = $event->getSubject();
        if (!$variant instanceof ProductVariantInterface) {
            return;
        }
        $product = $variant->getProduct();
        $product->addVariant($variant);
        $this->objectPersister->replaceOne($variant->getProduct());
    }

    public function updateProductInIndex(ResourceControllerEvent $event)
    {
        $variant = $event->getSubject();
        if (!$variant instanceof ProductVariantInterface) {
            return;
        }
        $this->objectPersister->replaceOne($variant->getProduct());
    }

    public function deleteProductInIndex(ResourceControllerEvent $event)
    {
        $variant = $event->getSubject();
        if (!$variant instanceof ProductVariantInterface) {
            return;
        }

        $product = $variant->getProduct();
        if (0 === \count($product->getVariants())) {
            $this->objectPersister->deleteOne($product);
            $this->objectPersister->insertOne($product);

            return;
        }
        $this->objectPersister->replaceOne($variant->getProduct());
    }

    public static function getSubscribedEvents()
    {
        return [
            'sylius.product_variant.post_create' => 'createProductInIndex',
            'sylius.product_variant.post_update' => 'updateProductInIndex',
            'sylius.product_variant.post_delete' => 'deleteProductInIndex',
        ];
    }
}
