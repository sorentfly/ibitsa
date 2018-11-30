<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
return array(
  array(
    'title' => 'Recent Messages',
    'description' => 'Displays a list of the signed in user\'s recent messages.',
    'category' => 'Messages',
    'type' => 'widget',
    'name' => 'messages.home-messages',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Recent Messages',
    ),
    'requirements' => array(
      'viewer',
    ),
  ),
  
  array(
    'title' => 'Messages Menu',
    'description' => 'Displays a menu in messages inbox, output, and compose pages.',
    'category' => 'Messages',
    'type' => 'widget',
    'name' => 'messages.menu',
  ),
) ?>