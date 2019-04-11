<?php
namespace FormSynergy;

/**
 * FormSynergy Api PHP Client.
 *
 * This client api can be used to manage and administer FormSynergy api accounts.
 *
 * For fast and easy development this Api, simplifies the registration of domains, along with strategies and modules.
 * Take a quick look at the example-setup-php directory, this basic example can be improved to best fit your needs.
 *
 * @author     Joseph G. Chamoun <formsynergy@gmail.com>
 * @copyright  2019 FormSynergy.com
 * @licence    https://github.com/form-synergy/php-api/blob/dev-master/LICENSE MIT
 */

/**
 *
 * Form Synergy Client Class
 *
 * @version 1.3.6
 */
class Client
{

    /**
     * Configuration of the api client
     *
     * @var array
     */
    private $config = [];

    /**
     * Internal array to manage variables
     *
     * @var array
     */
    private $internal = [];

    private $as = [];

    /**
     * Config()
     *
     * Configuration function to ser config values
     *
     * @param array $config
     * @return void
     */
    public function Config($config)
    {
        $this->config = $config;
        $this->_rel('max_auth_count', $config['max_auth_count']);
    }

    /**
     * _rel()
     *
     * Setter for rel
     *
     * @param sting $name
     * @param mixed $value
     * @return void
     */
    public function _rel($name, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->internal[$name][$k] = $v;
            }
            return $this;
        }

        $this->internal[$name] = $value;
        return $this;
    }

    /**
     * rel()
     *
     * Setter and getter, if a value is passed, the function will assume
     * the setter responsibilities.
     * If no value is present, it will assume the getter responsibilities.
     *
     * If the value is an array, the key and it's values will be appended
     * to the name.
     *
     * @uses Api::_rel()
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function rel($name, $value = null)
    {
        if ('reset' == $value) {
            $this->internal[$name] = is_string($this->internal[$name]) ? null : [];
            return;
        }

        if (is_null($value) && isset($this->internal[$name])) {
            return $this->internal[$name];
        }

        $this->_rel($name, $value);
        return $value;
    }

    /**
     * unrel()
     *
     * Will remove data stored in the internal object
     * @uses Api::unrel()
     * @return void
     */
    public function unrel($name, $key = null)
    {
        if (is_null($key) && isset($this->internal[$name])) {
            unset($this->internal[$name]);
        } elseif (isset($this->internal[$name][$key])) {
            unset($this->internal[$name][$key]);
        }
    }

    /**
     * options()
     *
     * The options will streamline the process of distributing variables
     * to the rel function.
     *
     * @uses Api::rel()
     * @param array $options
     * @return void
     */
    public function options($options)
    {
        foreach ($options as $key => $values) {
            $this->rel($key, $values);
        }
    }

    /**
     * Reseller()
     *
     * Will set the reseller account as master.
     *
     * @uses Api::options()
     * @param string $resellerid
     * @return void
     */
    public function Reseller($resellerid)
    {
        $this->options([
            'request' => [
                'reseller' => [
                    'resellerid' => $resellerid,
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Load()
     *
     * Will load the profile associated with an account.
     *
     * @uses Api::options()
     * @param string $profileid
     * @return void
     */
    public function Load($profileid)
    {
        $this->options([
            'request' => [
                'load' => [
                    'profileid' => $profileid,
                ],
            ],
        ]);

        return $this;
    }

    /**
     * Authenticate()
     *
     * Our Api infrastructure utilizes dynamic access point,
     * requiring automated authentication.
     *
     * This function will automatically provide the required
     * credentials.
     *
     * Once authentication is successful, a new access point,
     * will provide temporary access.
     *
     * @return void
     */
    public function Authenticate($authenticate = null)
    {

        // Get stored access point from session.
        $accessPoint = Session::get('AccessPoint');

        // Access point exists, try again on the next request.
        if ($accessPoint) {
            return;
        }

        /**
         * In certain cases an authenticate request disrupted ordinary requests,
         * we will move the request details to a temporary storage.
         **/
        $this->rel('temp', [
            'resource' => $this->rel('resource'),
            'method' => $this->rel('method'),
            'envelope' => $this->rel('envelope'),
        ]);

        /**
         * !Important: Do not pass the secret key directly.
         * The authentication process, will require the apikey, and a secret hash
         */

        // 1: Create a timestamp.
        $timestamp = time();

        // 2: Combine and hash the timestamp and the secretkey.
        // NOTE: md5 + SHA3-512 are required.
        $hash = md5(hash('SHA3-512', $timestamp . $this->config['secretkey']));
        if (is_null($authenticate)) {
            $authenticate = [
                'apikey' => $this->config['apikey'],
                'secrethash' => $hash,
                'timestamp' => $timestamp,
            ];
        }
        $this->options([
            'resource' => 'authenticate',
            'envelope' => 'authenticate',
            'request' => [
                'authenticate' => $authenticate,
            ],
        ]);

        $this->_transmit(true);
    }

    /**
     * _close_authentification_request()
     *
     * The authentication process was successful, we can resume activities.
     *
     * We previously stored request details in a temporary storage to prevent
     * service interruption, we can set the request back into position.
     *
     * @param string $accessPoint
     * @return void
     */
    public function _close_authentification_request($accessPoint)
    {
        $this->options([
            'method' => $this->rel('temp')['method'],
            'envelope' => $this->rel('temp')['envelope'],
            'resource' => $this->rel('temp')['resource'],

        ]);

        return $this;
    }

    /**
     * Get()
     *
     * Will prepare a Get request.
     *
     * NOTE: Get is used to set the resource we need to get.
     * Next, we can apply a query using Where.
     * This function can be used independently, however when updating or deleting
     * an object, Get must pressed and Update and Delete requests.
     *
     * @example:
     *   $api->Get( 'modules' )
     *
     *           ->Where([
     *                  'modid' => $modid
     *           ])
     *
     *           ->Update([
     *              'subject' => $subject,
     *               ...
     *           ]);
     *
     *   $response = $api->Response();
     *
     * @see https://... for more examples
     * @uses Api::Where()
     * @uses Api::options()
     * @param string $resource
     * @return void
     */
    public function Get($resource, $transmit = false)
    {
        $this->options([
            'resource' => $resource,
            'method' => 'GET',
            'envelope' => 'get',
        ]);
        return $this;
    }

    /**
     * Create()
     *
     * Will create a post request.
     *
     * NOTE: When sending a create request, any attributes related to
     * the create object must use the Attributes function.
     *
     * @example:
     *   $api->Create('leads')
     *
     *      ->Attributes([
     *          'fname' => '',
     *          'lname' => ''
     *      ]);
     *
     *   $response = $api->Response();
     *
     * @see https://... for more examples
     * @uses Api::Attributes()
     * @uses Api::options()
     * @param string $resource
     * @return void
     */
    public function Create($resource)
    {
        $this->options([
            'resource' => $resource,
            'method' => 'POST',
            'envelope' => 'create',
        ]);

        return $this;
    }

    /**
     * Delete()
     *
     * Will create a delete request.
     *
     * NOTE: When deleting an object, first we need to get the resource.
     *
     * 1) Get the resource:
     *      $api->Get( 'Resource name ')
     *
     * 2) Locate the object
     *      ->Where( array )
     *
     * 3) Complete the delete process
     *      ->Delete();
     *
     * @see https://... for more examples
     * @uses Api::Get()
     * @uses Api::Where()
     * @uses Api::options()
     * @uses Api::_transmit()
     * @return void
     */
    public function Delete()
    {
        $this->options([
            'objid' => $this->rel('objid'),
            'method' => 'DELETE',
            'envelope' => 'delete',
        ]);
        $this->_transmit(true);
        return $this;
    }

    /**
     * Find()
     *
     * Find can be used to get multiple objects.
     *
     * It also supports model based queries.
     *
     * It can be used in combination with:
     *  With() and Where().
     *
     * @example :
     *
     *      $api->Find('leads')
     *          ->With([
     *              'label' => 'example scoring model'
     *          ])
     *
     *          ->Where([
     *              'fname'=> [
     *                  'value' => 'joe',
     *                  'confirmed' => 'yes'
     *               ]
     *          ]);
     *
     *      $data = $api->Response()['data'];
     *
     * @see https://... for more examples
     * @uses Api::With()
     * @uses Api::Where()
     * @uses Api::options()
     * @param string $find
     * @return void
     */
    public function Find($find)
    {
        $this->options([
            'resource' => $find,
            'method' => 'GET',
            'envelope' => 'find',
        ]);

        return $this;
    }

    /**
     * With()
     *
     * Can be used in combination with:
     *      -Find()
     *
     * With() will apply the logic of a scoring model to the query.
     * At this time it can only be used to query leads.
     *
     * @param array $with
     * @uses Api::options()
     * @return void
     */
    public function With($with)
    {
        $this->options([
            'request' => [
                'with' => $with,
            ],
        ]);

        return $this;
    }

    /**
     * Where()
     *
     * Defines the terms of a request.
     * It is required when getting or finding object.
     *
     * @param array $where
     * @uses Api::options()
     * @uses Api::_transmit()
     * @return void
     */
    public function Where($where)
    {
        $this->options([
            'request' => [
                'where' => $where,
            ],
        ]);
        $this->_transmit();
        return $this;
    }

    /**
     * Verify()
     *
     * Once verification meta tag is included in the index page, verify the domain.
     *
     * @return void
     */
    public function Verify()
    {
        $this->_transmit();
        $this->options([
            'method' => 'PUT',
            'envelope' => 'verify',
            'request' => [
                'verify' => true,
                'objid' => $this->rel('objid'),
            ],
        ]);
        return $this;
    }

    /**
     * Scan()
     *
     * Once a domain has been verified, scan the domain in question to retrieve all etags.
     *
     * @return void
     */
    public function Scan()
    {
        $this->_transmit();
        $this->options([
            'method' => 'PUT',
            'envelope' => 'scan',
            'request' => [
                'scan' => true,
                'objid' => $this->rel('objid'),
            ],
        ]);

        return $this;
    }

    /**
     * Ready()
     *
     * Closure
     *
     * @param callable $fn
     * @return closure
     */
    public function Ready($fn)
    {
        $response = $this->Response();
        $fn($response);

        return $response;
    }

    /**
     * Then()
     *
     * Will renew Api and Secret key.
     * @param callable $fn
     * @return closure
     */
    public function Then($fn)
    {
        return $fn($this);
    }

    /**
     * __As()
     *
     * It store the response as a named keyword
     *
     * @use __call()
     * @use Api::_rel()
     * @param string $name
     * @return void
     */
    function as ($name, $index = null) {
        $response = $this->Response();
        $this->rel('_as', $name);
        $this->as[$name] = !is_null($index) ? $response['data'][$index] : $response['data'];

        return $this;
    }

    public function Export($resources)
    {
        $this->options([
            'resource' => 'export',
            'method' => 'GET',
            'envelope' => 'with',
            'request' => [
                'with' => $resources,
                'where' => [
                    'profileid' => $this->rel('request')['load']['profileid'],
                ],
            ],
        ]);
        $this->_transmit();
        return $this;
    }

    /**
     * __call()
     *
     * Used to retrieve responses
     *
     * @param string $as_method
     * @param string $k
     * @return mixed
     */
    public function __call($as_method, $k = false)
    {
        $method = ltrim(stristr($as_method, '_'), '_');

        if ('all' == $method) {
            return $this->as;
        } elseif (isset($this->as[$method])) {
            return $k
            && is_array($this->as[$method])
            && isset($this->as[$method][$k[0]])
            ? $this->as[$method][$k[0]]
            : $this->as[$method];
        } elseif ($k) {
            exit('Unable to locate information');
        } else {
            return false;
        }
    }

    /**
     * Attributes()
     *
     * Defines the data of an object that is being created.
     * It is required when creating an object.
     *
     * @param array $attributes
     * @uses options()
     * @uses Api::_transmit()
     * @return void
     */
    public function Attributes($attributes)
    {
        $this->options([
            'request' => [
                'create' => [
                    'attributes' => $attributes,
                ],
            ],
        ]);

        $this->_transmit();
        return $this;
    }

    /**
     * Update()
     *
     * Before updating an object, we must retrieve the object in question.
     * Any updates will be contained in the update method.
     * NOTE: This method supports partial updates.
     *      For partial updates it is necessary to provide an array
     *      representing the data to update.
     *      Once the request is received by the service the new changes
     *      will be applied to the existing data.
     *
     * @example 1 :
     *      $api->Get('leads')
     *          ->Where([
     *              'userid' => $userid
     *          ])
     *
     *          ->Update([
     *              'fname' => [
     *                  'value' => 'Smith',
     *                  'confirmed' => 'yes'
     *              ]
     *          ]);
     *
     * @example 2 :
     *      $api->Get('strategies')
     *          ->Where([
     *              'modid' => $modid
     *          ])
     *          ->Update([
     *              'onsubmit' => $useModuleId,
     *              'triggeringevents' => [
     *                  'eventcombo' => [
     *                      0 => [
     *                          'recurrence' => 25
     *                      ]
     *                  ]
     *              ],
     *              'message' => 'New message'
     *          ]);
     *
     * @see https://... for more examples
     * @param array $update
     * @uses Api::options()
     * @uses Api::_transmit()
     * @return void
     */
    public function Update($update)
    {
        $this->options([
            'method' => 'PUT',
            'envelope' => 'update',
            'request' => [
                'update' => [
                    'attributes' => $update,
                ],
                'objid' => $this->rel('objid'),
            ],
        ]);
        $this->_transmit();
        return $this;
    }

    /**
     * Replace()
     *
     * Will replace the attributes stored on the interaction service
     *
     * @param array $update
     * @uses Api::_options()
     * @uses Api::rel()
     * @uses Api::_transmit()
     * @return object
     */
    public function Replace($update)
    {
        $this->options([
            'method' => 'PUT',
            'envelope' => 'update',
            'request' => [
                'replace' => [
                    'attributes' => $update,
                ],
                'objid' => $this->rel('objid'),
            ],
        ]);
        $this->_transmit();
        return $this;
    }

    /**
     * Renew()
     *
     * Will renew Api and Secret key.
     *
     * @uses Api::_options()
     * @uses Api::rel()
     * @uses Api::_transmit()
     * @return object
     */
    public function Renew()
    {
        $this->options([
            'method' => 'PUT',
            'envelope' => 'renew',
            'request' => [
                'objid' => $this->rel('objid'),
            ],
        ]);
        $this->_transmit();
        return $this;
    }

    /**
     * Download
     *
     * This method is used to create and download the html output of a or multiple modules.
     * The response produced by this method will include an array of modules.
     * Each array should include module id (moduleid) and html output(html).
     * This method can be applied to a strategy or to a module.
     * NOTE: The html is encoded is base 64.
     *
     * @uses Api::_options()
     * @uses Api::_transmit()
     * @return object
     */
    public function Download($resource)
    {
        $this->options([
            'resource' => $resource,
            'method' => 'GET',
            'envelope' => 'download',
            'request' => [
                'download' => true,
            ],
        ]);

        return $this;
    }

    /**
     * uri()
     *
     * Will prepare the request uri by gathering the following:
     *  - version
     *  - accessPoint
     *  - resource
     *
     * @uses Api::_options()
     * @uses Api::rel()
     * @uses Session::get()
     * @return string url
     */
    public function uri()
    {
        $uri = '/';
        $uri .= $this->config['version'];
        $uri .= '/';
        $accessPoint = Session::get('AccessPoint');

        $uri .= $accessPoint ? $accessPoint : '';
        $uri .= $accessPoint ? '/' : '';
        $uri .= $this->rel('resource');
        $uri .= '/';

        $this->options([
            'request' => [
                'uri' => $uri,
            ],
        ]);
        return $uri;
    }

    /**
     * _prepared_request()
     *
     * Will prepare and package the request into a payload.
     *
     * @uses Api::rel()
     * @uses json_encode()
     * @return array
     */
    public function _prepared_request()
    {
        switch ($this->rel('method')) {

            case 'GET':
                return [
                    'query' => [
                        'payload' => json_encode($this->rel('request')),
                    ],
                ];
                break;

            case 'POST':
            case 'PUT':
            case 'DELETE':
                return [
                    'form_params' => [
                        'payload' => json_encode($this->rel('request')),
                    ],
                ];
                break;
        }
    }

    /**
     * _transmit()
     *
     * Will send a request to the Interactive Mod api service.
     *
     * Since authentication occurs automatically,
     * the "$auth" flag must be present to prevent
     * to hook the authentication process, and prevent an infinit loop.
     *
     * @uses Api::_response_handler()
     * @uses Api::rel()
     * @uses Api::uri()
     * @uses Api::_prepared_request()
     * @uses GuzzleHttp\Client()
     * @uses GuzzleHttp\Exception\ClientException
     * @param bool $auth
     * @return void
     */
    public function _transmit($auth = false)
    {

        // Check if the config exists.
        if (!$this->config) {
            // Exit the config is required.
            exit(json_encode([
                'Error' => 'API Configuration details are missing!',
            ], JSON_PRETTY_PRINT));
        }

        /**
         * "$auth" will indicate whether the current transmission
         * is used for authentication or regular request.
         *
         * If an "Authenticate" flag is sent, we must authenticate.
         **/
        if (!$auth && Session::get('Authenticate')) {
            $this->Authenticate();
        }

        /**
         * An other precaution, since authentication is generated automatically,
         * if authentication was not successful due to network or connection issues,
         * It will keep on trying.
         *
         * This block will limit this process to a set number.
         *
         * The max consecutive Authentication can be set in the config method.
         *
         **/

        if (0 > $this->rel('max_auth_count', $this->rel('max_auth_count') - 1)) {
            exit('Authorization Count exceeds the set limit of ' . $this->rel('max_auth_count'));
        }

        /**
         * Instantiate The Guzzle Client.
         */
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->config['protocol'] . '://' . $this->config['endpoint'],
        ]);

        try {
            $response = $client->request(
                $this->rel('method'),
                $this->uri(),
                $this->_prepared_request()
            );

            // Will send the response to a response handler.
            $this->_response_handler($response);

            /**
             * Certain response codes be the api service will trigger a 500 response code the Guzzle client.
             **/
        } catch (GuzzleHttp\Exception\ClientException $e) {

            /**
             * Exceptions can be edited to whatever is needed.
             *
             * In this case, we will issue the same response code,
             * and display the response phrase.
             */
            http_response_code($e->getResponse()->getStatusCode());

            exit('Server responded with a: ' . $e->getResponse()->getStatusCode() . ', ' . $e->getResponse()->getReasonPhrase() . '.');
        }
        return $this;
    }

    /**
     * _response_handler()
     *
     * Will restructure the response and provide simple access to
     * response data.
     *
     * In addition it handles authentication, and access point.
     *
     * The response data can be accessed by using $api->Response();
     *
     * @uses json_decode()
     * @uses GuzzleHttp\Client()::getBody()
     * @uses GuzzleHttp\Client()::getStatusCode()
     * @uses GuzzleHttp\Client()::getReasonPhrase()
     * @uses GuzzleHttp\Client()::getHeaders()
     * @uses Session::set()
     * @uses Session::delete()
     * @uses Api::_close_authentification_request()
     * @uses Api::rel()
     * @param object $response
     * @return void
     */
    public function _response_handler($response)
    {
        $data = json_decode($response->getBody(), true);

        $this->rel('response', [
            'statusCode' => $response->getStatusCode(),
            'responsePhrase' => $response->getReasonPhrase(),
            'responseHeaders' => $response->getHeaders(),
            'responseBody' => $response->getBody(),
            'data' => $data,
        ]);

        if (isset($data['AccessPoint']) && $data['AccessPoint']) {
            Session::set('AccessPoint', $data['AccessPoint']);
            Session::delete('Authenticate');
            $this->_close_authentification_request($data['AccessPoint']);
        }

        if (isset($data['Authenticate']) && $data['Authenticate'] && !$data['AccessPoint']) {
            Session::set('Authenticate', 'AccessPoint');
        }

        if (isset($data['objid'])) {
            $this->rel('objid', $data['objid']);
        }
        $this->rel('response', $data);

        return $this;
    }

    /**
     * Response()
     *
     * Will return the response of a request.
     *
     * @uses Api::rel()
     *
     * @return array $response [
     *          'statusCode',
     *          'responsePhrase',
     *          'responseHeaders',
     *          'responseBody',
     *          'data'
     *      ]
     */
    public function Response($null = false)
    {
        $response = $this->rel('response');
        if (is_null($null)) {
            $this->rel('response', []);
        }
        return $response;
    }
}

class Fs
{

    public static $config;
    public static $resellerid;
    public static $profileid;
    public static $storage;
    public static $errors = [];
    public static $responses = [];

    /**
     * Load the reseller account, in order to manage multiple profiles.
     */
    public static function Reseller($res)
    {
        self::$resellerid = $res;
    }

    /**
     * Load the profile, a new profile id can be set to load an different account.
     */
    public static function Load($prof)
    {
        self::$profileid = $prof;
    }
    
    /**
     * API configuration
     */
    public static function Config($conf = null)
    {
        if (is_null($conf) && isset(self::$config)) {
            return self::$config;
        }
        self::$config = $conf;
    }

    /**
     * If any errors are generated, they can be retrieved using this method.
     */
    public function Error($type = 'all', $error = false)
    {
        if ($error) {
            if (!isset(self::$errors[$type])) {
                self::$errors[$type] = [];
            }
            $pointer = count(self::$errors[$type]);
            self::$errors[$type][$pointer] = $error;
        }
        return self::$errors[$type];
    }

    /**
     * Will set the default storage.
     */
    public static function Storage($path, $dir)
    {
        is_dir($path . '/' . $dir) || mkdir($path . '/' . $dir);
        if (is_writable($path . '/' . $dir)) {
            self::$storage = rtrim($path . '/' . $dir, '/');
        } else {
            self::Error('Store', 'Unable to write in ' . self::$storage . ' directory! Local storage is disabled.');
            self::$storage = false;
        }
    }

    /**
     * Helper method to check if a needle exists in a haystack.
     */
    public static function Includes($needle, $haystack)
    {
        if (!$haystack) {
            return false;
        }
        if (!$needle) {
            return false;
        }
        $return = (strpos(strtolower($haystack), strtolower($needle)) !== false) ? true : false;
        return $return;
    }

    /**
     * Will instantiate the Resource class through static method.
     */
    public static function Resource($package)
    {
        $resource = new Resource($package);
        $resource->Storage(self::$storage);
        return $resource;
    }

    /**
     * This method will be deprecated soon, since introduction of the class Resource.
     */
    public static function Get($key, $id = null)
    {
        if (!self::$data) {
            self::Resources();
        }

        if (self::$data) {
            $package = isset(self::$data[$key]) ? self::$data[$key] : false;
            return !is_null($id) && $package && isset($package[$id]) ? true : is_null($id) && $package ? true : false;
        }
        return false;
    }

    /**
     * This method will be deprecated soon, since introduction of the class Resource.
     */
    public static function Store($name, $type, $data = null)
    {
        if (!self::$storage) {
            self::Error('Store', $name . ' Check storage permission');
            return false;
        }

        if (!is_null($data) && !empty($data)) {
            file_put_contents(self::$storage . '/' . $name . '.' . $type, $data);
        }

    }

    /**
     * Will instantiate the API.
     */
    public static function Api()
    {
        $api = new Client();
        $api->Config(self::$config);
        if (isset(self::$resesslerid)) {
            $api->Reseller(self::$resellerid);
        }
        if (isset(self::$profileid)) {
            $api->Load(self::$profileid);
        }
        return $api;
    }
}

/**
 * Small class will instantiate a resource object.
 */
class Resource
{
    public $package;
    public $get = false;
    public $storage;

    public function __construct($package)
    {
        $this->package = $package;
    }

    /**
     * Will set a storage directory.
     */
    public function Storage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Will store retrieved responses in json format.
     */
    public function Store($data, $name = null)
    {

        $file = $this->storage . '/';
        $file .= $this->package;
        if (!is_null($name)) {
            $file .= '-' . $name . '.json';
        } else {
            $file .= '.json';
        }
        $data = file_put_contents($file, json_encode($data));
    }

    /**
     * Will update a previously stored response.
     */
    public function Update($newdata, $name = null)
    {

        $data = false;
        $replace = false;
        $file = $this->storage . '/';
        $file .= $this->package;

        if (!is_null($name)) {
            $file .= '-' . $name . '.json';
        } else {
            $file .= '.json';
        }

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }

        if ($data) {
            $replace = array_replace($data, $newdata);
        }

        if ($replace) {
            file_put_contents($file, json_encode($replace));
        }
    }

    /**
     * Will temporarily store a retrieved object.
     */
    public function _set($data)
    {
        $this->get = $data;
        return $this;
    }

    /**
     * Will retrieve stored data.
     */
    public function Get($name = null)
    {
        $data = false;
        $replace = false;
        $file = $this->storage . '/';
        $file .= $this->package;

        if (!is_null($name)) {
            $file .= '-' . $name . '.json';
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
                $this->_set($data);
                return $data;
            }
        }

        $file .= '.json';
        if (!file_exists($file)) {
            return false;
        }

        $data = json_decode(file_get_contents($file), true);
        $this->_set($data);

        return $data;
    }

    /**
     * Will find a key within the retrieved data.
     */
    public function find($key)
    {
        if ($this->get && isset($this->get[$key])) {
            return $this->get[$key];
        }
        return false;
    }
}

/**
 * Small class to handle and control sessions.
 */
class Session
{

    /**
     * enable()
     *
     * Must be present to initialize sessions
     *
     * @return void
     */
    public static function enable()
    {
        date_default_timezone_set('America/Los_Angeles');
        global $_SESSION;
        if (!isset($_SESSION) || session_status() == PHP_SESSION_NONE) {
            @session_start();
        }
    }

    /**
     * set()
     *
     * Will set or replace the value of an item store in session
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * get()
     *
     * Will return the value of and item stored in session
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }

    /**
     * delete()
     *
     * Will permanently delete an item from the session
     *
     * @param mixed $key
     * @return void
     */
    public static function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}
