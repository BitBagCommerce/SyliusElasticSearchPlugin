<?php
/**
 * @author Peter Ukena <peter.ukena@brille24.de>
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Resolver;

use Sylius\Component\Core\Model\ProductInterface;

class IndexableResolver implements IndexableInterface
{
    /** {@inheritdoc} */
    public function isProductIndexable(ProductInterface $product): bool
    {
        // Check if product is enabled at all.
        if (!$product->isEnabled()) {
            return false;
        }

        /**
         * We can check things like
         *   * Stock
         *   * Channel
         *   * ...
         */

        return true;
    }
}
