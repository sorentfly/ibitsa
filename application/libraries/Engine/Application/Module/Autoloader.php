<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Autoloader.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Application
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Application_Module_Autoloader extends Zend_Application_Module_Autoloader
{
  public function __construct($options)
  {
      if ($options instanceof Zend_Config) {
          $options = $options->toArray();
      }
      if (!is_array($options)) {
          // require_once 'Zend/Loader/Exception.php';
          throw new Zend_Loader_Exception('Options must be passed to resource loader constructor');
      }

      $this->setOptions($options);

      $namespace = $this->getNamespace();
      if ((null === $namespace)
          || (null === $this->getBasePath())
      ) {
          // require_once 'Zend/Loader/Exception.php';
          throw new Zend_Loader_Exception('Resource loader requires both a namespace and a base path for initialization');
      }

      if (!empty($namespace)) {
          $namespace .= '_';
      }
      Engine_Loader::getInstance()->register(trim($namespace, '_'), $this->getBasePath());
      //Zend_Loader_Autoloader::getInstance()->unshiftAutoloader($this, $namespace);
  }

  public function initDefaultResourceTypes()
  {
    /*
    $basePath = $this->getBasePath();
    $this->addResourceTypes(array(
        'api' => array(
            'namespace' => 'Api',
            'path'      => 'apis',
        ),
        'actionhelper' => array(
          'namespace'   => 'Controller_Action_Helper',
          'path'        => 'controllers/Action/Helper',
        ),
        'action' => array(
          'namespace'   => 'Controller_Action',
          'path'        => 'controllers/Action'
        ),
        'dbtable' => array(
            'namespace' => 'Model_DbTable',
            'path'      => 'models/DbTable',
        ),
        'form'    => array(
            'namespace' => 'Form',
            'path'      => 'forms',
        ),
        'model'   => array(
            'namespace' => 'Model',
            'path'      => 'models',
        ),
        'plugin'  => array(
            'namespace' => 'Plugin',
            'path'      => 'plugins',
        ),
        'resource' => array(
            'namespace' => 'Resource',
            'path'      => 'resources',
        ),
        'service' => array(
            'namespace' => 'Service',
            'path'      => 'services',
        ),
        'viewhelper' => array(
            'namespace' => 'View_Helper',
            'path'      => 'views/helpers',
        ),
        'viewfilter' => array(
            'namespace' => 'View_Filter',
            'path'      => 'views/filters',
        ),
    ));
    $this->setDefaultResourceType('model');
     */
  }

  // Hacks

  public function autoload($class)
  {
    $segments  = explode('_', $class);
    $namespace = $this->getNamespace();
    $classNamespace = array_shift($segments);

    if( $classNamespace !== $namespace )
    {
      return false;
    }

    return include $this->getBasePath() . '/' . join('/', $segments) . '.php';
  }

  public function load($resource, $type = null)
  {
    $resource = ucfirst($resource);

    if( $type == 'dbtable' )
    {
      $type = 'Model_DbTable';
    }
    
    if( null !== $type )
    {
      $resource = ucfirst($type) . '_' . $resource;
    }

    $class = $this->getNamespace() . '_' . $resource;
    if( !isset($this->_resources[$class]) )
    {
      $this->_resources[$class] = new $class;
    }

    return $this->_resources[$class];
  }
}