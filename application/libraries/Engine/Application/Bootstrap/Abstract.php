<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Application_Bootstrap_Abstract
{
    /**
     * Contains the parent application object
     *
     * @var Engine_Application
     */
    protected $_application;

    /**
     * Contains the registry object where application resources are stored
     *
     * @var Zend_Registry
     */
    protected $_container;

    /**
     * List of class resources in this class
     *
     * @var array
     */
    protected $_classResources;

    /**
     * List of resources that have been run
     *
     * @var array
     */
    protected $_run = array();

    /**
     * List of resources that have started running (to prevent cyclical dependencies)
     * @var array
     */
    protected $_started =  array();

    /**
     * The name of the module that this class belongs to
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * The path of the module that this class belongs to
     *
     * @var string
     */
    protected $_modulePath;

    /**
     * An array of options
     *
     * @var array
     */
    protected $_options = array();



    // General

    /**
     * Constructor
     *
     * @param Engine_Application $application
     * @throws Engine_Application_Bootstrap_Exception
     */
    public function __construct($application)
    {
        // Get options
        if( $application instanceof Engine_Application )
        {
            $options = $application->getOptions();
            $autoloader = $application->getAutoloader();
        }
        else if( $application instanceof Engine_Application_Bootstrap_Abstract )
        {
            $options = $application->getApplication()->getOptions();
            $autoloader = $application->getApplication()->getAutoloader();
        }
        else
        {

            throw new Engine_Application_Bootstrap_Exception('Application not instance of Engine_Application or Engine_Application_Bootstrap_Abstract');
        }

        $this->_application = $application;
        $this->setOptions($options);
        Engine_Api::_()->setModuleBootstrap($this);
        $autoloader->register($this->getModuleName(), $this->getModulePath());
    }

    public function getApplication()
    {
        return $this->_application;
    }

    public function setOptions(array $options = array())
    {
        $this->_options = $options;
        return $this;
    }

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

    // The Good Stuff

    /**
     * Bootstrap a specified resource or all resources
     *
     * @param string $resource (OPTIONAL) The resource to bootstrap. defaults to null (all)
     * @return Engine_Application_Bootstrap_Abstract
     */
    public function bootstrap($resource = null)
    {
        $this->_bootstrap($resource);
        return $this;
    }

    /**
     * Run the application
     *
     * @throws Engine_Application_Bootstrap_Exception If not overridden by child class
     */
    public function run()
    {
        throw new Engine_Application_Bootstrap_Exception("Run must be implemented in child classes");
    }

    /**
     * Set the resource container
     *
     * @param Zend_Registry $container
     * @return Engine_Application_Bootstrap_Abstract
     */
    public function setContainer(Zend_Registry $container)
    {
        $this->_container = $container;
        return $this;
    }

    /**
     * Get the current resource container
     *
     * @return Zend_Registry
     */
    public function getContainer()
    {
        if( null === $this->_container )
        {
            $this->setContainer(new Zend_Registry());
        }
        return $this->_container;
    }



    // Resource stuff

    /**
     * Bootstrap a resource
     *
     * @param string $resource (OPTIONAL) The resource to bootstrap. defaults to null (all)
     * @throws Engine_Application_Bootstrap_Exception If invalid parameter type given
     */
    protected function _bootstrap($resource = null)
    {
        if( null === $resource )
        {
            $container = $this->getContainer();
            foreach( $this->getClassResources() as $resource => $method )
            {
                $this->_executeResource($resource);
            }
        }
        else if( is_string($resource) )
        {
            $this->_executeResource($resource);
        }
        else if( is_array($resource) )
        {
            foreach ($resource as $r)
            {
                $this->_executeResource($r);
            }
        }
        else
        {
            throw new Engine_Application_Bootstrap_Exception('Invalid argument passed to ' . __METHOD__);
        }
    }

    /**
     * Loads and executes a resource
     *
     * @param string $resource The name of the resource
     * @return void
     * @throws Engine_Application_Bootstrap_Exception If resource doesn't exist or
     *         a cyclical dependency is detected
     */
    protected function _executeResource($resource)
    {
        if( null === $this->_classResources )
        {
            $this->getClassResources();
        }

        if( in_array($resource, $this->_run) )
        {
            return;
        }

        if( !empty($this->_started[$resource]) )
        {
            throw new Engine_Application_Bootstrap_Exception('Cyclical dependency detected.');
        }

        if( isset($this->_classResources[$resource]) )
        {
            $this->_started[$resource] = true;
            $method = $this->_classResources[$resource];
            $return = $this->$method();
            unset($this->_started[$resource]);
            $this->_run[] = $resource;

            if( null !== $return )
            {
                $this->getContainer()->{$resource} = $return;
            }

            return;
        }

        throw new Engine_Application_Bootstrap_Exception(sprintf('Unknown resource %s', $resource));
    }

    /**
     * Gets all class resources that belong to this class. Class resources begin
     * with _init
     *
     * @return array
     */
    public function getClassResources()
    {
        if( null === $this->_classResources )
        {
            if( version_compare(PHP_VERSION, '5.2.6') === -1 )
            {
                $class        = new ReflectionObject($this);
                $classMethods = $class->getMethods();
                $methodNames  = array();

                foreach( $classMethods as $method )
                {
                    $methodNames[] = $method->getName();
                }
            }
            else
            {
                $methodNames = get_class_methods($this);
            }

            $this->_classResources = array();
            foreach( $methodNames as $method )
            {
                if( 5 < strlen($method) && '_init' === substr($method, 0, 5) )
                {
                    $this->_classResources[strtolower(substr($method, 5))] = $method;
                }
            }
        }

        return $this->_classResources;
    }



    // Options

    /**
     * Set the module name
     *
     * @param string $name
     * @return Engine_Application_Bootstrap_Abstract
     */
    public function setModuleName($name)
    {
        $this->_moduleName = $name;
        return $this;
    }

    /**
     * Get the module name
     *
     * @return string
     * @throws Engine_Application_Bootstrap_Exception If invalid class name
     */
    public function getModuleName()
    {
        if( $this->_moduleName === null )
        {
            $class = get_class($this);
            if( false === ($pos = strpos($class, '_')) )
            {
                throw new Engine_Application_Bootstrap_Exception(sprintf('Unable to get module name in class: %s', $class));
            }
            $this->_moduleName = substr($class, 0, $pos);
        }

        return $this->_moduleName;
    }

    /**
     * Set the module path
     *
     * @param string $path
     * @return Engine_Application_Bootstrap_Abstract
     */
    public function setModulePath($path)
    {
        $this->_modulePath = $path;
        return $this;
    }

    /**
     * Get the module path.
     *
     * @return string
     */
    public function getModulePath()
    {
        if( $this->_modulePath === null )
        {
            $r = new ReflectionClass($this);
            $this->_modulePath = dirname($r->getFileName());
        }

        return $this->_modulePath;
    }



    // Utility

    /**
     * Adds the action helper path for this module to the front controller
     *
     * @return Engine_Application_Bootstrap_Abstract
     */
    public function initActionHelperPath()
    {
        // Set up path
        $path = $this->getModulePath() . '/Controller/Action/Helper/';
        $prefix = ucfirst($this->getModuleName()).'_Controller_Action_Helper_';
        Zend_Controller_Action_HelperBroker::addPath($path, $prefix);

        return $this;
    }

    /**
     * Adds the view helper path for this module to the view
     *
     * @return Engine_Application_Bootstrap_Abstract
     */
    public function initViewHelperPath()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        if( is_null($view) )
        {
            return $this;
            //throw new Zend_Application_Exception("Could not get an instance of the view object");
        }

        $path = $this->getModulePath() . '/View/Helper/';
        $prefix = ucfirst($this->getModuleName()).'_View_Helper_';
        $view->addHelperPath($path, $prefix);

        return $this;
    }
}