<?php

class Engine_Template_Renderer
{
  protected $_cachePath;

  protected $_templatePath;

  protected $_templateSuffix = 'tpl';

  protected $_templateName;

  protected $_vars = array();
  
  public function __construct(array $options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  public function __get($key)
  {
    if( isset($this->_vars[$key]) ) {
      return $this->_vars[$key];
    } else {
      return null;
    }
  }

  public function __set($key, $value)
  {
    $this->_vars[$key] = $value;
  }

  public function assign($key, $value = null)
  {
    if( is_string($key) ) {
      $this->_vars[$key] = $value;
    } else if( is_array($key) ) {
      foreach( $key as $k => $v ) {
        $this->assign($k, $v);
      }
    }
    return $this;
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }
    return $this;
  }



  // Options

  public function setCachePath($cachePath)
  {
    $this->_cachePath = rtrim($cachePath, '\\/');
    return $this;
  }

  public function setTemplatePath($templatePath)
  {
    $this->_templatePath = rtrim($templatePath, '\\/');
    return $this;
  }

  public function setTemplateSuffix($templateSuffix)
  {
    $templateSuffix = preg_replace('/[^a-z0-9]/i', '', $templateSuffix);
    $this->_templateSuffix = $templateSuffix;
    return $this;
  }





  // Actions

  public function render($template)
  {
    $template = $this->_fixTemplateName($template);
    $template = $this->_templatePath . DIRECTORY_SEPARATOR . $template;
    $this->_templateName = $template;

    return $this->_render();
  }

  public function translate($template, $container = null)
  {
    $template = $this->_fixTemplateName($template);
    $template = $this->_templatePath . DIRECTORY_SEPARATOR . $template;

    if( !file_exists($template) ) {
      throw new Exception('Missing template');
    }

    $root = Engine_Template_Processor::process(file_get_contents($template));
    $content = Engine_Template_Processor::toJavascript($root);
    //$root = Engine_Template_Token::fromString(file_get_contents($template));
    //$content = $root->toJavascript();

//    echo "<pre>";
//    echo $root->outputDebug();
//    die();



    if( null === $container ) {
      return $content;
    } else {
      return 'window["' . $container . '"] = function() {' . "\n" . $content . "\n" . '}';
    }
  }

  public function isJavascript()
  {
    return false;
  }

  public function log($value)
  {
    // @todo
  }




  protected function _render()
  {
    //extract($this->_vars);
    include $this->_templateName;
  }
  
  protected function _fixTemplateName($template)
  {
    $template = preg_replace('/\.{1,}/', '.', $template);
    $tsf = '.' . $this->_templateSuffix;
    if( strtolower(substr($template, - strlen($tsf))) !== strtolower($tsf) ) {
      $template .= $tsf;
    }
    return $template;
  }
}
