<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FancyUpload.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Element_FancyUpload extends Zend_Form_Element_Hidden
{
  public function getValue()
  {
    if(!$this->_value && isset($_REQUEST["{$this->_name}"])){
        $this->_value = $_REQUEST["{$this->_name}"];
    }

    if(trim($this->_value == '')){
        return array();
    }

    return explode(" ", trim($this->_value));
  }

    public function setValue($value)
    {
        if (is_array($value)) $value = implode(' ', $value);
        return parent::setValue($value);
    }
  
  public function render(Zend_View_Interface $view = null)
  {
      if (null !== $view) {
          $this->setView($view);
      }

      $content = '';
      foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
      }
      return $content;
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
      $this->addDecorator('FormFancyUpload');
      Engine_Form::addDefaultDecorators($this);
      $this->removeDecorator('Label')->removeDecorator('HtmlTag');
    }
  }
}
