<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ElementType.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Decorator_ElementType extends Zend_Form_Decorator_Abstract
{
  public function render($content)
  {
    $element = $this->getElement();
    $type = array_pop(explode('_', $element->getType()));
    $class = 'element_type_'.strtolower($type);

    if( isset($element->class) )
    {
      $class = $element->class.' '.$class;
    }
    $element->class = $class;
    
    return $content;
  }
}