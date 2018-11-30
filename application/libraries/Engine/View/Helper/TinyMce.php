<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: TinyMce.php 10118 2013-11-20 17:15:32Z andres $
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_TinyMce extends Zend_View_Helper_Abstract
{
  protected $_enabled = false;
  protected $_defaultScript = 'externals/tinymce4/tinymce.min.js';
  public $identity = null;
  
  protected $_config = array(
      'renderJSFunction' => 'initTinymce_full'
  );
  protected $_scriptPath;
  protected $_scriptFile;

  public function __set($name, $value)
  {
	  $method = 'set' . $name;
	  if( !method_exists($this, $method) ) {
      throw new Engine_Exception('Invalid tinyMce property');
    }
    $this->$method($value);
  }

  public function __get($name)
  {
    $method = 'get' . $name;
    if( !method_exists($this, $method) ) {
      throw new Engine_Exception('Invalid tinyMce property');
    }
    return $this->$method();
  }

  public function setOptions(array $options)
  {
    $methods = get_class_methods($this);
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( in_array($method, $methods) ) {
        $this->$method($value);
      } else {
        $this->_config[$key] = $value;
      }
    }
    return $this;
  }

  public function TinyMce()
  {
    return $this;
  }

  public function setLanguage($language)
  {
    $this->_config['language'] = $language;
    return $this;
  }

  public function setDirectionality($directionality)
  {
    $this->_config['directionality'] = $directionality;
    return $this;
  }
  
  public function setRenderJSFunction($jsFunctionName)
  {
    $this->_config['renderJSFunction'] = $jsFunctionName;

    return $this;
  }

  public function setScriptPath($path)
  {
    $this->_scriptPath = rtrim($path, '/');
    return $this;
  }

  public function setScriptFile($file)
  {
    $this->_scriptFile = (string) $file;
  }

  public function render()
  {
    if( false === $this->_enabled ) {
      $this->_renderScript();
    }
    $this->_renderEditor();
    $this->_enabled = true;
  }

  protected function _renderScript()
  {
    if( null === $this->_scriptFile ) {
      $script = $this->view->baseUrl() . '/' . $this->_defaultScript;
    } else {
      if( null === $this->_scriptPath ) {
        $this->_scriptPath = $this->view->baseUrl();
      }
      $script = $this->_scriptPath . '/' . $this->_scriptFile;
    }

    $this->view->headScript()->appendFile($script);
    return $this;
  }

  protected function _renderEditor()
  {
    //$this->view->headScript()->captureStart(Zend_View_Helper_Placeholder_Container_Abstract::APPEND);
    /* NOTE - это не выводится в шапку, так как при отклбчении лаяута перестаёт работать (многий код к сожалению заточен на вывод без лаяута - аякс в олимпиадах например) */
    echo '<script>'. $this->_config['renderJSFunction'].'("#'.$this->identity.'");' . '</script>';
    //$this->view->headScript()->captureEnd(Zend_View_Helper_Placeholder_Container_Abstract::APPEND);
    return $this;
  }
}
