<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ControllerPrefix.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Controller
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Controller_Router_Route_ControllerPrefix extends Zend_Controller_Router_Route_Module
{
  // Constants
  
  const PREFIX_SEPARATOR = '-';

  // Properties

  /**
   * The prefix to use
   * 
   * @var string
   */
  protected $_prefix = 'admin';

  /**
   * The prefix to show in routing
   *
   * @var string
   */
  protected $_actualPrefix = 'admin';

  /**
   * Get an instance of self for easy configuration
   * 
   * @param Zend_Config $config
   * @return self
   */
  public static function getInstance(Zend_Config $config)
  {
    $frontController = Zend_Controller_Front::getInstance();

    $defs       = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
    $dispatcher = $frontController->getDispatcher();
    $request    = $frontController->getRequest();

    return new self($defs, $dispatcher, $request);
  }

  /**
   * Match url agains this route
   * 
   * @param string $path
   * @param bool $partial
   * @return array
   */
  public function match($path, $partial = false)
  {
    $this->_setRequestKeys();

    $values = array();
    $params = array();

    if (!$partial) {
        $path = trim($path, self::URI_DELIMITER);
    } else {
        $matchedPath = $path;
    }

    if ($path != '') {
        $path = explode(self::URI_DELIMITER, $path);

        // Check prefix
        $checkPrefix = array_shift($path);
        if( $checkPrefix != $this->_prefix ) {
          return false;
        }

        if (count($path) && !empty($path[0]) && $this->_dispatcher && $this->_dispatcher->isValidModule($path[0])) {
          $values[$this->_moduleKey] = array_shift($path);
          $this->_moduleValid = true;
        }

        if (count($path) && !empty($path[0])) {
            $values[$this->_controllerKey] = array_shift($path);
        }

        if (count($path) && !empty($path[0])) {
            $values[$this->_actionKey] = array_shift($path);
        }

        if ($numSegs = count($path)) {
            for ($i = 0; $i < $numSegs; $i = $i + 2) {
                $key = urldecode($path[$i]);
                $val = isset($path[$i + 1]) ? urldecode($path[$i + 1]) : null;
                $params[$key] = (isset($params[$key]) ? (array_merge((array) $params[$key], array($val))): $val);
            }
        }
    }

    if ($partial) {
        $this->setMatchedPath($matchedPath);
    }

    $this->_values = $values + $params;

    $vals = $this->_values + $this->_defaults;
    $vals[$this->_controllerKey] = $this->_actualPrefix . self::PREFIX_SEPARATOR . $vals[$this->_controllerKey];
    return $vals;
  }

  /**
   * Assemble a url for this route
   * 
   * @param array $data
   * @param bool $reset
   * @param bool $encode
   * @param bool $partial
   * @return string
   */
  public function assemble($data = array(), $reset = false, $encode = true, $partial = false)
  {
    return $this->_prefix . self::URI_DELIMITER . parent::assemble($data, $reset, $encode, $partial);
  }
}