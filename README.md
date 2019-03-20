# FormSynergy.com API PHP library

A PHP library to manage Form Synergy services.

## Install using composer
```bash
require '/vendor/autoload.php';
```

##  Enable session management
```PHP
\FormSynergy\Session::enable();
```

## Initialize FormSynergy class
```PHP
use \FormSynergy\Init as FS;
```


## Configuration
In order to establish a successful connection, update the configuration.
* Profile ID
* API Key
* API Secret
If you are a reseller
* Reseller ID

```PHP
FS::Config([
    'version' => 'v1',
    'protocol' => 'https',
    'endpoint' => 'api.formsynergy.com',
    'apikey' => '<apikey>',
    'secretkey' => '<apisecret>',
    'max_auth_count' => 15,
]);
```
## Create a local storage directory, to store downloads
```PHP
FS::Storage( PROJECT_DIR, 'local-storage' );
```
## Load account profile
```PHP
$api = FS::Api()->Load('<profileid>');
```
