<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Container.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Content_Decorator_Container extends Engine_Content_Decorator_Abstract
{
  public $helper = 'htmlContainer';

  public function getClass()
  {
    $name = $this->getElement()->getName();
    if( null === $name ) {
      return null;
    }
    $manual = $this->getParam('adv_class');
    return 'generic_layout_container layout_' . preg_replace('/[^\w\d]/', '_', $name). ($manual? ' '.$manual:'');
  }
  
  public function render($content)
  {
    $element = $this->getElement();
    $separator = $this->getSeparator();
    $attribs = array_merge($element->getAttribs(), $this->getParams());
    $view = $element->getView();

    // Auto class
    $class = $this->getClass();
    if( null !== $class ) {
      if( empty($attribs['class']) ) {
        $attribs['class'] = $class;
      } else {
        $attribs['class'] .= ' ' . $class;
      }
    }

    return $view->{$this->helper}($content, $attribs) . $separator;
  }
}
