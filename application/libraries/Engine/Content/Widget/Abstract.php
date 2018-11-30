<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Content_Widget_Abstract
{
  // Properties

  public $view;

  protected $_action;

  protected $_content;
  
  protected $_element;

  protected $_noRender = false;

  protected $_path;

  protected $_request;

  protected $_scriptPath;

  protected $_view;
  
  protected $_viewScript;

  
  // General

  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

  public function appendContent($content)
  {
    $this->_content .= $content;
    return $this;
  }

  public function getContent()
  {
    return $this->_content;
  }

  public function setElement(Engine_Content_Element_Abstract $element)
  {
    $this->_element = $element;
    return $this;
  }

  /**
   * @return Engine_Content_Element_Abstract
   */
  public function getElement()
  {
    return $this->_element;
  }

  public function setNoRender($flag = true)
  {
    $this->_noRender = (bool) $flag;
    $this->getElement()->setNoRender($flag);
    return $this;
  }

  public function getNoRender()
  {
    return (bool) $this->_noRender;
  }

  public function setPath($path)
  {
    $this->_path = $path;
    return $this;
  }

  public function getPath()
  {
    if( null === $this->_path ) {
      $r = new ReflectionClass(get_class($this));
      $this->_path = $r->getFileName();
    }

    return $this->_path;
  }

  public function setRequest(Zend_Controller_Request_Abstract $request)
  {
    $this->_request = $request;
    return $this;
  }
  /* @return  Zend_Controller_Request_Simple */
  public function getRequest()
  {
    return $this->_request;
  }

  public function setScriptPath($path)
  {
    $this->_scriptPath = $path;
    return $this;
  }

  public function getScriptPath()
  {
    if( null === $this->_scriptPath ) {
      $this->_scriptPath = $this->getPath() . '/scripts';
    }

    return $this->_scriptPath;
  }

  public function setView(Zend_View_Interface $view)
  {
    $this->_view = $view;
    return $this;
  }

  public function getView()
  {
    if( null === $this->_view ) {
      $this->_view = Engine_Content::getInstance()->getView();
      if( null === $this->_view ) {
        throw new Engine_Content_Widget_Exception('No view registered to widget');
      }
    }

    return $this->_view;
  }

	public function setViewScript($viewScript) {
		$this->_viewScript = $viewScript;
	}

	public function getViewScript() {
		return $this->_viewScript;
	}

  public function getCacheKey()
  {
    return null;
  }

  public function getCacheSpecificLifetime()
  {
    return false;
  }

  public function getCacheExtraContent()
  {
    
  }

  public function setCacheExtraData($data)
  {
    
  }



  // Params

  protected function _getParam($key, $default = null)
  {
    $element = $this->getElement();
    $value = $element->getParam($key);
    if( null !== $value ) {
      return $value;
    }
    $request = $this->getRequest();
    if( null !== $request && null !== ($value = $this->getRequest()->getParam($key)) ) {
      return $value;
    }

    return $default;
  }

  protected function _getAllParams()
  {
    $params = $this->getElement()->getParams();
    
    if( null !== ($request = $this->getRequest()) ) {
      $params = array_merge($request->getParams(), $params);
    }

    return $params;
  }

  // Rendering
  
  public function render($action = null)
  {
    try {
      ob_start();

      // Check action
      if( null !== $action && !is_string($action) ) {
        throw new Engine_Content_Widget_Exception('Action must be a string');
      }

      if( empty($action) ) {
        $action = 'index';
      }

      $this->_action = $action;
      $this->_viewScript = $action;
      
      $method = $this->inflect($action) . 'Action';
      if( !method_exists($this, $method) ) {
        throw new Engine_Content_Widget_Exception(sprintf('Action "%s" does not exist in widget "%s"', $action, get_class($this)));
      }
      
      // Caching (pre)
      $isCached = false;
      $cache = Engine_Content::getInstance()->getCache();
      $key = $this->getCacheKey();
      if( $cache instanceof Zend_Cache_Core && $key ) {
        if( $key === true ) {
          $key = get_class($this);
        } else {
          $key = get_class($this) . '_' . $key;
        }
        $cacheData = $cache->load($key);
        if( !empty($cacheData) && is_array($cacheData) && count($cacheData) == 2 ) {
          $this->setContent($cacheData[0]);
          $this->setCacheExtraData($cacheData[1]);
          $isCached = true;
        }
      }

      if( !$isCached ) {
      
        // Prepare stuff
        $view = $this->getView();
        $old = $view->getVars();
        $view->clearVars();
        $this->view = $view;

        // Pre-assign some info
        $view->identity = $this->getElement()->getIdentity();
        $view->element = $this->getElement();

        // Begin generation
        $content = '';

        // Call action
        $this->$method();

        // Render
        if( !$this->getNoRender() ) {
          $content = $this->renderScript();
        }
        $view->clearVars();
        $view->assign($old);
        $content .= ob_get_clean();

        $this->appendContent($content);

        // Caching (post)
        if( $cache instanceof Zend_Cache_Core && null !== $key ) {
          $content = $this->getContent();
          $extraContent = $this->getCacheExtraContent();
          if( !empty($content) || !empty($extraContent) ) {
            $cache->save(array($content, $extraContent), $key, array(), $this->getCacheSpecificLifetime());
          }
        }
        
      }
      
    } catch( Exception $e ) {
      ob_end_clean();
      if (defined('OUTPUT_WIDGET_EXCEPTIONS') && OUTPUT_WIDGET_EXCEPTIONS){
        echo($e);
      }
      throw $e;
    }

    return;
  }

  public function renderScript()
  {
    $path = $this->getScriptPath();
    $path = str_replace(APPLICATION_PATH . DIRECTORY_SEPARATOR, '', $path);
    $path .= DIRECTORY_SEPARATOR . $this->getViewScript() . '.tpl';

    $view = $this->getView();
    return $view->render($path);
  }



  // Utility

  public function inflect($action)
  {
    $action = str_replace(' ', '', ucwords(str_replace(array('.', '-'), ' ', $action)));
    return $action;
  }
}