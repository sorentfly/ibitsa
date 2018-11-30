<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Cache
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ArrayContainer.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Cache
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Cache_ArrayContainer implements Iterator, ArrayAccess,
    SeekableIterator, /*Serializable,*/ Countable
{
  /**
   * @var Zend_Cache_Core
   */
  protected $_cache;

  /**
   * @var string
   */
  protected $_id;

  /**
   * @var array
   */
  protected $_keys;

  /**
   * @var array
   */
  protected $_data;

  /**
   * @var array
   */
  protected $_loaded;

  /**
   * @var boolean
   */
  protected $_caching = true;

  /**
   * @var boolean
   */
  protected $_persistent = true;

  /**
   * @var integer
   */
  protected $_position = 0;

  /**
   * @var callback
   */
  protected $_sortFunc;



  // Constructor

  public function __construct($data, Zend_Cache_Core $cache = null, $options = null)
  {
    $this->_cache = $cache;
    $this->_keys = array_keys($data);
    $this->_position = 0;

    if( is_array($options) ) {
      $this->setOptions($options);
    } else {
      $this->setOptions(array());
    }

    if( null === $this->_cache ) {
      $this->_caching = false;
    }

    if( empty($data) ) {
      $this->_data = array();
      $this->_loaded = array();
    } else if( !$this->_caching ) {
      $this->_data = $data;
      $this->_loaded = array_combine($this->_keys, array_fill(0, count($this->_keys), true));
    } else {
      $this->_data = array_combine($this->_keys, array_fill(0, count($this->_keys), null));
      $this->_loaded = array_combine($this->_keys, array_fill(0, count($this->_keys), false));

      // Save data to cache
      foreach( $data as $key => $value ) {
        $this->_cache->save($value, $this->_getCacheKey($key), array($this->_id));
        unset($value);
      }
    }
  }

  public function __destruct()
  {
    if( !$this->_persistent && $this->_caching && $this->_cache ) {
      $this->clean();
    }
  }

  public function setOptions(array $options)
  {
    if( isset($options['id']) ) {
      $this->_id = $options['id'];
    } else {
      $this->_id = md5(time() . join(' ', $this->_keys) . get_class($this) . mt_rand(0, time()));
    }

    if( isset($options['caching']) ) {
      $this->_caching = (bool) $options['caching'];
    }

    if( isset($options['persistent']) ) {
      $this->_persistent = (bool) $options['persistent'];
    }
    
    return $this;
  }



  // Introspection
  
  public function getId()
  {
    return $this->_id;
  }

  public function getArrayKeys()
  {
    return $this->_keys;
  }

  public function getArrayValues()
  {
    return array_values($this->getArrayCopy());
  }

  public function getArrayKeyExists($key)
  {
    return (bool) in_array($key, $this->_keys);
  }

  public function getLoadedKeys()
  {
    $loadedKeys = array();
    foreach( $this->_keys as $key ) {
      if( $this->_loaded[$key] ) {
        $loadedKeys[] = $key;
      }
    }
    return $loadedKeys;
  }

  public function getUnloadedKeys()
  {
    $loadedKeys = array();
    foreach( $this->_keys as $key ) {
      if( !$this->_loaded[$key] ) {
        $loadedKeys[] = $key;
      }
    }
    return $loadedKeys;
  }

  public function flush()
  {
    foreach( $this->_keys as $key ) {
      $this->__set($key, $this->_get($key));
    }
    return $this;
  }

  public function clean()
  {
    if( $this->_cache ) {
      $this->_cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_id));
    }
  }




  // Magic

  public function __sleep()
  {
    //if( get_class($this) == 'Engine_Cache_ArrayContainer' ) {
    //  throw new Engine_Exception('Does not support serialization without extension');
    //} else {
      return array('_id', '_keys', '_data', '_loaded', '_caching',
        '_persistent',
        '_cache' // Hmmm
      );
    //}
  }

  public function __wakeup()
  {
    
  }
  
  /**
   *
   * @param string $key
   * @return Engine_Package_Manager_Operation_Abstract
   */
  public function __get($key)
  {
    // Doesn't exist
    if( !in_array($key, $this->_keys) ) {
      return null;
    }

    // Exists in memory
    if( $this->_loaded[$key] ) {
      $value = $this->_data[$key];

      // Write to disk if caching
      if( $this->_caching ) {
        $this->_cache->save($value, $this->_getCacheKey($key), array($this->_id));
        $this->_data[$key] = null;
        $this->_loaded[$key] = false;
      }
    }

    // Exists on disk
    else {
      $value = $this->_cache->load($this->_getCacheKey($key));

      // Write to memory if not caching
      if( !$this->_caching ) {
        $this->_data[$key] = $value;
        $this->_loaded[$key] = true;
      }
    }

    return $value;
  }

  public function __set($key, $value)
  {
    if( !in_array($key, $this->_keys) ) {
      $this->_keys[] = $key;
    }

    if( !$this->_caching ) {
      $this->_data[$key] = $value;
      $this->_loaded[$key] = true;
    } else {
      $this->_data[$key] = null;
      $this->_loaded[$key] = false;
      $this->_cache->save($value, $this->_getCacheKey($key), array($this->_id));
    }
  }

  public function __isset($key)
  {
    return in_array($key, $this->_keys);
  }

  public function __unset($key)
  {
    if( $this->__isset($key) ) {
      $index = array_search($key, $this->_keys);
      unset($this->_keys[$index]);
      unset($this->_data[$key]);
      unset($this->_loaded[$key]);
      $this->_keys = array_values($this->_keys);
    }

    $this->_position = 0; // Resets position
  }
  


  // Interface: ArrayAccess

  public function offsetExists($offset)
  {
    return $this->__isset($offset);
  }
  
  public function offsetGet($offset)
  {
    return $this->__get($offset);
  }

  public function offsetSet($offset, $value)
  {
    $this->__set($offset, $value);
  }

  public function offsetUnset($offset)
  {
    $this->__unset($offset);
  }


  
  // Interface: Countable

  public function count()
  {
    return count($this->_keys);
  }


  
  // Interface: Iterator

  public function current()
  {
    $key = $this->key();
    if( null === $key ) {
      return false;
    } else {
      return $this->__get($key);
    }
  }

  public function key()
  {
    if( isset($this->_keys[$this->_position]) ) {
      return $this->_keys[$this->_position];
    } else {
      return null;
    }
  }

  public function next()
  {
    $this->_position++;
  }

  public function rewind()
  {
    $this->_position = 0;
  }

  public function valid()
  {
    return isset($this->_keys[$this->_position]);
  }


  
  // Interface: SeekableIterator

  public function seek($position)
  {
    $this->_position = $position;
    
    if( !$this->valid() ) {
      throw new OutOfBoundsException("invalid seek position ($position)");
    }
  }



  // Pseudo-Interface: ArrayIterator

  public function append($value, $key = null)
  {
    if( null === $key ) {
      if( count($this->_keys) == 0 ) {
        $key = 0;
      } else {
        $key = max($this->_keys) + 1;
      }
    }

    //$this->_keys[] = $key; // Default is to append

    //$this->_position = 0; // Resets position. Edit: not for push?

    $this->__set($key, $value);
  }

  public function asort()
  {
    // Note: this will use a crap load of CPU and disk IO
    $this->_sortFunc = 'strcmp';
    usort($this->_keys, array($this, '_sortValue'));
  }

  public function getArrayCopy()
  {
    $array = array();
    foreach( $this as $key => $value ) {
      $array[$key] = $value;
    }
    return $array;
  }

  public function getFlags()
  {
    return null;
  }

  public function ksort()
  {
    sort($this->_keys);
  }

  public function natcasesort()
  {
    // Note: this will use a crap load of CPU and disk IO
    $this->_sortFunc = 'strnatcasecmp';
    usort($this->_keys, array($this, '_sortValue'));
  }

  public function natsort()
  {
    // Note: this will use a crap load of CPU and disk IO
    $this->_sortFunc = 'strnatcmp';
    usort($this->_keys, array($this, '_sortValue'));
  }

  public function prepend($value, $key = null)
  {
    if( null === $key ) {
      if( count($this->_keys) == 0 ) {
        $key = 0;
      } else {
        $key = min($this->_keys) - 1;
        if( $key > 0 ) {
          $key = 0;
        }
      }
    }

    $this->_keys = array_merge(array($key), $this->_keys);

    $this->_position = 0; // Resets position

    $this->__set($key, $value);
  }

  public function setFlags()
  {
    return null;
  }

  public function uasort($cmp_function)
  {
    $this->_sortFunc = $cmp_function;
    usort($this->_keys, array($this, '_sortValue'));
  }

  public function uksort($cmp_function)
  {
    $this->_sortFunc = $cmp_function;
    usort($this->_keys, array($this, '_sortKey'));
  }

  public function tell()
  {
    return $this->_position;
  }


  
  // Utility

  protected function _getCacheKey($key)
  {
    if( is_int($key) ) $key = sprintf('%d', $key);
    $key = $this->_id . '_' . $key;
    $key = preg_replace('/[^a-z0-9]+/i', '_', $key);
    return $key;
  }
  
  protected function _sortKey($a, $b)
  {
    $func = $this->_sortFunc;
    return call_user_func($func, $a, $b);
  }

  protected function _sortValue($a, $b)
  {
    $func = $this->_sortFunc;
    return call_user_func($func, $this->__get($a), $this->__get($b));
  }
}