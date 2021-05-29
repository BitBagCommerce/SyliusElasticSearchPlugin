<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Controller\Action\Shop;

use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\DataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\PaginationDataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\SortDataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Finder\ShopProductsFinderInterface;
use BitBag\SyliusElasticsearchPlugin\Form\Type\ShopProductsFilterType;
use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListProductsAction
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var DataHandlerInterface */
    private $shopProductListDataHandler;

    /** @var SortDataHandlerInterface */
    private $shopProductsSortDataHandler;

    /** @var PaginationDataHandlerInterface */
    private $paginationDataHandler;

    /** @var ShopProductsFinderInterface */
    private $shopProductsFinder;

    /** @var Environment */
    private $twig;

    public function __construct(
        FormFactoryInterface $formFactory,
        DataHandlerInterface $shopProductListDataHandler,
        SortDataHandlerInterface $shopProductsSortDataHandler,
        PaginationDataHandlerInterface $paginationDataHandler,
        ShopProductsFinderInterface $shopProductsFinder,
        Environment $twig
    ) {
        $this->formFactory = $formFactory;
        $this->shopProductListDataHandler = $shopProductListDataHandler;
        $this->shopProductsSortDataHandler = $shopProductsSortDataHandler;
        $this->paginationDataHandler = $paginationDataHandler;
        $this->shopProductsFinder = $shopProductsFinder;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->createNamed(null, ShopProductsFilterType::class);
        $form->handleRequest($request);

        $requestData = array_merge(
            $form->getData(),
            $request->query->all(),
            ['slug' => $request->get('slug')]
        );
        $data = array_merge(
            $this->shopProductListDataHandler->retrieveData($requestData),
            $this->shopProductsSortDataHandler->retrieveData($requestData),
            $this->paginationDataHandler->retrieveData($requestData)
        );

        $template = $request->get('template');
        $products = $this->shopProductsFinder->find($data);

        return $this->templatingEngine->renderResponse($template, [
            'form' => $form->createView(),
            'products' => $products,
            'taxon' => $data['taxon'],
        ]);
    }
}
