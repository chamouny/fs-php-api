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

You will need to retrieve your credentials in the Form Synergy console.

Console Access: https://formsynergy.com/console/

$profileid = '';
$apikey = '';
$apisecret = '';

If you are a reseller
$resellerid = '';




## Configuration
```PHP
FS::Config([
    'version' => 'v1',
    'protocol' => 'https',
    'endpoint' => 'api.formsynergy.com',
    'apikey' => $apikey,
    'secretkey' => $secretkey,
     //'resellerid' => $resellerid,  If you are a reseller
    'max_auth_count' => 15,
]);
```

## local storage
Enable local storage to store downloads and responses. 
```PHP
FS::Storage( PROJECT_DIR, 'local-storage' );
```

## Load account
To start managing an account, load the account in question by providing the profile id.
```PHP
$api = FS::Api()->Load($profileid);
```
## Features
 
