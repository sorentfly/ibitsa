<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Forgot.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Forgot extends Engine_Db_Table
{
  public function gc()
  {
    // Delete everything older than <del>6</del> 24 hours
    $this->delete(array(
      'creation_date < ?' => date('Y-m-d H:i:s', time() - (3600 * 24)),
    ));
  }
}