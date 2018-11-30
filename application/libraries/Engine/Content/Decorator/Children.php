<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Children.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Content_Decorator_Children extends Engine_Content_Decorator_Abstract
{
  protected $_placement = null;
  
  public function render($content)
  {
    $element = $this->getElement();
    $separator = $this->getSeparator();
    $placement = $this->getPlacement();

    // Render children
    $childContent = '';
    foreach( $element->getElements() as $childElement ) {
      $childContent .= $separator . $childElement->render();
    }

    // Place child content
    switch( $placement ) {
      default:
        $content = $childContent;
        break;
      case self::APPEND:
        $content .= $separator . $childContent;
        break;
      case self::PREPEND;
        $content = $childContent . $separator . $content;
        break;
    }

    return $content;
  }
}
