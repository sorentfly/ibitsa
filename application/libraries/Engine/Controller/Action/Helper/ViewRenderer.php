<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ViewRenderer.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer
{
  protected $_viewScriptPathSpec = 'application/modules/{module}/views/scripts/{controller}/{action}.{suffix}';

  public function getInflector()
  {
    throw new Exception('Removed');
  }

  public function setInflector(Zend_Filter_Inflector $inflector, $reference = false)
  {
    throw new Exception('Removed');
  }

  public function getViewScript($action = null, array $vars = array())
  {
    if ((null === $action) && (!isset($vars['action']))) {
        $action = $this->getScriptAction();
        if (null === $action) {
            $action = $this->getRequest()->getActionName();
        }
        $vars['action'] = $action;
    } elseif (null !== $action) {
        $vars['action'] = $action;
    }
    $vars['module'] = ucfirst($this->getModule());
    $vars['controller'] = $this->getRequest()->getControllerName();
    $vars['suffix'] = $this->getViewSuffix();

    $newVars = array();
    foreach( $vars as $key => $value )
    {
      $newVars['{'.$key.'}'] = $value;
    }

    $path = $this->getViewScriptPathSpec();
    $path = str_replace(array_keys($newVars), array_values($newVars), $path);
    return $path;
  }

  public function setBasePath($path)
  {
    $this->_basePath = $path;
    return $this;
  }

  protected function _getBasePath()
  {
    if( null === $this->_basePath ) {
      $this->setBasePath(APPLICATION_PATH);
    }
    return $this->_basePath;
  }

  public function initView($path = null, $prefix = null, array $options = array())
  {
    if( null === $this->view )
    {
      throw new Engine_Exception('No view set in ViewRenderer');
    }

    // Reset some flags every time
    $options['noController'] = (isset($options['noController'])) ? $options['noController'] : false;
    $options['noRender']     = (isset($options['noRender'])) ? $options['noRender'] : false;
    $this->_scriptAction     = null;
    $this->_responseSegment  = null;

    // Set options first; may be used to determine other initializations
    $this->_setOptions($options);

    // Get base view path
    $path = $this->_getBasePath();

    // Get class path
    $request = $this->getRequest();
    $module  = $request->getModuleName();
    if (null === $module) {
        $module = $this->getFrontController()->getDispatcher()->getDefaultModule();
    }
    $classPath = APPLICATION_PATH.DS.'application'.DS.'modules'.DS.$module.DS.'View'.DS;

    // Get class prefix
    if( null === $prefix )
    {
      $prefix = ucfirst($module).'_View_';
    }

    // Determine if this path has already been registered
    $currentPaths = $this->view->getScriptPaths();
    $path         = str_replace(array('/', '\\'), '/', $path);
    $pathExists   = false;
    foreach ($currentPaths as $tmpPath) {
        $tmpPath = str_replace(array('/', '\\'), '/', $tmpPath);
        if (strstr($tmpPath, $path)) {
            $pathExists = true;
            break;
        }
    }
    if (!$pathExists) {
      $this->view->addScriptPath($path);
    }
    $this->view->addHelperPath($classPath . 'Helper', $prefix . 'Helper');
    $this->view->addFilterPath($classPath . 'Filter', $prefix . 'Filter');

    // Register view with action controller (unless already registered)
    if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
        $this->_actionController->view       = $this->view;
        $this->_actionController->viewSuffix = $this->_viewSuffix;
    }
  }

  /*
  public $view;

  protected $_frontController;
  protected $_neverController = false;
  protected $_neverRender     = false;
  protected $_noController    = false;
  protected $_noRender        = false;
  protected $_responseSegment = null;
  protected $_scriptAction    = null;
  protected $_viewSuffix      = 'tpl';

  protected $_viewBasePathSpec = '{globalDir}';
  protected $_viewScriptPathSpec = '{moduleDir}/views/scripts/{controller}/{action}.{suffix}';


  // Main
  
  public function __construct(Zend_View_Interface $view = null)
  {
    if( null !== $view )
    {
      $this->setView($view);
    }
  }

  public function init()
  {
    if( $this->getFrontController()->getParam('noViewRenderer') )
    {
      return;
    }
    
    $this->initView();
  }

  public function initView($path = null, $prefix = null, array $options = array())
  {
    if( null === $this->view )
    {
      throw new Engine_Exception('No view set in ViewRenderer');
    }

    // Reset some flags every time
    $options['noController'] = (isset($options['noController'])) ? $options['noController'] : false;
    $options['noRender']     = (isset($options['noRender'])) ? $options['noRender'] : false;
    $this->_scriptAction     = null;
    $this->_responseSegment  = null;

    // Set options first; may be used to determine other initializations
    $this->_setOptions($options);

    // Get base view path
    $path = APPLICATION_PATH.DS.'application'.DS.'modules';
    
    // Get class path
    $request = $this->getRequest();
    $module  = $request->getModuleName();
    if (null === $module) {
        $module = $this->getFrontController()->getDispatcher()->getDefaultModule();
    }
    $classPath = APPLICATION_PATH.DS.'application'.DS.'modules'.DS.$module.DS.'View'.DS;

    // Get class prefix
    if( null === $prefix )
    {
      $prefix = ucfirst($module).'_View_';
    }

    // Determine if this path has already been registered
    $currentPaths = $this->view->getScriptPaths();
    $path         = str_replace(array('/', '\\'), '/', $path);
    $pathExists   = false;
    foreach ($currentPaths as $tmpPath) {
        $tmpPath = str_replace(array('/', '\\'), '/', $tmpPath);
        if (strstr($tmpPath, $path)) {
            $pathExists = true;
            break;
        }
    }
    if (!$pathExists) {
      $this->view->addScriptPath($path);
    }
    $this->view->addHelperPath($classPath . 'Helper', $classPrefix . 'Helper');
    $this->view->addFilterPath($classPath . 'Filter', $classPrefix . 'Filter');

    // Register view with action controller (unless already registered)
    if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
        $this->_actionController->view       = $this->view;
        $this->_actionController->viewSuffix = $this->_viewSuffix;
    }
  }
  
  public function postDispatch()
  {
    if( $this->_shouldRender() )
    {
      $this->render();
    }
  }



  // Options

  public function setView(Zend_View_Interface $view)
  {
    $this->view = $view;
    return $this;
  }
  
  public function setRender($action = null, $name = null, $noController = null)
  {
      if (null !== $action) {
          $this->setScriptAction($action);
      }

      if (null !== $name) {
          $this->setResponseSegment($name);
      }

      if (null !== $noController) {
          $this->setNoController($noController);
      }

      return $this;
  }
    public function setScriptAction($name)
    {
        $this->_scriptAction = (string) $name;
        return $this;
    }
    public function setResponseSegment($name)
    {
        if (null === $name) {
            $this->_responseSegment = null;
        } else {
            $this->_responseSegment = (string) $name;
        }

        return $this;
    }
    public function setNoController($flag = true)
    {
        $this->_noController = ($flag) ? true : false;
        return $this;
    }
    public function getViewScript($action = null, array $vars = array())
    {
        $request = $this->getRequest();
        if ((null === $action) && (!isset($vars['action']))) {
            $action = $this->getScriptAction();
            if (null === $action) {
                $action = $request->getActionName();
            }
            $vars['action'] = $action;
        } elseif (null !== $action) {
            $vars['action'] = $action;
        }


        $script =
        $inflector = $this->getInflector();
        if ($this->getNoController() || $this->getNeverController()) {
            $this->_setInflectorTarget($this->getViewScriptPathNoControllerSpec());
        } else {
            $this->_setInflectorTarget($this->getViewScriptPathSpec());
        }
        return $this->_translateSpec($vars);
    }
    public function getScriptAction()
    {
        return $this->_scriptAction;
    }



  // Rendering

  public function render($action = null, $name = null, $noController = null)
  {
    $this->setRender($action, $name, $noController);
    $path = $this->getViewScript();
    $this->renderScript($path, $name);
  }


  
  // Utility
  
  protected function _setOptions(array $options)
  {
      foreach ($options as $key => $value)
      {
          switch ($key) {
              case 'neverRender':
              case 'neverController':
              case 'noController':
              case 'noRender':
                  $property = '_' . $key;
                  $this->{$property} = ($value) ? true : false;
                  break;
              case 'responseSegment':
              case 'scriptAction':
              case 'viewBasePathSpec':
              case 'viewScriptPathSpec':
              //case 'viewScriptPathNoControllerSpec':
              case 'viewSuffix':
                  $property = '_' . $key;
                  $this->{$property} = (string) $value;
                  break;
              default:
                  break;
          }
      }

      return $this;
  }
  
  protected function _shouldRender()
  {
    return (!$this->getFrontController()->getParam('noViewRenderer')
        && !$this->_neverRender
        && !$this->_noRender
        && (null !== $this->_actionController)
        && $this->getRequest()->isDispatched()
        && !$this->getResponse()->isRedirect()
    );
  }
   * 
   */
}