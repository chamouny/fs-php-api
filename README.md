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

## Import the Form Synergy class
```PHP
use \FormSynergy\Fs as FS;
```

You will need to retrieve your credentials in the Form Synergy console.

Console Access: https://formsynergy.com/console/

- $profileid = '';
- $apikey = '';
- $apisecret = '';

If you are a reseller
- $resellerid = '';


## Configuration
```PHP
FS::Config([
    'version' => 'v1',
    'protocol' => 'https',
    'endpoint' => 'api.formsynergy.com',
    'apikey' => $apikey,
    'secretkey' => $secretkey,
    'max_auth_count' => 15,
]);
```

## local storage
Enable local storage to store downloads and responses. 
```PHP
FS::Storage( '/', 'local-storage' );
```

## Load account
Load and start managing an account.
```PHP
$api = FS::Api()->Load($profileid);
```

## Add a domain
```PHP
$api->Create('website')
    ->Attributes([
        'name' => 'MyWebsite',
        'domain' => 'example.website.ltd',
        'proto' => 'https://'
    ])
    ->As('website');
```

## Meta tag
Add the site id meta tag as follow
```PHP
<meta name="fs:siteid" content="<?php echo $api->_website('siteid');?>">
```  

## Verify
```PHP
$api->Get('website')
    ->Where([
        'siteid' => $api->_website('siteid')
    ])
    ->verify();
```  

## Create a strategy
A strategy is composed of modules and objectives.
```PHP
$api->Create('strategy')
    ->Attributes([
        'name' => 'Default strategy',
        'siteid' => $api->_website('siteid')
    ])
    ->As('defaultStrategy');
```

## Creating modules
Modules are bundles composed of a subject, body and form inputs. Each module can be customized to handle events and responses individually. Modules can be chain linked together to create contiuous interactions. <a href="https://formsynergy.com/documentation/modules/">API documentation</a>



## Create an objective
An objective allows you to define notification methods, and goals based on obtained information. <a href="https://formsynergy.com/documentation/objectives/">API documentation</a>
