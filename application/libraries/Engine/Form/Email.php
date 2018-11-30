<?php

/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Email extends Engine_Form
{
  protected $_emailFieldName = null;
  protected $_emailAntispamEnabled = true;
  protected $_orgEmailFieldName = 'email';

  public function addEmailElement($attributes = array())
  {
    $emailFieldName = $this->getEmailElementFieldName();
    $attributes = array_merge(array(
        'required' => true,
        'allowEmpty' => false,
        'filters' => array(
            'StringTrim',
        ),
        'validators' => array(
            'EmailAddress'
        ),
        // Fancy stuff
        'inputType' => 'email',
        'class' => 'text',
      ), $attributes);

    $this->addElement('Text', $emailFieldName, $attributes);

    if ($emailFieldName !== $this->_orgEmailFieldName) {
      $this->addElement('Hidden', $this->_orgEmailFieldName, array(
          'order' => 70000
      ));

      $this->addElement('Hidden', $this->_orgEmailFieldName . '_field', array(
          'order' => 100000,
          'value' => base64_encode($emailFieldName)
      ));
    }

    return $this->{$emailFieldName};
  }

  public function getEmailElementFieldName()
  {
    if ($this->_emailFieldName !== null) {
      return $this->_emailFieldName;
    }

    if (!$this->isEmailAntispamEnabled()) {
      $this->_emailFieldName = $this->_orgEmailFieldName;
    } else if (isset($_POST[$this->_orgEmailFieldName . '_field'])) {
      $this->_emailFieldName = base64_decode($_POST[$this->_orgEmailFieldName . '_field']);
    } else {
      $this->_emailFieldName = Engine_String::str_random(10);
    }
    return $this->_emailFieldName;
  }

  public function isValid($params)
  {
    $isValid = parent::isValid($params);
    if ($isValid == true && $this->isEmailAntispamEnabled()) {
      if (!empty($params[$this->_orgEmailFieldName])) {
        $isValid = false;
      } else {
        $emailElementName = $this->getEmailElementFieldName();
        $this->{$this->_orgEmailFieldName}->setValue($params[$emailElementName]);
      }
    }

    return $isValid;
  }

  public function render(Zend_View_Interface $view = null)
  {
    if ($this->isEmailAntispamEnabled()) {
      $this->{$this->_orgEmailFieldName}->setValue('');
    }
    return parent::render($view);
  }

  public function isEmailAntispamEnabled()
  {
    return $this->_emailAntispamEnabled;
  }
}

