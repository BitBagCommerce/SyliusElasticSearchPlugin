<?php
/**
 * @author Peter Ukena <peter.ukena@brille24.de>
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Resolver;

use Sylius\Component\Core\Model\ProductInterface;

interface IndexableInterface
{
    /**
     * In all cases, the callback should return a true or false, with true indicating it will be indexed,
     * and a false indicating the object should not be indexed, or should be removed from
     * the index if we are running an update.
     *
     * @see https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/doc/types.md#testing-if-an-object-should-be-indexed
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function isProductIndexable(ProductInterface $product): bool;
}
