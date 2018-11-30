<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Sanity.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity implements Engine_Sanity_Test_Interface
{
  const ERROR_NONE = 0;
  const ERROR_NOTICE = 1;
  const ERROR_WARNING = 2;
  const ERROR_ERROR = 4;

  static protected $_defaultTranslator;

  static protected $_defaultDbAdapter;

  protected $_name;

  protected $_tests;

  protected $_basePath;



  // General
  
  public function __construct($options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value)
    {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }

  public function getType()
  {
    return 'collection';
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }

  public function getName()
  {
    if( null === $this->_name ) {
      $translate = Engine_Sanity::getDefaultTranslator();
      if( $translate ) {
        $this->_name = $translate->_(strtolower(get_class($this)) . '_name');
      } else {
        $this->_name = $this->getType();
      }
    }

    return $this->_name;
  }

  public function getBasePath()
  {
    if( defined('APPLICATION_PATH') ) {
      $this->_basePath = APPLICATION_PATH;
    } else {
      $this->_basePath = rtrim(getcwd());
    }
    return $this->_basePath;
  }

  public function setBasePath($basePath)
  {
    $this->_basePath = $basePath;
    return $this;
  }

  

  // Run stuff

  public function run()
  {
    foreach( (array) $this->getTests() as $test )
    {
      $test->execute();
    }

    return $this;
  }

  public function execute()
  {
    $this->run();
    return $this;
  }


  
  // Tests

  public function addTest($spec, $options = array())
  {
    if( $spec instanceof Engine_Sanity_Test_Interface ) {
      $test = $spec;
    } else if( is_string($spec) ) {
      $class = 'Engine_Sanity_Test_' . ucfirst($spec);
      Engine_Loader::loadClass($class);
      $test = new $class($options);
    } else if( is_array($spec) ) {
      if( !empty($spec['type']) ) {
        $class = 'Engine_Sanity_Test_' . ucfirst($spec['type']);
        unset($spec['type']);
      } else if( !empty($spec['class']) ) {
        $class = $spec['class'];
        unset($spec['class']);
      } else {
        throw new Engine_Sanity_Exception('No type or class specified for test');
      }
      
      Engine_Loader::loadClass($class);
      $options = array_merge($spec, $options);
      $test = new $class($options);
    }

    if( !($test instanceof Engine_Sanity_Test_Interface ) ) {
      throw new Engine_Sanity_Exception('Test must be an instance of Engine_Sanity_Test_Abstract');
    }

    $this->_tests[] = $test;
    return $this;
  }

  public function addTests(array $tests)
  {
    foreach( $tests as $key => $value ) {
      if( is_array($value) ) {
        $value['basePath'] = $this->getBasePath();
      }
      if( is_numeric($key) ) {
        $this->addTest($value);
      } else {
        $this->addTest($key, $value);
      }
    }
    return $this;
  }

  public function clearTests()
  {
    $this->_tests = array();
    return $this;
  }

  public function getTests()
  {
    return $this->_tests;
  }

  public function setTests(array $tests)
  {
    $this->addTests($tests);
    return $this;
  }



  // Messages

  public function getMaxErrorLevel()
  {
    $maxErrorLevel = Engine_Sanity::ERROR_NONE;
    foreach( (array) $this->getTests() as $test ) {
      $maxErrorLevel = max($maxErrorLevel, $test->getMaxErrorLevel());
    }
    return $maxErrorLevel;
  }

  public function getMessages()
  {
    $messages = array();
    foreach( (array) $this->getTests() as $test ) {
      $testMessages = array();
      foreach( (array) $test->getMessages() as $testMessage ) {
        $testMessages[] = $testMessage->toString();
      }
      $messages[$test->getName()] = $testMessages;
    }
    return $messages;
  }

  public function hasMessages()
  {
    return count($this->getMessages() > 0);
  }



  // Translation

  static public function setDefaultTranslator(Zend_Translate $translate)
  {
    self::$_defaultTranslator = $translate;
  }

  static public function getDefaultTranslator()
  {
    return self::$_defaultTranslator;
  }

  static public function setDefaultDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
  {
    self::$_defaultDbAdapter = $dbAdapter;
  }

  static public function getDefaultDbAdapter()
  {
    return self::$_defaultDbAdapter;
  }
}