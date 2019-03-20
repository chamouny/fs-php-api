# php-api
FormSynergy.com API PHP library

require '/vendor/autoload.php';

\FormSynergy\Session::enable();
use \FormSynergy\Init as FS;
/**
 * You will need to provide the following in order to establish a connection.
 * - Profile ID
 * - API Key
 * - Secret Key
 * If you are a reseller you should include the
 * - Reseller ID
 *
 * Add credentials to configuration
 */
FS::Config([
    'version' => 'v1',
    'protocol' => 'https',
    'endpoint' => 'api.formsynergy.com',
    'apikey' => $apikey,
    'secretkey' => $secret,
    'max_auth_count' => 15,
]);
/**
 * Create a local storage directory, to store downloads.
 */
FS::Storage( PROJECT_DIR, 'local-storage' );
/**
 * Resellers simply change the profile id to manage different accounts.
 */
$api = FS::Api()->Load($profileid);
