# Product Category Url Fix
Fix Magento 2 core behaviour on product category url generation.
By default Magento 2 generates product urls containing categories even if _Use Categories Path for Product URLs_ is turned off.
This modules prevents the generation of those urls if the aforementioned setting is set to _No_.

[![Build Status](https://travis-ci.org/aune-io/magento2-product-category-url-fix.svg?branch=master)](https://travis-ci.org/aune-io/magento2-product-category-url-fix)
[![Coverage Status](https://coveralls.io/repos/github/aune-io/magento2-product-category-url-fix/badge.svg?branch=master)](https://coveralls.io/github/aune-io/magento2-product-category-url-fix?branch=master)
[![Latest Stable Version](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/v/stable)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)
[![Latest Unstable Version](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/v/unstable)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)
[![Total Downloads](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/downloads)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)
[![License](https://poser.pugx.org/aune-io/magento2-product-category-url-fix/license)](https://packagist.org/packages/aune-io/magento2-product-category-url-fix)

## System requirements
This extension supports the following versions of Magento:

*	Community Edition (CE) versions 2.1.x, 2.2.x, 2.3.x and 2.4.x

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
