<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Form\Type\ChoiceMapper;

use BitBag\SyliusElasticsearchPlugin\Formatter\StringFormatterInterface;
use BitBag\SyliusElasticsearchPlugin\Repository\ProductAttributeValueRepositoryInterface;
use Sylius\Component\Attribute\AttributeType\SelectAttributeType;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;

final class ProductAttributesMapper implements ProductAttributesMapperInterface
{
    /** @var ProductAttributeValueRepositoryInterface */
    private $productAttributeValueRepository;

    /** @var LocaleContextInterface */
    private $localeContext;

    /** @var StringFormatterInterface */
    private $stringFormatter;

    public function __construct(
        ProductAttributeValueRepositoryInterface $productAttributeValueRepository,
        LocaleContextInterface $localeContext,
        StringFormatterInterface $stringFormatter
    ) {
        $this->productAttributeValueRepository = $productAttributeValueRepository;
        $this->localeContext = $localeContext;
        $this->stringFormatter = $stringFormatter;
    }

    public function mapToChoices(ProductAttributeInterface $productAttribute): array
    {
        $configuration = $productAttribute->getConfiguration();

        if (isset($configuration['choices']) && is_array($configuration['choices'])) {
            $choices = [];
            foreach ($configuration['choices'] as $singleValue => $val) {
                $label = $configuration['choices'][$singleValue][$this->localeContext->getLocaleCode()];
                $singleValue = SelectAttributeType::TYPE === $productAttribute->getType() ? $label : $singleValue;
                $choice = $this->stringFormatter->formatToLowercaseWithoutSpaces($singleValue);
                $choices[$label] = $choice;
            }

            return $choices;
        }

        $attributeValues = $this->productAttributeValueRepository->getUniqueAttributeValues($productAttribute);

        $choices = [];
        array_walk($attributeValues, function ($productAttributeValue) use (&$choices, $productAttribute): void {
            $value = $productAttributeValue['value'];

            $configuration = $productAttribute->getConfiguration();

            if (is_array($value)
                && isset($configuration['choices'])
                && is_array($configuration['choices'])
            ) {
                foreach ($value as $singleValue) {
                    $choice = $this->stringFormatter->formatToLowercaseWithoutSpaces($singleValue);
                    $label = $configuration['choices'][$singleValue][$this->localeContext->getLocaleCode()];
                    $choices[$label] = $choice;
                }
            } else {
                $choice = is_string($value) ? $this->stringFormatter->formatToLowercaseWithoutSpaces($value) : $value;
                $choice = is_bool($value) ? var_export($value, true) : $choice;
                $choices[$value] = $choice;
            }
        });
        unset($attributeValues);

        return $choices;
    }
}
