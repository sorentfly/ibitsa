<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SingleRadio.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Element_SingleRadio extends Zend_Form_Element_Checkbox
{
  public $helper = 'FormSingleRadio';

  protected $_nameGroup;

  public function setNameGroup($nameGroup)
  {
    $this->_nameGroup = $nameGroup;
    return $this;
  }

  public function getFullyQualifiedName()
  {
    if( null === $this->_nameGroup )
    {
      return parent::getFullyQualifiedName();
    }

    return $this->_nameGroup;
  }

  public function getId()
  { 
      if (isset($this->id)) {
          return $this->id;
      }

      $id = parent::getFullyQualifiedName();

      // Bail early if no array notation detected
      if (!strstr($id, '[')) {
          return $id;
      }

      // Strip array notation
      if ('[]' == substr($id, -2)) {
          $id = substr($id, 0, strlen($id) - 2);
      }
      $id = str_replace('][', '-', $id);
      $id = str_replace(array(']', '['), '-', $id);
      $id = trim($id, '-');

      return $id;
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
}