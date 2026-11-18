# Product Category Url Fix
Fix Magento 2 core behaviour on product category url generation.
By default Magento 2 generates product urls containing categories even if _Use Categories Path for Product URLs_ is turned off.
This modules prevents the generation of those urls if the aforementioned setting is set to _No_.

[![Tests](https://github.com/aune-io/magento2-product-category-url-fix/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/aune-io/magento2-product-category-url-fix/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/v/stable)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)
[![Latest Unstable Version](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/v/unstable)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)
[![Total Downloads](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/downloads)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)
[![License](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/license)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)

## System requirements
This extension supports the following versions:

*	Magento Open Source / Adobe Commerce 2.4.6, 2.4.7, 2.4.8 and 2.4.9
*	PHP 8.2, 8.3, 8.4 and 8.5 (as far as each Magento release supports them)

Older Magento (2.2, 2.3, 2.4.0 - 2.4.5) and PHP (7.x, 8.0, 8.1) versions are no longer supported.
The last release supporting them is the one tagged before this change; use that if you are still on those versions.

## Installation
1. Require the module via Composer
```bash
$ composer require aune-io/magento2-product-category-url-fix
```

2. Enable the module
```bash
$ bin/magento module:enable Aune_ProductCategoryUrlFix
$ bin/magento setup:upgrade
```

3. Login to the admin
4. Go to Stores > Configuration > Catalog > Catalog > Search Engine Optimization
5. Set _Use Categories Path for Product URLs_ to _No_, or to use the default value

Product category urls won't be generated for new products, plus old urls will be deleted for a product when it is saved after a change associated categories or websites.

## Authors, contributors and maintainers

Author:
- [Renato Cason](https://github.com/renatocason)

## License
Licensed under the Open Software License version 3.0
