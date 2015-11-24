# ClamAV Validator For Laravel 5

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/80f28825-1385-4daa-aaad-0e4c6b6b3910/mini.png)](https://insight.sensiolabs.com/projects/80f28825-1385-4daa-aaad-0e4c6b6b3910)
[![Code Coverage](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/clamav-validator)
[![Build Status](https://travis-ci.org/sunspikes/clamav-validator.svg?branch=master)](https://travis-ci.org/sunspikes/clamav-validator) 
[![Latest Stable Version](https://poser.pugx.org/sunspikes/clamav-validator/v/stable)](https://packagist.org/packages/sunspikes/clamav-validator)
[![License](https://poser.pugx.org/sunspikes/clamav-validator/license)](https://packagist.org/packages/sunspikes/clamav-validator)

Custom Laravel 5 anti-virus validator for file uploads.

* [Installation](#installation)
* [Usage](#usage)
* [Change Log](#changelog)
* [Author](#author)

<a name="installation"></a>
## Installation

Install the package through [Composer](http://getcomposer.org).

In your `composer.json` file:

```json
{
	"require": {
		"laravel/framework": ">=4.1.21",
		"sunspikes/clamav-validator": "dev-master"
	}
}
```

**Note:** the minimum version of Laravel that's supported is 4.1.21. 

Run `composer install` or `composer update` to install the package.

Add the following to your `providers` array in `app/config/app.php`:

```php
'providers' => array(
	// ...

	'Sunpikes\ClamavValidator\ClamavValidatorServiceProvider',
),
```


<a name="usage"></a>
## Usage

Use it like any `Validator` rule:

```php
$rules = array(
	'my_file_field' => 'clamav',
);
```


<a name="changelog"></a>
## Change Log

2014.12.05 - Initial version, using extension php-clamav

2014.12.05 - Removed the dependency php-clamav, Now using [Quahog](https://github.com/jonjomckay/quahog)

2015.10.20 - Updated for Laravel 5

2015.11.20 - Updated to use PSR-4

<a name="author"></a>
## Author

Krishnaprasad MG [@sunspikes]

_Contact me at [sunspikes at gmail dot com]_
