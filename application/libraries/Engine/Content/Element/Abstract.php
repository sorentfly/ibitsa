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
abstract class Engine_Content_Element_Abstract
{
  // Constants

  const DECORATOR = 'DECORATOR';
  const ELEMENT = 'ELEMENT';
  


  // Properties

  protected $_attribs = array();

  protected $_belongsTo;

  protected $_decorators = array();
  
  protected $_elements = array();

  protected $_elementsNeedSort = false;

  protected $_elementsOrder = array();

  protected $_identity;

  protected $_loaders = array();

  protected $_name;

  protected $_noRender = false;
  
  protected $_order;

  protected $_params = array();
  
  protected $_parent;

  protected $_title;

  protected $_view;



  // General

  public function __construct($options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }

    $this->loadDefaultDecorators();
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      } else {
        $this->setAttrib($key, $value);
      }
    }
    
    return $this;
  }

  public function loadDefaultDecorators()
  {
    if( !empty($this->_decorators) ) {
      return;
    }

    $this
      ->addDecorator('Title')
      ->addDecorator('Container');
  }



  // Info

  public function setIdentity($identity)
  {
    $this->_identity = $identity;
    return $this;
  }

  public function getIdentity()
  {
    return $this->_identity;
  }

  public function setName($name)
  {
    $this->_name = (string) $name;
    return $this;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function setTitle($title)
  {
    $this->_title = (string) $title;
    return $this;
  }

  public function getTitle()
  {
    return $this->_title;
  }

  public function setNoRender($flag = true)
  {
    $this->_noRender = (bool) $flag;
    return $this;
  }

  public function getNoRender()
  {
    return (bool) $this->_noRender;
  }
  
  public function getOrder()
  {
    return $this->_order;
  }

  public function setOrder($order)
  {
    $this->_order = (int) $order;
    return $this;
  }

  public function setParent(Engine_Content_Element_Abstract $element)
  {
    $this->_parent = $element;
    return $this;
  }

  public function getParent()
  {
    return $this->_parent;
  }



  // Attributes

  public function setAttrib($key, $value)
  {
    $this->_attribs[$key] = $value;
    return $this;
  }

  public function setAttribs(array $attribs)
  {
    foreach( $attribs as $key => $value ) {
      $this->_attribs[$key] = $value;
    }
    return $this;
  }

  public function getAttrib($key, $default = null)
  {
    if( isset($this->_attribs[$key]) ) {
      return $this->_attribs[$key];
    } else {
      return $default;
    }
  }

  public function getAttribs()
  {
    return $this->_attribs;
  }



  // Params

  public function setParam($key, $value)
  {
    $this->_params[$key] = $value;
    return $this;
  }

  public function setParams(array $attribs)
  {
    foreach( $attribs as $key => $value ) {
      $this->_params[$key] = $value;
    }
    return $this;
  }

  public function getParam($key, $default = null)
  {
    if( isset($this->_params[$key]) ) {
      return $this->_params[$key];
    } else {
      return $default;
    }
  }

  public function getParams()
  {
    return $this->_params;
  }



  // Decorators

  public function addDecorator($decorator, $options = null)
  {
    if ($decorator instanceof Zend_Form_Decorator_Interface) {
      $name = get_class($decorator);

    } elseif( is_string($decorator) ) {
      $name      = $decorator;
      $decorator = array(
          'decorator' => $name,
          'options'   => $options,
      );

    } elseif( is_array($decorator) ) {
        foreach ($decorator as $name => $spec) {
            break;
        }
        if (is_numeric($name)) {
            throw new Engine_Content_Element_Exception('Invalid alias provided to addDecorator; must be alphanumeric string');
        }
        if (is_string($spec)) {
          $decorator = array(
            'decorator' => $spec,
            'options'   => $options,
          );
        } elseif ($spec instanceof Zend_Form_Decorator_Interface) {
          $decorator = $spec;
        }
    } else {
        throw new Engine_Content_Element_Exception('Invalid decorator provided to addDecorator; must be string or Engine_Content_Decorator_Abstract');
    }

    $this->_decorators[$name] = $decorator;

    return $this;
  }

  public function addDecorators(array $decorators)
  {
    foreach( $decorators as $decoratorInfo ) {
      if( $decoratorInfo instanceof Engine_Content_Decorator_Abstract ) {
        $this->addDecorator($decoratorInfo);
      } else if( is_string($decoratorInfo) ) {
        $this->addDecorator($decoratorInfo);
      } else if( is_array($decoratorInfo) ) {
        if( isset($decoratorInfo['decorator']) ) {
          $decorator = $decoratorInfo['decorator'];
          $options = ( isset($decoratorInfo['options']) && is_array($decoratorInfo['options']) ? $decoratorInfo['options'] : null );
          $this->addDecorator($decorator, $options);
        } else {
          $argc = count($decoratorInfo);
          $options = array();
          switch (true) {
            case (0 == $argc):
              break;
            case (1 <= $argc):
              $decorator  = array_shift($decoratorInfo);
            case (2 <= $argc):
              $options = array_shift($decoratorInfo);
            default:
              $this->addDecorator($decorator, $options);
              break;
          }
        }
      } else {
        throw new Engine_Content_Element_Exception('Invalid decorator passed to addDecorators()');
      }
    }
    
    return $this;
  }

  public function clearDecorators()
  {
    $this->_decorators = array();
    return $this;
  }

  public function setDecorators(array $decorators)
  {
    $this->clearDecorators();
    $this->addDecorators($decorators);
    return $this;
  }

  public function getDecorator($name)
  {
    if (!isset($this->_decorators[$name])) {
      $len = strlen($name);
      foreach ($this->_decorators as $localName => $decorator) {
        if ($len > strlen($localName)) {
          continue;
        }

        if (0 === substr_compare($localName, $name, -$len, $len, true)) {
          if (is_array($decorator)) {
            return $this->_loadDecorator($decorator, $localName);
          }
          return $decorator;
        }
      }
      return false;
    }

    if (is_array($this->_decorators[$name])) {
      return $this->_loadDecorator($this->_decorators[$name], $name);
    }

    return $this->_decorators[$name];
  }

  public function getDecorators()
  {
    foreach ($this->_decorators as $key => $value) {
      if( is_array($value) ) {
        $this->_loadDecorator($value, $key);
      }
    }
    return $this->_decorators;
  }

  public function removeDecorator($name)
  {
    if (isset($this->_decorators[$name])) {
      unset($this->_decorators[$name]);
    } else {
      $len = strlen($name);
      foreach (array_keys($this->_decorators) as $decorator) {
        if ($len > strlen($decorator)) {
          continue;
        }
        if (0 === substr_compare($decorator, $name, -$len, $len, true)) {
          unset($this->_decorators[$decorator]);
          break;
        }
      }
    }

    return $this;
  }
  
  protected function _loadDecorator(array $decorator, $name)
  {
    $sameName = false;
    if ($name == $decorator['decorator']) {
        $sameName = true;
    }

    $class = $this->getPluginLoader(self::DECORATOR)->load($name);
    if( null === $decorator['options'] ) {
      $instance = new $class;
    } else {
      $instance = new $class($decorator['options']);
    }

    $instance->setElement($this);

    if ($sameName) {
        $newName            = get_class($instance);
        $decoratorNames     = array_keys($this->_decorators);
        $order              = array_flip($decoratorNames);
        $order[$newName]    = $order[$name];
        $decoratorsExchange = array();
        unset($order[$name]);
        asort($order);
        foreach ($order as $key => $index) {
            if ($key == $newName) {
                $decoratorsExchange[$key] = $instance;
                continue;
            }
            $decoratorsExchange[$key] = $this->_decorators[$key];
        }
        $this->_decorators = $decoratorsExchange;
    } else {
        $this->_decorators[$name] = $instance;
    }

    return $instance;
  }


  
  // Elements

  public function addElement($spec, $options = null)
  {
    if( $spec instanceof Engine_Content_Element_Abstract ) {
      $element = $spec;
    } else if( is_string($spec) ) {
      $element = $this->createElement($spec, $options);
    } else if( is_array($spec) ) {
      if( !isset($spec['type']) ) {
        throw new Engine_Content_Element_Exception('Element must have a type');
      }
      $type = $spec['type'];
      unset($spec['type']);
      if( is_array($options) ) $spec = array_merge($spec, $options);
      $element = $this->createElement($type, $spec);
    } else {
      throw new Engine_Content_Element_Exception('Unknown element spec');
    }

    $this->_elements[] = $element;
    $this->_elementsNeedSort = true;
    
    return $this;
  }

  public function addElements(array $elements)
  {
    foreach( $elements as $key => $value ) {
      $this->addElement($value);
    }
    return $this;
  }

  public function clearElements()
  {
    $this->_elements = array();
    $this->_elementsOrder = array();
    $this->_elementsNeedSort = false;
    return $this;
  }

  public function createElement($type, $options = null)
  {
    if( !is_string($type) ) {
      throw new Engine_Content_Element_Exception('Element type must be a string indicating type');
    }

    $class = $this->getPluginLoader(self::ELEMENT)->load($type);
    $element = new $class($options);

    return $element;
  }

  public function getElement($index)
  {
    if( isset($this->_elements[$index]) ) {
      return $this->_elements[$index];
    }
    return null;
  }

  public function getElements()
  {
    if( $this->_elementsNeedSort ) {
      $this->_sortElements();
    }

    return $this->_elements;
  }

  public function setElements(array $elements)
  {
    $this->addElements($elements);
    return $this;
  }

  public function removeElement($index)
  {
    if( isset($this->_elements[$index]) ) {
      unset($this->_elements[$index]);
      unset($this->_elementsOrder[$index]);
    }
    return $this;
  }

  protected function _sortElements()
  {
    foreach( $this->_elements as $index => $element ) {
      $this->_elementsOrder[$index] = $element->getOrder();
    }

    asort($this->_elementsOrder);

    $elements = array();
    foreach( $this->_elementsOrder as $index => $order ) {
      $elements[$index] = $this->_elements[$index];
    }

    $this->_elements = $elements;
    $this->_elementsNeedSort = false;
  }



  // Plugin Loader

  public function getPluginLoader($type)
  {
    $type = strtoupper($type);
    if( !isset($this->_loaders[$type]) ) {
      switch( $type ) {
        case self::DECORATOR:
          $prefixSegment = 'Content_Decorator';
          $pathSegment   = 'Content/Decorator';
          break;
        case self::ELEMENT:
          $prefixSegment = 'Content_Element';
          $pathSegment   = 'Content/Element';
          break;
        default:
          throw new Engine_Content_Element_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
          break;
      }

      $this->_loaders[$type] = new Zend_Loader_PluginLoader(
          array('Engine_' . $prefixSegment . '_' => 'Engine/' . $pathSegment . '/')
      );
    }

    return $this->_loaders[$type];
  }

  public function setPluginLoader($type, $pluginLoader)
  {
    switch( $type ) {
      case self::DECORATOR:
      case self::ELEMENT:
        $this->_loaders[$type] = $pluginLoader;
        break;
      default:
        throw new Engine_Content_Element_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        break;
    }
  }



  // Rendering

  public function getView()
  {
    if( null === $this->_view ) {
      return Engine_Content::getInstance()->getView();
    }

    return $this->_view;
  }

  public function setView(Zend_View_Interface $view)
  {
    $this->_view = $view;
    return $this;
  }

  public function render()
  {
    $content = $this->_render();

    // Do not render decorators if no content
    //if( '' === $content ) {
    if( $this->getNoRender() ) {
      return '';
    }

    // Do decorators
    foreach( $this->getDecorators() as $decorator ) {
      $decorator->setElement($this);
      $content = $decorator->render($content);
    }
    
    return $content;
  }
  
  public function __toString()
  {
    try {
      $return = $this->render();
      return $return;
    } catch (Exception $e) {
      $message = "Exception caught by form: " . $e->getMessage()
               . "\nStack Trace:\n" . $e->getTraceAsString();
      trigger_error($message, E_USER_WARNING);
      return '';
    }
  }

  abstract protected function _render();
}