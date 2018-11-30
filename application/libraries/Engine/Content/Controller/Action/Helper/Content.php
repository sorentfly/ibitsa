<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Content.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Content_Controller_Action_Helper_Content extends Zend_Controller_Action_Helper_Abstract
{
  // Properties
  
  protected $_enabled = false;

  protected $_content;

  protected $_contentName;
  
  public static $contentName = null;
  
  protected $_noRender = true;

  

  // General

  public function postDispatch()
  {
    if( $this->_enabled ) {
      $this->_enabled = false;
      $this->render();
      $this->reset();
    }
  }

  public function __call($method, $args)
  {
    throw new Engine_Content_Exception(sprintf("Invalid method '%s' called " .
        "on content action helper", $method));
  }


  // Options
  
  public function setEnabled($flag = true)
  {
    if (Engine_Content::getInstance()->getContentForward()) return;
    $this->getActionController()->autoContentEnabled =
        $this->_enabled = (bool) $flag;
    return $this;
  }

  public function getEnabled()
  {
    return (bool) $this->_enabled;
  }

  public function setNoRender($flag = true)
  {
    $this->_noRender = (bool) $flag;
    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
    $viewRenderer->setNoRender($this->_noRender);
    return $this;
  }

  public function getNoRender()
  {
    return (bool) $this->_noRender;
  }

  public function setContent(Engine_Content $content)
  {
    $this->_content = $content;
    return $this;
  }

  public function getContent()
  {
    if( null === $this->_content ) {
      $this->_content = Engine_Content::getInstance();
    }

    return $this->_content;
  }

  public function setContentName($name)
  {
    $this->_contentName = self::$contentName = $name;
    return $this;
  }

  public function getContentName()
  {
    if( null === $this->_contentName ) {
      $controller = $this->getActionController();
      $request = $controller->getRequest();
      $this->_contentName = self::$contentName = $request->getModuleName() . '_' . $request->getControllerName() . '_' . $request->getActionName();
    }

    return $this->_contentName;
  }



  public function reset()
  {
    $this->_enabled = false;
    $this->_contentName = self::$contentName = null;
  }



  // Rendering

  public function render()
  {
    if( empty($this->getActionController()->autoContentEnabled) ) {
      return;
    }
    
    // Prepare
    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
    $viewRenderer->setNoRender($this->_noRender);
    $response = $this->getResponse();
    
    // Generate
    $content = $this->getContent();
    $contentName = $this->getContentName();
    $contentMeta = $content->getMetaData($contentName);
    $contentBody = $content->render($contentName);

    // Prepare view and page info
    $view = $this->getActionController()->view;
    if( empty($view->layout()->siteinfo) ) {
      $view->layout()->siteinfo = array();
    }
    $siteinfo = $view->layout()->siteinfo;

    // Set title
    if( !empty($contentMeta['title']) && ($title = $view->translate($contentMeta['title'])) ) {
      $view->headTitle($title);
    }

    // Set description
    if( !empty($contentMeta['description']) ) {
      $description = $view->translate($contentMeta['description']);
      if( empty($siteinfo['description']) ) {
        $siteinfo['description'] = $description;
      } else {
        $siteinfo['description'] .= ' ' . $description;
      }
    }

    // Set keywords
    if( !empty($contentMeta['keywords']) ) {
      $keywords = $view->translate($contentMeta['keywords']);
      if( empty($siteinfo['keywords']) ) {
        $siteinfo['keywords'] = $keywords;
      } else {
        $siteinfo['keywords'] .= ' ' . $keywords;
      }
    }

    // Set layout
    if( !empty($contentMeta['layout']) ) {
      $view->layout()->setLayout($contentMeta['layout']);
    }

    // get id
    /*
    $siteinfo['identity'] = join('-', array(
      $this->getRequest()->getModuleName(),
      $this->getRequest()->getControllerName(),
      $this->getRequest()->getActionName(),
    ));
     * 
     */

    // Set siteinfo back
    $view->layout()->siteinfo = $siteinfo;

    // Save body
    $response->setBody($contentBody, 'default');

    return $this;
  }
}