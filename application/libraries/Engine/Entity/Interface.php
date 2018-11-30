<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Entity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Interface.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Entity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
interface Engine_Entity_Interface
{
  /**
   * Get the type
   * 
   * @return string
   */
  public function getType();

  /**
   * Get the id
   *
   * @return integer
   */
  public function getIdentity();

  /**
   * Get the guid (type_id)
   *
   * @return string
   */
  public function getGuid();

  /**
   * Get the title
   *
   * @return string
   */
  public function getTitle();

  /**
   * Get the description
   *
   * @return string
   */
  public function getDescription();

  /**
   * Get the keywords
   *
   * @return array
   */
  public function getKeywords();

  /**
   * Get a uri
   * 
   * @param array $params (OPTIONAL)
   * @return string
   */
  public function getHref(array $params = array());

  /**
   * Get the creation date
   *
   * @return integer
   */
  public function getCreationDate();

  /**
   * Get the modification date
   *
   * @return integer
   */
  public function getModifiedDate();

  /**
   * Get a uri to the photo
   * 
   * @param string $type The thumbnail identifier
   */
  public function getPhotoHref($type = null);

  /**
   * Should this be indexed in search?
   *
   * @return boolean
   */
  public function isSearchable();
}