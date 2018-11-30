<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Content.php 10000 2013-03-26 21:31:32Z jung $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Content
{
  // Constants
  
  const DECORATOR = 'DECORATOR';
  const ELEMENT = 'ELEMENT';


  
  // Properties

  static protected $_instance;

  protected $_cache;
  
  protected $_loaders = array();

  protected $_translator;
  
  protected $_view;



  // Static

  static public function getInstance()
  {
    if( null === self::$_instance ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  static public function setInstance(Engine_Content $content = null)
  {
    self::$_instance = null;
  }



  // Loading
  
  public function getPluginLoader($type = null)
  {
    $type = strtoupper($type);
    if( !isset($this->_loaders[$type]) ) {
      switch( $type ) {
        case self::DECORATOR:
          $prefixSegment = 'Content_Decorator';
          $pathSegment   = 'Content/Decorator';
          break;
        case self::ELEMENT:
          $prefixSegment = 'Content_Element';
          $pathSegment   = 'Content/Element';
          break;
        default:
          throw new Engine_Content_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
          break;
      }
      
      $this->_loaders[$type] = new Zend_Loader_PluginLoader(
          array('Engine_' . $prefixSegment . '_' => 'Engine/' . $pathSegment . '/')
      );
    }

    return $this->_loaders[$type];
  }

  public function loadWidget($widget)
  {
    // Sanitize
    $widget = preg_replace('/[^\d\w.-]/', '', $widget);

    // Split
    $segments = explode('.', strtolower($widget));
    if( count($segments) == 2 ) {
      $name = array_pop($segments);
      $module = array_pop($segments);
    } else if( count($segments) == 1 ) {
      $name = array_pop($segments);
      $module = null;
    } else {
      throw new Engine_Content_Exception(sprintf('Widget name must have exactly one or two sections, given "%s"', $widget));
    }

    // Check to see if module is installed
    if( null !== $module && !Engine_Api::_()->hasModuleBootstrap($module) ) {
      //return false;
      throw new Engine_Content_Exception(sprintf('Widget\'s module is not enabled or not installed: "%s"', $module));
    }

    // Make class
    $class = 'Widget_' . $this->inflect($name) . 'Controller';
    if( null !== $module ) {
      $class = $this->inflect($module) . '_' . $class;
    }

    // Make paths
    if( null !== $module ) {
      $path = 'application/modules/' . $this->inflect($module) . '/widgets/' . $name . '/Controller.php';
    } else {
      $path = 'application/widgets/' . $name . '/Controller.php';
    }
    
    // Load the class
    if( !class_exists($class, false) ) {
      if( file_exists(APPLICATION_PATH . '/' . $path) ) {
        include APPLICATION_PATH . '/' . $path;
      }
      if( !class_exists($class, false) ) {
        throw new Engine_Content_Exception(sprintf('Unable to load widget class "%s" in path "%s"', $class, $path));
      }
    }

    $instance = new $class();

    // Set up paths for widget
    $instance->setPath(dirname($path));
    $instance->setScriptPath(dirname($path));
    //$instance->setScriptPath(dirname($path) . '/scripts');

    return $instance;
  }

  public function inflect($string)
  {
    return str_replace(' ', '', ucwords(str_replace(array('.', '-'), ' ', $string)));
  }



  // Storage

  public function setStorage(Engine_Content_Storage_Interface $storage)
  {
    $this->_storage = $storage;
    return $this;
  }

  public function getStorage()
  {
    if( null === $this->_storage ) {
      throw new Engine_Content_Exception('No storage registered to content system');
    }

    return $this->_storage;
  }



  // Rendering

  public function getView()
  {
    if( null === $this->_view ) {
      $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
      if( null !== $viewRenderer && $viewRenderer->view instanceof Zend_View_Interface ) {
        $this->_view = $viewRenderer->view;
      }
      if( null === $this->_view ) {
        throw new Engine_Content_Exception('Content not configure with view object');
      }
    }

    return $this->_view;
  }

  public function setView(Zend_View_Interface $view)
  {
    $this->_view = $view;
    return $this;
  }

  public function getMetaData($name)
  {
    $storage = $this->getStorage();
    $metaData = $storage->loadMetaData($this, $name);
    if( null === $metaData ) {
      return array();
    }
    return $metaData;
  }

  protected $_mainContentForward = null;
  public function render($name, $forward = null)
  {
    $this->_mainContentForward = $forward;
    $storage = $this->getStorage();
    $structure = $storage->loadContent($this, $name);
    if( null === $structure ) {
      return '';
    }
    $rendered = $structure->render();
    $this->_mainContentForward = null;
    return $rendered;
  }
  
  public function getContentForward()
  {
      return $this->_mainContentForward;
  }



  // Caching

  public function setCache(Zend_Cache_Core $cache)
  {
    $this->_cache = $cache;
    return $this;
  }

  public function getCache()
  {
    return $this->_cache;
  }



  // Translation

  public function setTranslator(Zend_Translate $translator)
  {
    $this->_translator = $translator;
    return $this;
  }

  public function getTranslator()
  {
    return $this->_translator;
  }
}