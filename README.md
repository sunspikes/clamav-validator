# ClamAV Validator Rules For Laravel 4

Custom Laravel 4 Validator for file upload ClamAV anti-virus check.

**Note:** this package requires [PHP-ClamAV extension](http://php-clamav.sourceforge.net/).

* [Installation](#installation)
* [Usage](#usage)
* [Copyright and License](#copyright)

<a name="installation"></a>
## Installation

Install and configure the PHP ClamAV extension from [Sourceforge](http://php-clamav.sourceforge.net/)

Install the package through [Composer](http://getcomposer.org).

In your `composer.json` file:

```json
{
	"require": {
		"laravel/framework": ">=4.1.21",
		// ...
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


<a name="copyright"></a>
## Copyright and License

Copyright 2014 Krishnaprasad MG