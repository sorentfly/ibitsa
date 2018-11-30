<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Bootstrap.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
{
  protected $_moduleName;

  protected $_modulePath;

  public function __construct($application, $options = null)
  {
    // Set application
    $this->setApplication($application);

    // Set custom module options (for dynamic bootstrapping)
    if( is_array($options) )
    {
      $this->setOptions($options);
    }
    
    // Use same plugin loader as parent bootstrap
    if ($application instanceof Zend_Application_Bootstrap_ResourceBootstrapper) {
        $this->setPluginLoader($application->getPluginLoader());
    }
    
    // Get the options from config
    $key = strtolower($this->getModuleName());
    if ($application->hasOption($key)) {
        // Don't run via setOptions() to prevent duplicate initialization
        $this->setOptions($application->getOption($key));
    }

    // Init resource loader
    if ($application->hasOption('resourceloader')) {
        $this->setOptions(array(
            'resourceloader' => $application->getOption('resourceloader')
        ));
    }
    //$this->initResourceLoader();
    Engine_Loader::getInstance()->register($this->getModuleName(), $this->getModulePath());
    
    $isDefaultModule = ( get_class($this->getApplication()) === 'Zend_Application' );
    
    // ZF-6545: ensure front controller resource is loaded
    if (!$isDefaultModule && !$this->hasPluginResource('FrontController')) {
        $this->registerPluginResource('FrontController');
    }

    // ZF-6545: prevent recursive registration of modules
    if (!$isDefaultModule && $this->hasPluginResource('Modules')) {
        $this->unregisterPluginResource('Modules');
    }
    
    // Register with Engine_Api
    Engine_Api::_()->setModuleBootstrap($this);

    // Run internal hook
    $this->preBootstrap();
  }

  /**
   * This is used to allow for configurable boot orders
   */
  protected function _bootstrap($resource = null)
  {
    // We can use boot order to resolve
    if( is_null($resource) && $this->hasOption('bootorder') )
    {
      $bootorder = (array) $this->getOption('bootorder');
      ksort($bootorder);
      $bootorder = array_unique(array_merge(
          array_values($bootorder),
          $this->getClassResourceNames(),
          $this->getPluginResourceNames()
      ));
      foreach( $bootorder as $resource )
      {
        $this->_executeResource($resource);
      }
      $this->postBootstrap();
    }

    // No boot order, just boot them all, or resource was specified
    else
    {
      parent::_bootstrap($resource);
      if( is_null($resource) )
      {
        $this->postBootstrap();
      }
    }
  }



  // Internal hooks

  /**
   * Pre-Bootstrap hook
   */
  public function preBootstrap()
  {

  }

  /**
   * Post-Bootstrap hook
   */
  public function postBootstrap()
  {

  }



  // Options

  public function setModuleName($name)
  {
    $this->_moduleName = $name;
    return $this;
  }

  public function getModuleName()
  {
    if( $this->_moduleName === null )
    {
      list($module) = explode('_', get_class($this), 2);
      if( $module === 'Engine' || $module === 'Zend' || $module === null )
      {
        throw new Zend_Application_Exception('Unable to get module name');
      }
      $this->_moduleName = $module;
    }
    
    return $this->_moduleName;
  }

  public function setModulePath($path)
  {
    $this->_modulePath = $path;
    return $this;
  }

  public function getModulePath()
  {
    if( $this->_modulePath === null )
    {
      list($module) = explode('_', get_class($this), 2);
      if( $module === 'Engine' || $module === 'Zend' || $module === null )
      {
        throw new Zend_Application_Exception('Unable to get module path');
      }
      $r    = new ReflectionClass($this);
      $this->_modulePath = dirname($r->getFileName());
    }

    return $this->_modulePath;
  }



  // Loaders
  
  public function getResourceLoader()
  {
      throw new Engine_Exception("Disabled");
      
      if (null === $this->_resourceLoader) {
          $this->setResourceLoader(new Engine_Application_Module_Autoloader(array(
              'namespace' => $this->getModuleName(),
              'basePath'  => $this->getModulePath()
          )));
      }
      return $this->_resourceLoader;
  }

  public function getPluginLoader()
  {
    if ($this->_pluginLoader === null) {
      $this->_pluginLoader = new Zend_Loader_PluginLoader();
    }

    return $this->_pluginLoader;
  }



  // Utility

  /**
   * Adds the action helper path for this module to the front controller
   */
  public function initActionHelperPath()
  {
    // Set up path
    $path = $this->getModulePath() .
      //'/libraries/Controller/Action/Helper/';
      '/Controller/Action/Helper/';
    $prefix = ucfirst($this->getModuleName()).'_Controller_Action_Helper_';
    Zend_Controller_Action_HelperBroker::addPath($path, $prefix);
  }
  
  /**
   * Adds a path to the plugin loader
   */
  public function initPluginResourcePath()
  {
    $this->getPluginLoader()->addPrefixPath(
      ucfirst($this->getModuleName()).'_Resource',
      $this->getModulePath().'/Resource'
    );
  }

  /**
   * Adds the view helper path for this module to the view
   */
  public function initViewHelperPath()
  {
    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
    $view = $viewRenderer->view;
    if( is_null($view) )
    {
      throw new Zend_Application_Exception("Could not get an instance of the view object");
    }
    
    //$path = $this->getModulePath() . '/View/Helper/';
    $path = $this->getModulePath() . '/View/Helper/';
    $prefix = ucfirst($this->getModuleName()).'_View_Helper_';
    $view->addHelperPath($path, $prefix);
  }

  /**
   * Will return a resource if loaded, or bootstrap and return if not loaded
   * @todo Verify this doesn't cause problems or race conditions
   *
   * @param string $resource The name of the resource
   * @return mixed
   */
  public function loadResource($resource)
  {
    if( !$this->hasResource($resource) )
    {
      $this->bootstrap($resource);
    }

    $object = $this->getResource($resource);

    if( is_null($object) )
    {
      throw new Zend_Application_Exception(sprintf('Could not load resource: %s', $resource));
    }

    return $object;
  }
}
