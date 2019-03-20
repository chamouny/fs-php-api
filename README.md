# FormSynergy.com API PHP library

A PHP library to manage Form Synergy services.

## Install using composer
```bash
composer require form-synergy/php-api
```

## Include the library
```php
require '/vendor/autoload.php';
```

##  Enable session management
```PHP
\FormSynergy\Session::enable();
```

## Import the FormSynergy class
```PHP
use \FormSynergy\Init as FS;
```


## Configuration
You will need to retrieve:
* Profile ID
* API Key
* API Secret

If you are a reseller
* Reseller ID

To retrieve your credentials, you will need to sign to the Form Synergy Console: https://formsynergy.com/console/

To register a new account: https://formsynergy.com/register/



```PHP
FS::Config([
    'version' => 'v1',
    'protocol' => 'https',
    'endpoint' => 'api.formsynergy.com',
    'apikey' => '...',
    'secretkey' => '...',
    'max_auth_count' => 15,
]);
```

## Enable local storage to store downloads and responses. 
```PHP
FS::Storage( PROJECT_DIR, 'local-storage' );
```

## Load account profile

```PHP
$api = FS::Api()->Load('...');
```
