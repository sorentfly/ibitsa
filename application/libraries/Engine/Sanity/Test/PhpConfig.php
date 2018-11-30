<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PhpConfig.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_PhpConfig extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'badValue' => 'Invalid php.ini directive value: %comparison_value% - %value%',
    'noIniGet' => 'Unable to check: ini_get function is disabled.'
  );

  protected $_messageVariables = array(
    'directive' => '_directive',
    'comparison_method' => '_comparisonMethod',
    'comparison_value' => '_comparisonValue',
    'value' => '_value',
    'raw_value' => '_rawValue',
  );

  protected $_directive;

  protected $_comparisonMethod;

  protected $_comparisonValue;

  protected $_rawValue;
  
  protected $_value;

  public function setDirective($directive)
  {
    $this->_directive = $directive;
    return $this;
  }

  public function getDirective()
  {
    return $this->_directive;
  }

  public function setComparisonMethod($method)
  {
    $method = str_replace(array('>', '<', '=', '!'), array('g', 'l', 'e', 'n'), $method);
    $this->_comparisonMethod = $method;
    return $this;
  }

  public function getComparisonMethod()
  {
    return $this->_comparisonMethod;
  }

  public function setComparisonValue($value)
  {
    $this->_comparisonValue = $value;
    return $this;
  }

  public function getComparisonValue()
  {
    return $this->_comparisonValue;
  }
  
  public function execute()
  {
    $directive = $this->getDirective();
    $comparisonMethod = $this->getComparisonMethod();
    $comparisonValue = $this->getComparisonValue();
    $method = '_compare_' . strtolower($comparisonMethod);

    // Damn, ini_get is disabled
    if( !function_exists('ini_get') ) {
      return $this->_error('noIniGet');
    }
    
    if( !empty($directive) && method_exists($this, $method) ) {
      $this->_rawValue = $value = ini_get($directive);
      $value = trim($value);

      // Try to guess if it's going to be a byte shorthand
      if( $this->_is_byte_shorthand($value) ) {
        $value = $this->_return_bytes($value);
      }

      $this->_value = $value;

      if( !$this->$method($value, $comparisonValue) ) {
        return $this->_error('badValue');
      }
    }
  }



  // Comparison methods

  protected function _compare_g($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue > $expectedValue );
  }

  protected function _compare_ge($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue >= $expectedValue );
  }

  protected function _compare_l($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue < $expectedValue );
  }

  protected function _compare_le($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue <= $expectedValue );
  }

  protected function _compare_e($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue == $expectedValue );
  }

  protected function _compare_ee($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue == $expectedValue );
  }

  protected function _compare_eee($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue === $expectedValue );
  }

  protected function _compare_ne($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue != $expectedValue );
  }

  protected function _compare_nee($iniValue, $expectedValue)
  {
    return (bool) ( $iniValue !== $expectedValue );
  }

  protected function _compare_array($iniValue, $expectedValue)
  {
    return (bool) in_array($iniValue, $expectedValue);
  }

  protected function _compare_preg($iniValue, $expectedValue)
  {
    return (bool) preg_match($expectedValue, $iniValue);
  }

  
  // Utility
  
  protected function _is_byte_shorthand($value)
  {
    if( is_numeric($value) || !is_string($value) ) {
      return false;
    }
    $value = trim($value);
    return (bool) preg_match('/^\d+[gmk]$/', $value);
  }

  protected function _return_bytes($value)
  {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
  }
}