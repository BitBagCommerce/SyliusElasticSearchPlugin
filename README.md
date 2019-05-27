<h1 align="center">
    <a href="http://bitbag.shop" target="_blank">
        <img src="doc/logo.png" width="55%" />
    </a>
    <br />
    <a href="https://packagist.org/packages/bitbag/elasticsearch-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/bitbag/elasticsearch-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/bitbag/elasticsearch-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/bitbag/elasticsearch-plugin.svg" />
    </a>
    <a href="http://travis-ci.org/BitBagCommerce/SyliusElasticsearchPlugin" title="Build status" target="_blank">
            <img src="https://img.shields.io/travis/BitBagCommerce/SyliusElasticsearchPlugin/master.svg" />
        </a>
    <a href="https://scrutinizer-ci.com/g/BitBagCommerce/SyliusElasticsearchPlugin/" title="Scrutinizer" target="_blank">
        <img src="https://img.shields.io/scrutinizer/g/BitBagCommerce/SyliusElasticsearchPlugin.svg" />
    </a>
    <a href="https://packagist.org/packages/bitbag/elasticsearch-plugin" title="Total Downloads" target="_blank">
        <img src="https://poser.pugx.org/bitbag/elasticsearch-plugin/downloads" />
    </a>
    <p>
        <img src="https://sylius.com/assets/badge-approved-by-sylius.png" width="85">
    </p>
</h1>

## BitBag SyliusElasticsearchPlugin

Working [Sylius](https://sylius.com/) [Elasticsearch](https://www.elastic.co/products/elasticsearch) integration based on [FOSElasticaBundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle). The main goal of this plugin is to support filtering products by 
options, attributes, taxons, channels and name in the front product list page. It totally replaces the default Sylius `sylius_shop_product_index`
route.

If you are curious about the details of this plugin, read [this Medium blog post](https://medium.com/@BitBag/using-sylius-and-elasticsearch-for-a-products-filter-6dc9f0ce929) for more details.

What is more, the plugin has a nice Sylius-oriented architecture that allows mapping resources to the Elastic document easier. It is flexible as well,
so that you can customize the existing features for your specific business needs.   

## Support

You can order our support on [this page](https://bitbag.shop/products/sylius-mailchimp).

We work on amazing eCommerce projects on top of Sylius and other great Symfony based solutions, like eZ Platform, Akeneo or Pimcore.
Need some help or additional resources for a project? Write us an email on mikolaj.krol@bitbag.pl or visit
[our website](https://bitbag.shop/)! :rocket:

## Demo

We created a demo app with some useful use-cases of the plugin! Visit [demo.bitbag.shop](https://demo.bitbag.shop/en_US/products-list/t-shirts) to take a look at it. 
The admin can be accessed under [demo.bitbag.shop/admin](https://demo.bitbag.shop/admin) link and `sylius: sylius` credentials.

## Installation

If you use Sylius 1.4, you might get a compatibility issue for Pagerfanta. Please read [this issue](https://github.com/BitBagCommerce/SyliusElasticsearchPlugin/issues/23) in order to proceed with a workaround. 

```bash
$ composer require bitbag/elasticsearch-plugin
```
    
    
Add plugin dependencies to your `config/bundles.php` file:
```php
return [
    ...

    FOS\ElasticaBundle\FOSElasticaBundle::class => ['all' => true],
    BitBag\SyliusElasticsearchPlugin\BitBagSyliusElasticsearchPlugin::class => ['all' => true],
];
```

Import required config in your `config/packages/_sylius.yaml` file:
```yaml
# config/packages/_sylius.yaml

imports:
    ...
    
    - { resource: "@BitBagSyliusElasticsearchPlugin/Resources/config/config.yml" }
```

Import routing **on top** of your `config/routes.yaml` file:
```yaml
# config/routes.yaml

bitbag_sylius_elasticsearch_plugin:
    resource: "@BitBagSyliusElasticsearchPlugin/Resources/config/routing.yml"
```

...and set up the redirection from the default Sylius shop products index page on top of your `config/routes/sylius_shop.yaml`
file.
```
# config/routes/sylius_shop.yaml

redirect_sylius_shop_product_index:
    path: /{_locale}/taxons/{slug}
    controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction
    defaults:
        route: bitbag_sylius_elasticsearch_plugin_shop_list_products
        permanent: true
    requirements:
        _locale: ^[a-z]{2}(?:_[A-Z]{2})?$
        slug: .+
```
...and update installed assets with the following command:
```
$ bin/console assets:install
```
...and remove the default ElasticSearch index (`app`) defined by `FOSElasticaBundle` in `config/packages/fos_elastica.yaml`:
```

fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }
    indexes:
        app: ~
```
should become:
```

fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }
```
In the end, with an elasticsearch server running, execute following command:
```
$ bin/console fos:elastica:populate
```

**Note:** If you are running it on production, add the `-e prod` flag to this command. Elastic are created with environment suffix.

## Usage

### Rendering the shop products list

When you go now to the `/{_locale}/products/taxon/{slug}` page, you should see a totally new set of filters. You should see something like this:

<div align="center">
    <img src="https://raw.githubusercontent.com/bitbager/BitBagCommerceAssets/master/BitBagElasticesearchProductIndex.jpg" />
</div>

You might also want to refer the horizontal menu to a new product list page. Follow below instructions to do so:

1. If you haven't done it yet, create two files:
    * `_horizontalMenu.html.twig` in `templates/bundles/SyliusShopBundle/Taxon` directory
    * `_breadcrumb.html.twig` in `templates/bundles/SyliusShopBundle/Product/Show` directory
2. Paste into those files content of respectively `vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/views/Taxon/_horizontalMenu.html.twig` and `vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/views/Product/Show/_breadcrumb.html.twig` files, replacing `sylius_shop_product_index` with `bitbag_sylius_elasticsearch_plugin_shop_list_products` in both of them.
3. Clean your cache with `bin/console cache:clear` command.
4. :tada:

If you're using vertical menu - follow steps above with `_verticalMenu.html.twig` file instead. It's in the same directory as the `horizontal_menu.html.twig` file.

**Be aware! Elasticsearch does not handle dashes well. This plugin depends on the code field in Sylius resources. Please use underscores instead of dashes in your code fields.**

### Excluding options and attributes in the filter menu

You might not want to show some specific options or attributes in the menu. You can set specific parameters for that:
```yml
parameters:
    bitbag_es_excluded_filter_options: []
    bitbag_es_excluded_filter_attributes: ['book_isbn', 'book_pages']
```

By default all options and attributes are indexed. After you change these parameters, remember to run `bin/console fo:el:po` command again
(a shortcut for `fos:elastica:populate`).

### Reindexing

By default, current indexes listen on all Doctrine events. You can override this setting for each index by overriding index definition in your `config.yml` file:
```yml
fos_elastica:
    indexes:
        bitbag_attribute_taxons:
            types:
                default:
                    persistence:
                        listener:
                            insert: true
                            update: false
                            delete: true
```

Indexes with `bitbag_shop_product`, `bitbag_attribute_taxons` and `bitbag_option_taxons` keys are available so far.

## Customization

### Available services you can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) and forms you can [extend](http://symfony.com/doc/current/form/create_form_type_extension.html)
```bash
$ bin/console debug:container | grep bitbag_sylius_elasticsearch_plugin
```

### Parameters you can override in your parameters.yml(.dist) file
```bash
$ bin/console debug:container --parameters | grep bitbag
```

## Testing
```bash
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run gulp
$ bin/console assets:install public -e test
$ bin/console doctrine:schema:create -e test
$ bin/console server:run 127.0.0.1:8080 -d public -e test
$ elasticsearch
$ open http://localhost:8080
$ vendor/bin/behat
$ vendor/bin/phpspec run
```

## Contribution

Learn more about our contribution workflow on http://docs.sylius.org/en/latest/contributing/.
