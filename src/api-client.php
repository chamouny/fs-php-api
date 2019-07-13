<?php
namespace FormSynergy;

/**
 * FormSynergy Api PHP Client.
 *
 * This client api can be used to manage and administer FormSynergy api accounts.
 *
 * For fast and easy development this Api, simplifies the registration of domains, along with strategies and modules.
 * Take a quick look at the fs-demo package, this basic example can be improved to best fit your needs.
 * https://github.com/form-synergy/fs-demo
 *
 * @author     Joseph G. Chamoun <formsynergy@gmail.com>
 * @copyright  2019 FormSynergy.com
 * @licence    https://github.com/form-synergy/php-api/blob/dev-master/LICENSE MIT
 */

/**
 * Fs class
 * 
 * @version 1.6.0.0
 * 
 */
class Fs
{
    /**
	 * FormSynergy version constant.
	 */
	const FORMSYNERGY_VERSION = '1.6.0.0';

    /**
     * self::$config 
     * 
     * Contains the necessary keys to gain access to the API service.
     * 
     * 
     * @visibility public static
     * @var array $config
     */
    public static $config;

    /**
     * self::$resellerid
     * 
     * Enables management over multiple accounts.
     * 
     * 
     * @visibility public static
     * @var string $resellerid
     */
    public static $resellerid;

    /**
     * self::$profileid
     * 
     * Unique identifier for an account or profile.
     * 
     * 
     * @visibility public static
     * @var string $profileid
     */
    public static $profileid;

    /**
     * self::$storage
     * 
     * Directory to store details regarding resources.
     * 
     * 
     * @visibility public static
     * @var sting $storage
     */
    public static $storage;

    /**
     * Resellerid()
     * Load the reseller account, in order to manage multiple profiles.
     * 
     * 
     * @visibility public static
     * @uses self::$resellerid
     * @return void
     */
    public static function Reseller($resellerid = null)
    {
        if( !self::$resellerid && is_null($resellerid)) {
            throw new FsException('In order to return the reseller id, it must be defined first.');
        }
        if(!is_null($resellerid)) {
            self::$resellerid = $resellerid;
        }
        return self::$resellerid;
    }

    /**
     * Load()
     * 
     * Load the profile, a new profile id can be set to load an different account.
     * 
     * 
     * @visibility public static
     * @uses self::$profileid
     * @return void
     */
    public static function Load($profileid = null)
    {
        if( !self::$profileid && is_null($profileid)) {
            throw new FsException('In order to return the profile id, it must be defined first.');
        }
        if(!is_null($profileid)) {
            self::$profileid = $profileid;
        }
        return self::$profileid;
    }
    
    /**
     * Config()
     * 
     * API configuration
     * 
     * 
     * @visibility public static
     * @uses self::$config
     * @return void
     */
    public static function Config($config = null)
    {
        if( !self::$config && is_null($config)) {
            throw new FsException('In order to return configuration variables, they myst be defined first.');
        }
        if(!is_null($config)) {
            self::$config = $config;
        }
        return self::$config;
    }

    /**
     * Storage()
     * 
     * Will set the default storage.
     * 
     * 
     * @visibility public static
     * @uses is_dir()
     * @uses mkdir()
     * @uses file_exists()
     * @uses rtrim()
     * @uses FsException()
     * @uses self::$storage
     * @param string $path
     * @param string $dir
     * @return string
     */
    public static function Storage($path, $dir)
    {
        is_dir($path . '/' . $dir) || mkdir($path . '/' . $dir);
        if (is_writable($path . '/' . $dir)) {
            self::$storage = rtrim($path . '/' . $dir, '/');
        }
        else if(file_exists($path . '/' . $dir)) {
            self::$storage = rtrim($path . '/' . $dir, '/');
        } else {
            self::$storage = false;
        }
    }

    /**
     * Includes()
     * 
     * Helper method to check if a needle exists in a haystack.
     * 
     * 
     * @visibility public static
     * @uses strpos()
     * @uses strtolower()
     * @return bool
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
     * Resources()
     * 
     * Will instantiate the Resource class through static method
     * 
     * 
     * @visibility public static
     * @see class Resources
     * @param string $package
     * @return object $resource
     */
    public static function Resource($package)
    {
        $resource = new File_Storage($package, self::$storage);
        return $resource;
    }
 
    /**
     * Api()
     * 
     * Will instantiate the API.
     * 
     * 
     * @visibility public static
     * @see class Client
     * @uses isset()
     * @uses Client::Config()
     * @uses Client::Reseller()
     * @uses Client::Load()
     * @return object
     */
    public static function Api()
    {
        try {
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
        catch(FsException $e) {
            echo $e->getMessage();
        }
    }

    /**
	 * Returns the FormSynergy version string.
	 * The FormSynergy version string always has the same format "X.Y.Z"
	 * where X is the major version number and Y is the minor version number
     * and z for patch.
	 *
     * @visibility public static
	 * @return string
	 */
	public static function Version()
	{
		return self::FORMSYNERGY_VERSION;
	}
}



/**
 * Form Synergy Client Class
 */
class Client
{

    /**
     * self::$config
     * 
     * Configuration of the api client
     * 
     * 
     * @visibility private
     * @var array
     */
    private $config = [];

    /**
     * self::$internal
     * 
     * Internal array to manage variables
     * 
     * 
     * @visibility private
     * @var array
     */
    private $internal = [];

    /**
     * self::as
     * 
     * Will temporary store response
     * 
     * 
     * @visibility private
     * @var array
     */
    private $as = [];

    /**
     * Config()
     *
     * Configuration function to ser config values
     *
     * @visibility public
     * @param array $config
     * @return void
     */
    public function Config($config)
    {
        $this->config = $config;
        $this->_rel('max_auth_count', 0);
        $this->_rel('max_auth_allowed', $config['max_auth_count']);
    }

    /**
     * _rel()
     *
     * Setter for rel
     *
     * @visibility public
     * @param sting $name
     * @param mixed $value
     * @return self
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
     * @visibility public
     * @uses Api::_rel()
     * @param string $name
     * @param mixed $value
     * @return mixed
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
     * 
     * 
     * @visibility public
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
     * @visibility public
     * @uses Api::rel()
     * @param array $options
     * @return self
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
     * @visibility public
     * @uses Api::options()
     * @param string $resellerid
     * @return self
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
     * @visibility public
     * @uses Api::options()
     * @param string $profileid
     * @return self
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
     * @visibility private
     * @return array
     */
    private function Authenticate($authenticate = null)
    {

        // Get stored access point from session.
        $accessPoint = Session::Get('AccessPoint');

        // Access point exists, try again on the next request.
        if ($accessPoint) {
            return;
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
        
        if ( $this->rel('max_auth_allowed') < $this->rel('max_auth_count', $this->rel('max_auth_count') + 1 ) ) {
            throw new FsException( 'Authorization Count exceeds the set limit of ' . $this->rel('max_auth_count') );
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
     * @visibility private
     * @param string $accessPoint
     * @return self
     */
    private function _close_authentification_request($accessPoint)
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
     * @visibility public
     * @see https://... for more examples
     * @uses Api::Where()
     * @uses Api::options()
     * @param string $resource
     * @return self
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
     * @visibility public
     * @see https://... for more examples
     * @uses Api::Attributes()
     * @uses Api::options()
     * @param string $resource
     * @return self
     */
    public function Create($resource)
    {
        $options = [
            'resource' => $resource,
            'method' => 'POST',
            'envelope' => 'create',
        ];
        if('access' == $resource) {
            $options['request']['create']['objid'] = $this->rel('objid');
        }
        $this->options($options);
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
     * @visibility public
     * @see https://... for more examples
     * @uses Api::Get()
     * @uses Api::Where()
     * @uses Api::options()
     * @uses Api::_transmit()
     * @return self
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
     * @visibility public
     * @see https://... for more examples
     * @uses Api::With()
     * @uses Api::Where()
     * @uses Api::options()
     * @param string $find
     * @return self
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
     * Where()
     *
     * Defines the terms of a request.
     * It is required when getting or finding object.
     *
     * @visibility public
     * @param array $where
     * @uses Api::options()
     * @uses Api::_transmit()
     * @return self
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
     * @visibility public
     * @return self
     */
    public function Verify()
    {
        $this->options([
            'method' => 'PUT',
            'envelope' => 'verify',
            'request' => [
                'verify' => true,
                'objid' => $this->rel('objid'),
            ],
        ]);
        $this->_transmit();
        return $this;
    }

    /**
     * Scan()
     *
     * Once a domain has been verified, scan the domain in question to retrieve all etags.
     *
     * @visibility public
     * @return self
     */
    public function Scan()
    {
        $this->options([
            'method' => 'PUT',
            'envelope' => 'scan',
            'request' => [
                'scan' => true,
            ],
            'objid' => $this->rel('objid'),
        ]);
        $this->_transmit();
        return $this;
    }
 
    /**
     * Then()
     *
     * Will return self in closure.
     * 
     * 
     * @visibility public
     * @param callable $fn
     * @return self
     */
    public function Then($fn)
    {
        return $fn($this);
    }

    /**
     * __As()
     *
     * It store the response as a named keyword, 
     * when using the Find method, the response will 
     * consist of multiple results, use $index, to 
     * store one result.
     * 
     * Example:
     *  ->As('name', 0);
     *
     * @visibility public
     * @use __call()
     * @use Api::_rel()
     * @param string $name
     * @return self
     */
    public function As($name, $index = null) 
    {
        $response = $this->Response();
        $this->rel('_as', $name);
        $this->as[$name] = !is_null($index) ? $response['data'][$index] : $response['data'];

        return $this;
    }


    /**
     * Export()
     * 
     * Will return all selected resources.
     * 
     * 
     * @visibility public
     * @param array $resource
     * @return self
     */
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
     * @visibility public
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
            throw new FsException('Unable to locate information');
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
     * @visibility public
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
     * @visibility public
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
     * @visibility public
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
     * @visibility public
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
     * @visibility public
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
     * @visibility private
     * @uses Api::_options()
     * @uses Api::rel()
     * @uses Session::Get()
     * @return string url
     */
    private function uri()
    {
        $uri = '/';
        $uri .= $this->config['version'];
        $uri .= '/';
        $accessPoint = Session::Get('AccessPoint');

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
     * @visibility private
     * @uses Api::rel()
     * @uses json_encode()
     * @return array
     */
    private function _prepared_request()
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
     * @visibility private
     * @uses Api::_response_handler()
     * @uses Api::rel()
     * @uses Api::uri()
     * @uses Api::_prepared_request()
     * @uses GuzzleHttp\Client()
     * @uses GuzzleHttp\Exception\ClientException
     * @param bool $auth
     * @return void
     */
    private function _transmit($auth = false)
    {

        // Check if the config exists.
        if (!$this->config) {
            // Exit the config is required.
            throw new FsException('Configuration settings are missing');
        }

        /**
         * "$auth" will indicate whether the current transmission
         * is used for authentication or regular request.
         *
         * If an "Authenticate" flag is sent, we must authenticate.
         **/
        if (!$auth && Session::Get('Authenticate')) {
            $this->Authenticate();
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
            throw new FsException('Server responded with a: ' . $e->getResponse()->getStatusCode() . ', ' . $e->getResponse()->getReasonPhrase() . '.');
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
     * @visibility private
     * @uses json_decode()
     * @uses GuzzleHttp\Client()::getBody()
     * @uses GuzzleHttp\Client()::getStatusCode()
     * @uses GuzzleHttp\Client()::getReasonPhrase()
     * @uses GuzzleHttp\Client()::getHeaders()
     * @uses Session::Set()
     * @uses Session::Delete()
     * @uses Api::_close_authentification_request()
     * @uses Api::rel()
     * @param object $response
     * @return void
     */
    private function _response_handler($response)
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
            Session::Set('AccessPoint', $data['AccessPoint']);
            Session::Delete('Authenticate');
            $this->_close_authentification_request($data['AccessPoint']);
        }

        if (isset($data['Authenticate']) && $data['Authenticate'] && !$data['AccessPoint']) {
            Session::Set('Authenticate', 'AccessPoint');
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
     * @visibility public
     * @uses Api::rel()
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

/**
 * Class FsException extends Exception
 */
class FsException extends \Exception {}

/**
 * Small class to handle and control sessions.
 */
class Session
{

    /**
     * Enable()
     *
     * Must be present to initialize sessions
     *
     * @visibility public static
     * @return void
     */
    public static function Enable()
    {
        date_default_timezone_set('America/Los_Angeles');
        global $_SESSION;
        if (!isset($_SESSION) || session_status() == PHP_SESSION_NONE) {
            @session_start();
        }
    }

    /**
     * Set()
     *
     * Will set or replace the value of an item store in session
     *
     * @visibility public static
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function Set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get()
     *
     * Will return the value of and item stored in session
     *
     * @visibility public static
     * @param string $key
     * @return mixed
     */
    public static function Get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }

    /**
     * Delete()
     *
     * Will permanently delete an item from the session
     *
     * @visibility public static
     * @param mixed $key
     * @return void
     */
    public static function Delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}
