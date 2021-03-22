<?php

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Finder;

interface FinderExcludable
{
    public function isFilterExcluded(): bool;
}
