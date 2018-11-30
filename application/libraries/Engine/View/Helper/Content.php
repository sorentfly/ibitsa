<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Content.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_Content extends Zend_View_Helper_Abstract
{
  /**
   * Name of current area
   * 
   * @var string
   */
  protected $_name;

  /**
   * Render a content area by name
   * 
   * @param string $name
   * @return string
   */
  public function content($name = null)
  {
    // Direct access
    if( func_num_args() == 0 )
    {
      return $this;
    }

    if( func_num_args() > 1 )
    {
      $name = func_get_args();
    }

    $content = Engine_Content::getInstance();

    return $content->render($name);
  }

  public function renderWidget($name, $params = array())
  {
    $structure = array(
      'type' => 'widget',
      'name' => $name,
      'action' => ( !empty($params['action']) ? $params['action'] : 'index' ),
    );
    
    if( !empty($params) ) {
      $structure['request'] = new Zend_Controller_Request_Simple('index',
          'index', 'core', $params);
    }
    
    $DS = Engine_Api::_()->core()->getNowDomainSettings();
    if (isset($DS['widgets'][$name])){
    	$structure = Engine_Api::_()->core()->widgetSettingsOverload($structure, $DS['widgets'][$name]);
    }
    
    // Create element (with structure)
    $element = new Engine_Content_Element_Container(array(
      'elements' => array($structure),
      'decorators' => array(
        'Children',
        'Container'
      )
    ));
    if (!empty($params['disable_wrapper'])){
        return $element->getElement(0)->render();
    }
    return $element->render();
  }
}