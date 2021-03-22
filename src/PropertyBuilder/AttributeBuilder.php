<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\PropertyBuilder;

use BitBag\SyliusElasticsearchPlugin\Formatter\StringFormatterInterface;
use BitBag\SyliusElasticsearchPlugin\PropertyNameResolver\ConcatedNameResolverInterface;
use Elastica\Document;
use FOS\ElasticaBundle\Event\TransformEvent;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class AttributeBuilder extends AbstractBuilder
{
    /** @var ConcatedNameResolverInterface */
    private $attributeNameResolver;

    /** @var StringFormatterInterface */
    private $stringFormatter;

    /** @var LocaleContextInterface */
    private $localeContext;

    public function __construct(
        ConcatedNameResolverInterface $attributeNameResolver,
        StringFormatterInterface $stringFormatter,
        LocaleContextInterface $localeContext
    ) {
        $this->attributeNameResolver = $attributeNameResolver;
        $this->stringFormatter = $stringFormatter;
        $this->localeContext = $localeContext;
    }

    public function consumeEvent(TransformEvent $event): void
    {
        $this->buildProperty($event, ProductInterface::class,
            function (ProductInterface $product, Document $document): void {
                $this->resolveProductAttributes($product, $document);
            });
    }

    private function resolveProductAttributes(ProductInterface $product, Document $document): void
    {
        foreach ($product->getAttributes() as $attributeValue) {
            $attribute = $attributeValue->getAttribute();
            if (!$attribute) {
                continue;
            }
            $attributeCode = $attribute->getCode();
            $index = $this->attributeNameResolver->resolvePropertyName($attributeCode);
            $value = $attributeValue->getValue();
            if ($attribute->getType() === 'select') {
                $choices = $attribute->getConfiguration()['choices'] ?? [];
                if (is_array($value)) {
                    foreach ($value as $i => $item) {
                        $value[$i] = $choices[$item][$this->localeContext->getLocaleCode()] ?? $item;
                    }
                } else {
                    $value = $choices[$value][$this->localeContext->getLocaleCode()] ?? $value;
                }
            }
            $attributes = [];

            if (is_array($value)) {
                foreach ($value as $singleElement) {
                    $attributes[] = $this->stringFormatter->formatToLowercaseWithoutSpaces((string) $singleElement);
                }
            } else {
                $value = is_string($value) ? $this->stringFormatter->formatToLowercaseWithoutSpaces($value) : $value;
                $attributes[] = $value;
            }

            $document->set($index, $attributes);
        }
    }
}
