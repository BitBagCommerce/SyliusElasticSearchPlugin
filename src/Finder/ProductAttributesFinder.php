<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Finder;

use BitBag\SyliusElasticsearchPlugin\QueryBuilder\QueryBuilderInterface;
use FOS\ElasticaBundle\Finder\FinderInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final class ProductAttributesFinder implements ProductAttributesFinderInterface
{
    /** @var FinderInterface */
    private $attributesFinder;

    /** @var QueryBuilderInterface */
    private $attributesByTaxonQueryBuilder;

    /** @var string */
    private $taxonsProperty;

    /** @var int */
    private $filterMax;

    public function __construct(
        FinderInterface $attributesFinder,
        QueryBuilderInterface $attributesByTaxonQueryBuilder,
        string $taxonsProperty,
        int $filterMax = 20
    ) {
        $this->attributesFinder = $attributesFinder;
        $this->attributesByTaxonQueryBuilder = $attributesByTaxonQueryBuilder;
        $this->taxonsProperty = $taxonsProperty;
        $this->filterMax = $filterMax;
    }

    public function findByTaxon(TaxonInterface $taxon): ?array
    {
        $data = [];
        $data[$this->taxonsProperty] = strtolower($taxon->getCode());

        $query = $this->attributesByTaxonQueryBuilder->buildQuery($data);

        return $this->attributesFinder->find($query, $this->filterMax);
    }
}
