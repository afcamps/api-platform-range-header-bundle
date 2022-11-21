# API Platform Range header Bundle

[![Build](https://github.com/afcamps/api-platform-range-header-bundle/actions/workflows/build.yml/badge.svg)](https://github.com/afcamps/api-platform-range-header-bundle/actions/workflows/build.yml)

Use the range header request and response for paginate resources [RFC 7233](https://www.rfc-editor.org/rfc/rfc7233)

***this bundle uses the new Api resources metadata*** 

[Migrate API Platform 2.7 to 3.0](https://api-platform.com/docs/core/upgrade-guide/#api-platform-2730)

[New Metadata System](https://api-platform.com/docs/core/upgrade-guide/#metadata-changes)

[Metadata backward compatibility](https://api-platform.com/docs/core/upgrade-guide/#the-metadata_backward_compatibility_layer-flag)

**Compatibility:** 

| PHP Version | APIP Version |
|-------------|--------------|
| 8.0         | ^2.7         |
| 8.1         | ^2.7         |
| 8.1         | ^3.0         |

## Getting Started

### Installation

You can install this bundle by composer

```shell
composer require campingcom/api-platform-range-header-bundle
```

Then, the bundle should be registered. Juste verify that `config\bundles.php` is containing:

```php 
Campings\Bundle\ApiPlatformRangeHeaderBundle\ApiPlatformRangeHeaderBundle::class => ['all' => true ],
```

### Configuration

In `config/packages/api_platform_range_header.yaml` , you can configure this options:

```yaml
api_platform_range_header:
  defaults:
    range_header_enabled: true
    range_unit: "items"
    count_total_items: true
```

You could override this configuration by Api Resources with api platorm metadata and this new key extra properties:

```php
<?php 

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(extraProperties: ['range_header_enabled' => true, 'range_unit' => 'books'])]
class Book {
}
```


### Usages

Add the range request header with your range unit configured as above or use the default `items` range unit.

```shell
 curl -X 'GET'
  'http://localhost/api/books?page=1'
  -H 'accept: application/json' \
  -H 'Range: books=1-10  
  
  #Response headers
  HTTP/1.1 206 Partial Content
  Accept-ranges: books
  Content-Range: books 1-10/100
```

```shell
 curl -X 'GET'
  'http://localhost/api/books?page=1'
  -H 'accept: application/json' \
  -H 'Range: books=1000-1001
  
  #Response headers
  HTTP/1.1 416 Range Not Satisfiable
  Accept-ranges: books
```
