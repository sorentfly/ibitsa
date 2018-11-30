<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Hooks.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_Hooks extends Zend_View_Helper_Abstract
{
  protected $_separator = "\n\n";
  
  public function hooks($name, $payload = null)
  {
    $dispatcher = Engine_Hooks_Dispatcher::getInstance();
    $event = $dispatcher->callEvent($name, $payload);
    $responses = $event->getResponses();

    if( !is_array($responses) || empty($responses) ) {
      return '';
    }

    $content = '';
    foreach( $responses as $response ) {
      if( is_string($response) ) {
        $content .= $response;
      } else if( is_array($response) && !empty($response['type']) ) {
        if( $response['type'] == 'partial' ) {
          $content .= $this->view->partial(@$response['args'][0], @$response['args'][1], @$response['args'][2], @$response['args'][3]);
        } else if( $response['type'] == 'action' ) {
          $content .= $this->view->action(@$response['args'][0], @$response['args'][1], @$response['args'][2], @$response['args'][3]);
        }
      } else {
        throw new Zend_View-Exception('Unknown data type returned in '.get_class($this));
        continue;
      }

      $content .= $this->getSeparator();
    }

    return $content;
  }

  public function getSeparator()
  {
    return $this->_separator;
  }

  public function setSeparator($separator)
  {
    $this->_separator = $separator;
    return $this;
  }
}