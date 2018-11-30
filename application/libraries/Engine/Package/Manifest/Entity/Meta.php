<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Meta.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Entity_Meta extends Engine_Package_Manifest_Entity_Abstract
{
  protected $_date;

  protected $_title;

  protected $_description;

  protected $_developer;

  protected $_authors;

  protected $_changeLog;

  protected $_props = array(
    'date',
    'title',
    'description',
    'developer',
    'authors',
    'changeLog',
  );

  public function __construct($spec)
  {
    if( is_array($spec) ) {
      $this->setOptions($spec);
    }
  }

  public function getDate()
  {
    // Initialize to now?
    if( null === $this->_date ) {
      $this->setDate();
    }
    return $this->_date;
  }

  public function setDate($datetime = null)
  {
    if( null === $datetime ) {
      $datetime = time();
    }
    if( is_string($datetime) ) {
      $datetime = strtotime($datetime);
    }
    if( $datetime instanceof Zend_Date ) {
      $datetime = $datetime->toValue();
    }
    if( is_numeric($datetime) ) {
      $this->_date = date('r', $datetime);
    }
    return $this;
  }

  public function getTitle()
  {
    return $this->_title;
  }

  public function setTitle($title)
  {
    $this->_title = (string) $title;
    return $this;
  }

  public function getDescription()
  {
    return $this->_description;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
    return $this;
  }

  public function getDeveloper()
  {
    return $this->_developer;
  }
  
  public function setDeveloper($developer)
  {
    $this->_developer = $developer;
    return $this;
  }

  public function addAuthor($author)
  {
    if( !in_array($author, (array) $this->_authors) ) {
      $this->_authors[] = (string) $author;
    }
    return $this;
  }

  public function addAuthors(array $authors = null)
  {
    foreach( (array) $authors as $author ) {
      $this->addAuthor($author);
    }
    return $this;
  }

  public function clearAuthors()
  {
    $this->_authors = array();
    return $this;
  }

  public function getAuthors()
  {
    return $this->_authors;
  }

  public function setAuthor($author)
  {
    $this->addAuthor($author);
    return $this;
  }

  public function setAuthors(array $authors = null)
  {
    $this->clearAuthors()
      ->addAuthors($authors);
    return $this;
  }

  public function getChangeLog()
  {
    return $this->_changeLog;
  }

  public function setChangeLog($changeLog)
  {
    $this->_changeLog = $changeLog;
    return $this;
  }
}