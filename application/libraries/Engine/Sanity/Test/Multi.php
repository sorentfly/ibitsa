<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Multi.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_Multi extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'allTestsFailed' => 'Failed',
    'someTestsFailed' => 'Failed',
    'oneTestFailed' => 'Failed',
  );

  protected $_messageVariables = array(
    
  );

  protected $_tests;

  protected $_breakOnFailure = false;

  protected $_allForOne = true;

  public function setBreakOnFailure($flag)
  {
    $this->_breakOnFailure = (bool) $flag;
    return $this;
  }

  public function setAllForOne($flag)
  {
    $this->_allForOne = (bool) $flag;
    return $this;
  }

  public function execute()
  {
    $andResults = true;
    $orResults = false;
    foreach( $this->getTests() as $test ) {
      $test->execute();
      $result = (bool) !$test->hasMessages();
      if( $this->_breakOnFailure ) {
        // A test failed
        if( !$result ) {
          return $this->_error('oneTestFailed');
        }
      }

      $andResults &= $result;
      $orResults |= $result;
    }

    if( $this->_allForOne ) {
      // No tests passed
      if( !$orResults ) {
        return $this->_error('allTestsFailed');
      }
    } else {
      // At least one test did not pass
      if( !$andResults ) {
        return $this->_error('someTestsFailed');
      }
    }
  }

  public function addTest($spec, $options = array())
  {
    if( $spec instanceof Engine_Sanity_Test_Abstract ) {
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

    if( !($test instanceof Engine_Sanity_Test_Abstract ) ) {
      throw new Engine_Sanity_Exception('Test must be an instance of Engine_Sanity_Test_Abstract');
    }

    $this->_tests[] = $test;
    return $this;
  }

  public function addTests(array $tests)
  {
    foreach( $tests as $key => $value ) {
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
}