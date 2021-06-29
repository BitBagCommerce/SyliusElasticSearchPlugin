<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Repository;

use Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface as BaseProductVariantRepositoryInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;

final class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    /** @var BaseProductVariantRepositoryInterface|EntityRepository */
    private $baseProductVariantRepository;

    public function __construct(BaseProductVariantRepositoryInterface $baseProductVariantRepository)
    {
        $this->baseProductVariantRepository = $baseProductVariantRepository;
    }

    public function findOneByOptionValue(ProductOptionValueInterface $productOptionValue): array
    {
        return $this->baseProductVariantRepository->createQueryBuilder('o')
            ->where(':optionValue MEMBER OF o.optionValues')
            ->setParameter('optionValue', $productOptionValue)
            ->getQuery()
            ->getResult()
        ;
    }
}
