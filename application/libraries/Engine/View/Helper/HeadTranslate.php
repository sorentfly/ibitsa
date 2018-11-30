<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: HeadTranslate.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_HeadTranslate extends Zend_View_Helper_Abstract
{
  protected $_javascriptContainer = 'en4.core.language.addData(%s);';

  protected $_includeScriptTags = false;

  public function headTranslate($string = null)
  {
    if( null !== $string ) {
      if( is_array($string) ) {
        foreach( $string as $subString ) {
          $this->_getContainer()->append($subString);
        }
      } else {
        $this->_getContainer()->append($string);
      }
    }
    
    return $this;
  }

  public function render()
  {
    if( $this->_getContainer()->count() <= 0 ) {
      return '';
    }

    $content = '';

    // Header
    if( $this->_includeScriptTags ) {
      $content .= '<script type="text/javascript">' . "\n"
        .'//<![CDATA[' . "\n";
    }
    
    // Data
    $vars = array_flip(array_unique($this->_getContainer()->getArrayCopy()));
    foreach( $vars as $key => &$value ) {
      $value = $this->view->translate($key);
    }

    $content .= sprintf($this->_javascriptContainer, Zend_Json::encode($vars));

    // Footer
    if( $this->_includeScriptTags ) {
      $content .= "\n" . '//]]>' . "\n" .
        '</script>';
    }

    return $content;
  }
  
  public function __toString()
  {
    return $this->render();
  }

  public function toString()
  {
    return $this->render();
  }

  /**
   * Get the container
   * 
   * @return ArrayObject
   */
  protected function _getContainer()
  {
    if( !Zend_Registry::isRegistered(get_class($this)) ) {
      $container = new ArrayObject();
      Zend_Registry::set(get_class($this), $container);
    } else {
      $container = Zend_Registry::get(get_class($this));
    }
    return $container;
  }
}