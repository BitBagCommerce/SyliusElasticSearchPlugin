<?php
declare(strict_types=1);

namespace BitBag\SyliusElasticsearchPlugin\Form\Type;

use BitBag\SyliusElasticsearchPlugin\Facet\RegistryInterface;
use BitBag\SyliusElasticsearchPlugin\Model\Box;
use BitBag\SyliusElasticsearchPlugin\Model\Search;
use Elastica\Query;
use Elastica\Query\MultiMatch;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    /**
     * @var PaginatedFinderInterface
     */
    private $finder;
    /**
     * @var RegistryInterface
     */
    private $facetRegistry;

    public function __construct(PaginatedFinderInterface $finder, RegistryInterface $facetRegistry)
    {
        $this->finder = $finder;
        $this->facetRegistry = $facetRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('box', SearchBoxType::class, ['label' => false])
        ;

        $formModifier = function (FormInterface $form, AdapterInterface $adapter) {
            if (!$adapter instanceof FantaPaginatorAdapter) {
                return;
            }

            $form->add('facets', SearchFacetsType::class, ['facets' => $adapter->getAggregations(), 'label' => false]);
        };

        $builder
            ->get('box')
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    /** @var Box $data */
                    $data = $event->getForm()->getData();

                    if (!$data->getQuery()) {
                        return;
                    }

                    $multiMatch = new MultiMatch();
                    $multiMatch->setQuery($data->getQuery());
                    // TODO set search fields here (pay attention to locale-contex field, like name): $query->setFields([]);
                    $multiMatch->setFuzziness('AUTO');
                    $query = new Query($multiMatch);

                    foreach ($this->facetRegistry->getFacets() as $facetId => $facet) {
                        $query->addAggregation($facet->getAggregation()->setName($facetId));
                    }
                    $query->setSize(0);

                    $results = $this->finder->findPaginated($query);

                    if ($results->getAdapter()) {
                        $formModifier($event->getForm()->getParent(), $results->getAdapter());
                    }
                }
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'bitbag_elasticsearch_search';
    }
}
