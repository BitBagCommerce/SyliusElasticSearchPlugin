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
use Sylius\Component\Attribute\Model\AttributeTranslation;
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

            $attributeValueCode = $attributeValue->getLocaleCode();
            $attributeCode = $attribute->getCode();

            $attributeConfiguration = $attribute->getConfiguration();
            foreach ($attribute->getTranslations() as $attributeTranslation) {
                if ($attributeValueCode !== $attributeTranslation->getLocale()) {
                    continue;
                }
                $attributeValue = $attributeValue->getValue();

                $propertyName = $this->attributeNameResolver->resolvePropertyName(
                    \sprintf('%s_%s', $attributeCode, $attributeTranslation->getLocale()
                    )
                );
                $values = $this->resolveProductAttribute(
                    $attributeConfiguration,
                    $attributeValue,
                    $attributeTranslation
                );

                $document->set($propertyName, $values);
            }
        }
    }

    private function resolveProductAttribute(array $attributeConfiguration, $attributeValue, AttributeTranslation $attribute): array
    {
        if ($attribute->getTranslatable()->getType() === 'select') {
            $choices = $attributeConfiguration['choices'];
            if (is_array($attributeValue)) {
                foreach ($attributeValue as $i => $item) {
                    $attributeValue[$i] = $choices[$item][$attribute->getLocale()] ?? $item;
                }
            } else {
                $attributeValue = $choices[$attributeValue][$attribute->getLocale()] ?? $attributeValue;
            }
        }

        $attributes = [];
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $singleElement) {
                $attributes[] = $this->stringFormatter->formatToLowercaseWithoutSpaces((string) $singleElement);
            }
        } else {
            $attributeValue = is_string($attributeValue) ? $this->stringFormatter->formatToLowercaseWithoutSpaces($attributeValue) : $attributeValue;
            $attributes[] = $attributeValue;
        }

        return $attributes;
    }
}
