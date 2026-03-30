# ClamAV Virus Validator For Laravel

[![Code Coverage](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/sunspikes/clamav-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/clamav-validator)
[![Latest Stable Version](https://poser.pugx.org/sunspikes/clamav-validator/v/stable)](https://packagist.org/packages/sunspikes/clamav-validator)
[![License](https://poser.pugx.org/sunspikes/clamav-validator/license)](https://packagist.org/packages/sunspikes/clamav-validator)

A custom Laravel virus validator based on ClamAV anti-virus scanner for file uploads.

* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Author](#author)

<a name="requirements"></a>
## Requirements

- PHP >= 8.0
- Laravel 9.x, 10.x, 11.x, 12.x, or 13.x
- ClamAV anti-virus scanner running on the server

You can see the ClamAV installation instructions on the official [ClamAV documentation](http://www.clamav.net/documents/installing-clamav).

For example on an Ubuntu machine, you can do:

```sh
# Install clamav virus scanner
sudo apt-get update && sudo apt-get install -y clamav-daemon

# Update virus definitions
sudo freshclam

# Start the scanner service
sudo systemctl enable --now clamav-daemon clamav-freshclam
```

This package is not tested on Windows, but if you have ClamAV running (usually on port 3310) it should work.
You will also need to have `sockets` extension installed and enabled (all executions without this module will fail with this error - `"Use of undefined constant 'AF_INET'"`).

<a name="installation"></a>
## Installation

#### 1. Install the package through [Composer](http://getcomposer.org).

   ```bash
   composer require sunspikes/clamav-validator
   ```

#### 2. Publish assets from the vendor package

##### Config file

The default configuration file does use `ENV` to override the defaults. If you want to change the configuration file
anyway you run the following command to publish the package config file:

    php artisan vendor:publish --provider="Sunspikes\ClamavValidator\ClamavValidatorServiceProvider" --tag=config

Once the command is finished you should have a `config/clamav.php` file that will be used as well.

##### Language files

If you want to customize the translation or add your own language you can run the following command to
publish the language files to a folder you maintain:

    php artisan vendor:publish --provider="Sunspikes\ClamavValidator\ClamavValidatorServiceProvider" --tag=lang

This will copy the language files to `lang/vendor/clamav-validator`.

<a name="configuration"></a>
## Configuration

The package can be configured using environment variables:

| Environment Variable | Default | Description |
|---|---|---|
| `CLAMAV_PREFERRED_SOCKET` | `unix_socket` | Socket type: `unix_socket` or `tcp_socket` |
| `CLAMAV_UNIX_SOCKET` | `/var/run/clamav/clamd.ctl` | Path to the ClamAV unix socket |
| `CLAMAV_TCP_SOCKET` | `tcp://127.0.0.1:3310` | TCP socket connection string |
| `CLAMAV_SOCKET_CONNECT_TIMEOUT` | `null` | Connection timeout in seconds (`null` = no limit) |
| `CLAMAV_SOCKET_READ_TIMEOUT` | `30` | Read timeout in seconds |
| `CLAMAV_CLIENT_EXCEPTIONS` | `false` | Throw exceptions on scan failures instead of returning validation failure |
| `CLAMAV_SKIP_VALIDATION` | `false` | Skip virus scanning entirely (useful for local development) |

<a name="usage"></a>
## Usage

Use it like any `Validator` rule:

```php
$rules = [
    'file' => 'required|file|clamav',
];
```

Or in a Form Request:

```php
class UploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|clamav',
        ];
    }
}
```

`ClamavValidator` will automatically run multiple files one-by-one through ClamAV in case `file` represents multiple uploaded files.

<a name="author"></a>
## Author

Krishnaprasad MG [@sunspikes] and other [awesome contributors](https://github.com/sunspikes/clamav-validator/graphs/contributors)
