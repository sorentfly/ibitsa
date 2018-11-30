<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Duration.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Element_Duration extends Zend_Form_Element_Select
{
  public $helper = 'formDuration';

  protected $_registerInArrayValidator = false;
  
  protected $_autoInsertNotEmptyValidator = false;
  
  public function init()
  {
    $this->setMultiOptions(array(
      'day' => 'Day(s)',
      'week' => 'Week(s)',
      'month' => 'Month(s)',
      'year' => 'Year(s)',
      'forever' => 'Forever',
    ));
  }

  public function setValue($value)
  {
    if( is_string($value) && preg_match('/^(\d+)\s+(\w+)$/', $value, $matches) ) {
      $value = array($matches[1], $matches[2]);
    }
    if( !is_array($value) || count($value) != 2 || '' === $value[0] ) {
      $value = null;
    }
    return parent::setValue($value);
  }

  public function isValid($value, $context = null)
  {
    $this->setValue($value);
    $value = $this->getValue();
    
    if( '' === $value || null === $value ) {
      if( !$this->isRequired() && $this->getAllowEmpty() ) {
        return true;
      } else {
        $this->addError('Value is required and can\'t be empty');
        return false;
      }
    }

    // Process
    $numValue = $value[0];
    $selValue = $value[1];

    // Validate number
    if( !in_array($selValue, array('lifetime', 'forever')) ) {
      if( !is_numeric($numValue) || (int) $numValue != $numValue || $numValue <= 0 ) {
        $this->addError('Please enter a valid integer greater than zero.');
        return false;
      }
    } else {
      $value[0] = $numValue = '0';
    }

    // Make composite options
    $options = array();
    foreach( $this->options as $k => $v ) {
      if( is_array($v) ) {
        $options = array_merge($options, $v);
      } else {
        $options[$k] = $v;
      }
    }

    // Validate selection
    if( !isset($options[$selValue]) ) {
      $this->addError('Please select an option.');
      return false;
    }

    $valid = parent::isValid($value, $context);
    return $valid;
  }

  /**
   * Load default decorators
   *
   * @return void
   */
  public function loadDefaultDecorators()
  {
    if( $this->loadDefaultDecoratorsIsDisabled() )
    {
      return;
    }

    $decorators = $this->getDecorators();
    if( empty($decorators) )
    {
      $this->addDecorator('ViewHelper');
      Engine_Form::addDefaultDecorators($this);
    }
  }

  protected function _getErrorMessages()
  {
    $translator = $this->getTranslator();
    $messages   = $this->getErrorMessages();
    $value      = $this->getValue();
    foreach ($messages as $key => $message) {
      if (null !== $translator) {
          $message = $translator->translate($message);
      }
//      if (($this->isArray() || is_array($value))
//          && !empty($value)
//      ) {
//          $aggregateMessages = array();
//          foreach ($value as $val) {
//              $aggregateMessages[] = str_replace('%value%', $val, $message);
//          }
//          $messages[$key] = implode($this->getErrorMessageSeparator(), $aggregateMessages);
//      } else {
          $messages[$key] = str_replace('%value%', $value, $message);
//      }
    }
    return $messages;
  }
}