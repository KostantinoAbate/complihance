# Laravel package for cookie consent, policies and consent tracking

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kostantinoabate/complihance.svg?style=flat-square)](https://packagist.org/packages/kostantinoabate/complihance)
[![GitHub Tests Action Status](https://github.com/spatie/package-complihance-laravel/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kostantinoabate/complihance/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/spatie/package-complihance-laravel/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/kostantinoabate/complihance/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kostantinoabate/complihance.svg?style=flat-square)](https://packagist.org/packages/kostantinoabate/complihance)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/complihance.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/complihance)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require kostantinoabate/complihance
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="complihance-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="complihance-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="complihance-views"
```

## Usage

```php
$complihance = new KostantinoAbate\Complihance();
echo $complihance->echoPhrase('Hello, KostantinoAbate!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kostantino Abate](https://github.com/kostantinoabate)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
