<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Application.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Application
{
    // Static Properties

    static protected $_instance;

    static protected $_defaultOptions = array(
        'charset' => 'UTF-8',
        'environment' => 'production',
        'timezone' => 'UTC',
    );



    // Properties

    /**
     * Contains the loader/autoloader instance
     *
     * @var Engine_Loader
     */
    protected $_autoloader;

    /**
     * Contains the primary bootstrap object
     *
     * @var Engine_Application_Bootstrap_Abstract
     */
    protected $_bootstrap;

    /**
     * Contains the charset
     *
     * @var string
     */
    protected $_charset;

    /**
     * The environment. Used to flag certain debug features on or off.
     *
     * @var string
     */
    protected $_environment;

    /**
     * Misc options
     *
     * @var array
     */
    protected $_options = array();



    // Static

    static public function _()
    {
        if( null === self::$_instance ) {
            throw new Engine_Application_Exception("No instance configured");
        }
        return self::$_instance;
    }

    static public function getInstance()
    {
        if( null === self::$_instance ) {
            throw new Engine_Application_Exception("No instance configured");
        }
        return self::$_instance;
    }

    static public function setInstance(Engine_Application $application)
    {
        self::$_instance = $application;
    }

    static public function unsetInstance()
    {
        self::$_instance = null;
    }



    // General

    /**
     * Constructor
     *
     * @param array|Zend_Config $options The options to set
     * @internal param string $environment The environment (development/production)
     */
    public function __construct($options)
    {
        if( is_object($options) && method_exists($options, 'toArray') ) {
            $options = $options->toArray();
        }

        if( is_array($options) ) {
            $options = array_merge(self::$_defaultOptions, $options);

            // Must do autoloaderNamespaces first
            if( isset($options['autoloaderNamespaces']) ) {
                $this->setAutoloaderNamespaces($options['autoloaderNamespaces']);
                unset($options['autoloaderNamespaces']);
            }
            if( isset($options['environment']) ) {
                $this->_environment = $options['environment'];
                unset($options['environment']);
            } else {
                $this->_environment = 'production';
            }
            $this->setOptions($options);
        }

        if( null === $this->_environment ) {
            $this->_environment = 'production';
        }

        if( !$this->getOption('noStripGlobals', false) ) {
            self::_stripGlobals();
        }
    }

    /**
     * Bootstrap the application
     *
     * @param null $name
     * @return Engine_Application
     */
    public function bootstrap($name = null)
    {
        $this->getBootstrap()->bootstrap($name);
        return $this;
    }

    /**
     * Run the application
     *
     * @return Engine_Application
     */
    public function run()
    {
        $this->getBootstrap()->run();
        return $this;
    }

    /**
     * Set options
     *
     * @param array $options The options to set
     */
    public function setOptions(array $options)
    {
        foreach( $options as $key => $value ) {
            $method = 'set'.ucfirst($key);
            if( method_exists($this, $method) ) {
                $this->$method($value);
            } else {
                $this->setOption($key, $value);
            }
        }
    }

    /**
     * Get the loader object
     *
     * @return Engine_Loader
     */
    public function getAutoloader()
    {
        if( null === $this->_autoloader ) {
            $this->_autoloader = Engine_Loader::getInstance();
        }

        return $this->_autoloader;
    }

    /**
     * Get the primary bootstrap
     *
     * @return Engine_Application_Bootstrap_Abstract
     * @throws Engine_Application_Exception If the bootstrap has not been configured
     */
    public function getBootstrap()
    {
        if( null === $this->_bootstrap ) {
            throw new Engine_Application_Exception('No bootstrap registered');
        }

        return $this->_bootstrap;
    }



    // Options

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    public function getOption($key, $default = null)
    {
        if( !isset($this->_options[$key]) )
        {
            return $default;
        }

        return $this->_options[$key];
    }

    /**
     * Sets loader prefixes in the autoloader
     *
     * @param array $namespaces
     * @return Engine_Application
     */
    public function setAutoloaderNamespaces(array $namespaces)
    {
        foreach( $namespaces as $prefix => $path ) {
            if( is_numeric($prefix) ) {
                $prefix = $path;
                $path = null;
            }
            $this->getAutoloader()->register($prefix, $path);
        }
        return $this;
    }

    /**
     * Set bootstrap options
     *
     * @param $spec
     * @return Engine_Application
     * @throws Engine_Application_Exception
     * @internal param array $options
     */
    public function setBootstrap($spec)
    {
        if( $spec instanceof Engine_Application_Bootstrap_Abstract ) {
            $this->_bootstrap = $spec;
        } else if( is_array($spec) ) {
            $class = @$spec['class'];
            $path = @$spec['path'];

            if( !file_exists($path) ) {
                throw new Engine_Application_Exception('Bootstrap not found');
            }

            require_once $path;

            if( !class_exists($class, false) ) {
                throw new Engine_Application_Exception('Bootstrap not found');
            }

            $this->_bootstrap = new $class($this);

        } else if( is_string($spec) ) {
            $class = $spec;

            if( !class_exists($class, true) ) {
                throw new Engine_Application_Exception('Bootstrap not found');
            }

            $this->_bootstrap = new $class($this);

        } else {
            throw new Engine_Application_Exception('Invalid spec');
        }

        return $this;
    }

    public function getCharset()
    {
        if( null === $this->_charset ) {
            $this->setCharset('UTF-8');
        }

        return $this->_charset;
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;

        if( function_exists('mb_internal_encoding') ) {
            mb_internal_encoding($charset);
        }

        if( function_exists('iconv_set_encoding') && PHP_VERSION_ID < 50600 ) {
            // Not sure if we want to do all of these
            iconv_set_encoding("input_encoding", $charset);
            iconv_set_encoding("output_encoding", $charset);
            iconv_set_encoding("internal_encoding", $charset);
        }else{
            ini_set('default_charset', 'UTF-8');
        }

        return $this;
    }

    public function setErrorReporting($errorReporting)
    {
        error_reporting($errorReporting);
    }

    /**
     * Add php include paths
     *
     * @param array $paths
     * @return Engine_Application
     */
    public function setIncludePaths(array $paths)
    {
        $path = implode(PATH_SEPARATOR, $paths);
        set_include_path($path . PATH_SEPARATOR . get_include_path());
        return $this;
    }

    /**
     * Set php settings
     *
     * @param array $settings An array of setting to value
     * @param string $prefix (OPTIONAL) Prefix to use with setting name
     * @return Engine_Application
     */
    public function setPhpSettings(array $settings, $prefix = '')
    {
        $settings = (array) $settings;
        foreach( $settings as $key => $value ) {
            $key = empty($prefix) ? $key : $prefix . $key;
            if( is_scalar($key) ) {
                ini_set($key, $value);
            } else if( is_array($value) ) {
                $this->setPhpSettings($settings, $key . '.');
            }
        }
        return $this;
    }

    public function setTimezone($timezone)
    {
        date_default_timezone_set($timezone);
        return $this;
    }

    /**
     * Strip all input globals of slashes, if magic quotes gps is on
     *
     * @staticvar boolean $stripped Whether or not we've run yet
     */
    protected static function _stripGlobals()
    {
        static $stripped;
        if( !$stripped && get_magic_quotes_gpc() ) {
            $_GET     = self::_stripSlashes($_GET);
            $_POST    = self::_stripSlashes($_POST);
            $_COOKIE  = self::_stripSlashes($_COOKIE);
            $_REQUEST = self::_stripSlashes($_REQUEST);
            $stripped = true;
        }
    }

    /**
     * Deep slasher
     *
     * @param mixed $value
     * @return mixed
     */
    protected static function _stripSlashes($value)
    {
        return ( is_array($value) ?
            array_map(array(__CLASS__, '_stripSlashes'), $value) :
            stripslashes($value) );
    }
}