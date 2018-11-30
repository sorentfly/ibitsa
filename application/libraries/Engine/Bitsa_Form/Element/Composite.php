<?php
class Bitsa_Form_Element_Composite extends Zend_Form_Element_Xhtml implements Iterator,Countable
{
    protected $_attribs = array();
    protected $_decorators = array();
    protected $_description;
    protected $_disableLoadDefaultDecorators = false;
    protected $_elementOrder = array();
    protected $_elements = array();
    protected $_groupUpdated = false;
    protected $_loader;
    protected $_name;
    protected $_order;
    protected $_translator;
    protected $_translatorDisabled = false;
    protected $_view;
    public function init()
    {
    }

    public function setOptions(array $options)
    {
        $forbidden = array(
            'Options', 'Config', 'PluginLoader', 'View',
            'Translator', 'Attrib'
        );
        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);

            if (in_array($normalized, $forbidden)) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->setAttrib($key, $value);
            }
        }
        return $this;
    }

    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        return $this;
    }

    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();
        return $this->addAttribs($attribs);
    }

    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    public function getAttribs()
    {
        return $this->_attribs;
    }

    public function removeAttrib($key)
    {
        if (array_key_exists($key, $this->_attribs)) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    public function filterName($value)
    {
        return preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', '', (string) $value);
    }

    public function setName($name)
    {
        $name = $this->filtername($name);
        if (('0' !== $name) && empty($name)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getFullyQualifiedName()
    {
        return $this->getName();
    }

    public function getId()
    {
        if (isset($this->id)) {
            return $this->id;
        }

        $id = $this->getFullyQualifiedName();

        // Bail early if no array notation detected
        if (!strstr($id, '[')) {
            return $id;
        }

        // Strip array notation
        if ('[]' == substr($id, -2)) {
            $id = substr($id, 0, strlen($id) - 2);
        }
        $id = str_replace('][', '-', $id);
        $id = str_replace(array(']', '['), '-', $id);
        $id = trim($id, '-');

        return $id;
    }

    public function setLegend($legend)
    {
        return $this->setAttrib('legend', (string) $legend);
    }

    public function getLegend()
    {
        return $this->getAttrib('legend');
    }

    public function setDescription($value)
    {
        $this->_description = (string) $value;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setOrder($order)
    {
        $this->_order = (int) $order;
        return $this;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function addElement(Zend_Form_Element $element)
    {
      $this->_elements[$element->getName()] = $element;
      $this->_groupUpdated = true;
        return $this;
    }

    public function addElements(array $elements)
    {
        foreach ($elements as $element) {
            if (!$element instanceof Zend_Form_Element) {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Elements passed via array to addElements() must be Zend_Form_Elements only');
            }
            $this->addElement($element);
        }
        return $this;
    }

    public function setElements(array $elements)
    {
        $this->clearElements();
        return $this->addElements($elements);
    }
    
    public function getElement($name)
    {
        $name = (string) $name;
        if (isset($this->_elements[$name])) {
            return $this->_elements[$name];
        }

        return null;
    }

    public function getElements()
    {
        return $this->_elements;
    }

    public function removeElement($name)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_elements)) {
            unset($this->_elements[$name]);
            $this->_groupUpdated = true;
            return true;
        }

        return false;
    }

    public function clearElements()
    {
        $this->_elements = array();
        $this->_groupUpdated = true;
        return $this;
    }

    public function setPluginLoader(Zend_Loader_PluginLoader $loader)
    {
        $this->_loader = $loader;
        return $this;
    }

    public function getPluginLoader()
    {
        return $this->_loader;
    }

    public function addPrefixPath($prefix, $path)
    {
        $this->getPluginLoader()->addPrefixPath($prefix, $path);
        return $this;
    }

    public function addPrefixPaths(array $spec)
    {
        if (isset($spec['prefix']) && isset($spec['path'])) {
            return $this->addPrefixPath($spec['prefix'], $spec['path']);
        }
        foreach ($spec as $prefix => $paths) {
            if (is_numeric($prefix) && is_array($paths)) {
                $prefix = null;
                if (isset($paths['prefix']) && isset($paths['path'])) {
                    $this->addPrefixPath($paths['prefix'], $paths['path']);
                }
            } elseif (!is_numeric($prefix)) {
                if (is_string($paths)) {
                    $this->addPrefixPath($prefix, $paths);
                } elseif (is_array($paths)) {
                    foreach ($paths as $path) {
                        $this->addPrefixPath($prefix, $path);
                    }
                }
            }
        }
        return $this;
    }

    public function setDisableLoadDefaultDecorators($flag)
    {
        $this->_disableLoadDefaultDecorators = (bool) $flag;
        return $this;
    }

    public function loadDefaultDecoratorsIsDisabled()
    {
        return $this->_disableLoadDefaultDecorators;
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }
        
        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormComposite')
                 ->addDecorator('HtmlTag', array('tag' => 'dl'))
                 ->addDecorator('Fieldset')
                 ->addDecorator('DtDdWrapper');
        }
    }
    
    protected function _getDecorator($name, $options = null)
    {
        $class = $this->getPluginLoader()->load($name);
        if (null === $options) {
            $decorator = new $class;
        } else {
            $decorator = new $class($options);
        }

        return $decorator;
    }

    public function addDecorator($decorator, $options = null)
    {
        if ($decorator instanceof Zend_Form_Decorator_Interface) {
            $name = get_class($decorator);
        } elseif (is_string($decorator)) {
            $name      = $decorator;
            $decorator = array(
                'decorator' => $name,
                'options'   => $options,
            );
        } elseif (is_array($decorator)) {
            foreach ($decorator as $name => $spec) {
                break;
            }
            if (is_numeric($name)) {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid alias provided to addDecorator; must be alphanumeric string');
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
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid decorator provided to addDecorator; must be string or Zend_Form_Decorator_Interface');
        }

        $this->_decorators[$name] = $decorator;

        return $this;
    }

    public function addDecorators(array $decorators)
    {
        foreach ($decorators as $decoratorInfo) {
            if (is_string($decoratorInfo)) {
                $this->addDecorator($decoratorInfo);
            } elseif ($decoratorInfo instanceof Zend_Form_Decorator_Interface) {
                $this->addDecorator($decoratorInfo);
            } elseif (is_array($decoratorInfo)) {
                $argc    = count($decoratorInfo);
                $options = array();
                if (isset($decoratorInfo['decorator'])) {
                    $decorator = $decoratorInfo['decorator'];
                    if (isset($decoratorInfo['options'])) {
                        $options = $decoratorInfo['options'];
                    }
                    $this->addDecorator($decorator, $options);
                } else {
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
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid decorator passed to addDecorators()');
            }
        }

        return $this;
    }

    public function setDecorators(array $decorators)
    {
        $this->clearDecorators();
        return $this->addDecorators($decorators);
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
            if (is_array($value)) {
                $this->_loadDecorator($value, $key);
            }
        }
        return $this->_decorators;
    }

    public function removeDecorator($name)
    {
        $decorator = $this->getDecorator($name);
        if ($decorator) {
            if (array_key_exists($name, $this->_decorators)) {
                unset($this->_decorators[$name]);
            } else {
                $class = get_class($decorator);
                unset($this->_decorators[$class]);
            }
            return true;
        }

        return false;
    }

    public function clearDecorators()
    {
        $this->_decorators = array();
        return $this;
    }

    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    public function getView()
    {
        if (null === $this->_view) {
            require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }
        $content = '';
        foreach ($this->getDecorators() as $decorator) {
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
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }

    public function setTranslator($translator = null)
    {
        if ((null === $translator) || ($translator instanceof Zend_Translate_Adapter)) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        } else {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid translator specified');
        }
        return $this;
    }

    public function getTranslator()
    {
        if ($this->translatorIsDisabled()) {
            return null;
        }

        if (null === $this->_translator) {
            require_once 'Zend/Form.php';
            return Zend_Form::getDefaultTranslator();
        }

        return $this->_translator;
    }

    public function setDisableTranslator($flag)
    {
        $this->_translatorDisabled = (bool) $flag;
        return $this;
    }

    public function translatorIsDisabled()
    {
        return $this->_translatorDisabled;
    }

    public function __call($method, $args)
    {
        if ('render' == substr($method, 0, 6)) {
            $decoratorName = substr($method, 6);
            if (false !== ($decorator = $this->getDecorator($decoratorName))) {
                $decorator->setElement($this);
                $seed = '';
                if (0 < count($args)) {
                    $seed = array_shift($args);
                }
                return $decorator->render($seed);
            }

            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Decorator by name %s does not exist', $decoratorName));
        }

        require_once 'Zend/Form/Exception.php';
        throw new Zend_Form_Exception(sprintf('Method %s does not exist', $method));
    }

    public function current()
    {
        $this->_sort();
        current($this->_elementOrder);
        $key = key($this->_elementOrder);
        return $this->getElement($key);
    }

    public function key()
    {
        $this->_sort();
        return key($this->_elementOrder);
    }

    public function next()
    {
        $this->_sort();
        next($this->_elementOrder);
    }

    public function rewind()
    {
        $this->_sort();
        reset($this->_elementOrder);
    }

    public function valid()
    {
        $this->_sort();
        return (current($this->_elementOrder) !== false);
    }

    public function count()
    {
        return count($this->_elements);
    }

    protected function _sort()
    {
        if ($this->_groupUpdated || !is_array($this->_elementOrder)) {
            $elementOrder = array();
            foreach ($this->getElements() as $key => $element) {
                $elementOrder[$key] = $element->getOrder();
            }

            $items = array();
            $index = 0;
            foreach ($elementOrder as $key => $order) {
                if (null === $order) {
                    while (array_search($index, $elementOrder, true)) {
                        ++$index;
                    }
                    $items[$index] = $key;
                    ++$index;
                } else {
                    $items[$order] = $key;
                }
            }

            $items = array_flip($items);
            asort($items);
            $this->_elementOrder = $items;
            $this->_groupUpdated = false;
        }
    }

    protected function _loadDecorator(array $decorator, $name)
    {
        $sameName = false;
        if ($name == $decorator['decorator']) {
            $sameName = true;
        }

        $instance = $this->_getDecorator($decorator['decorator'], $decorator['options']);
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


    public function getElementsBelongTo()
    {
      return $this->_name;
    }
}