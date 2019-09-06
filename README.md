# ClamAV Virus Validator For Laravel

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/80f28825-1385-4daa-aaad-0e4c6b6b3910/mini.png)](https://insight.sensiolabs.com/projects/80f28825-1385-4daa-aaad-0e4c6b6b3910)
[![Code Coverage](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/clamav-validator)
[![Build Status](https://travis-ci.org/sunspikes/clamav-validator.svg?branch=master)](https://travis-ci.org/sunspikes/clamav-validator) 
[![Latest Stable Version](https://poser.pugx.org/sunspikes/clamav-validator/v/stable)](https://packagist.org/packages/sunspikes/clamav-validator)
[![License](https://poser.pugx.org/sunspikes/clamav-validator/license)](https://packagist.org/packages/sunspikes/clamav-validator)

A custom Laravel virus validator based on ClamAV anti-virus scanner for file uploads.

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [Author](#author)

<a name="requirements"></a> 
## Requirements

You must have ClamAV anti-virus scanner running on the server to make this package work.

You can see the ClamAV installation instructions on the official [ClamAV documentation](http://www.clamav.net/documents/installing-clamav).

For example on an Ubuntu machine, you can do:

```sh
# Install clamav virus scanner
sudo apt-get update
sudo apt-get install clamav-daemon

# Update virus definitions
sudo freshclam

# Start the scanner service
sudo service clamav-daemon start
```

This package is not tested on windows, but if you have ClamAV running (usually on port 3310) it should work.

<a name="installation"></a>
## Installation

#### 1. Install the package through [Composer](http://getcomposer.org).
   
   ```bash
   $ composer require sunspikes/clamav-validator
   ```

#### 2. Add the service provider (for Laravel 5.4 or below)

This package supports Laravel new [Package Discovery](https://laravel.com/docs/5.5/packages#package-discovery).
    
If you are using Laravel < 5.5, you need to add `Sunspikes\ClamavValidator\ClamavValidatorServiceProvider::class` to your `providers` array in `config/app.php`:

```php
'providers' => [
	// ...

	Sunspikes\ClamavValidator\ClamavValidatorServiceProvider::class,
],
```
#### 3. Publish assets from the the vendor package

##### Config file

The default configuration file does use `ENV` to override the defaults. If you want to change the configuration file 
anyway you run the following command to publish the package config file:

    php artisan vendor:publish --provider="Sunspikes\ClamavValidator\ClamavValidatorServiceProvider" --tag=config

Once the command is finished you should have a `config/clamav.php` file that will be used as well.

##### Language files

If you want to customize the translation or add your own language you can run the the following command to
publish the language files to a folder you maintain:

    php artisan vendor:publish --provider="Sunspikes\ClamavValidator\ClamavValidatorServiceProvider" --tag=lang

This will copy the language files to `resources/lang/vendor/clamav-validator` for Laravel >= 5.1

<a name="usage"></a>
## Usage

Use it like any `Validator` rule:

```php
$rules = [
    'my_file_field' => 'clamav',
];
```

<a name="author"></a>
## Author

Krishnaprasad MG [@sunspikes] and other [awesome contributors](https://github.com/sunspikes/clamav-validator/graphs/contributors)

_Contact me at [sunspikes at gmail dot com]_
