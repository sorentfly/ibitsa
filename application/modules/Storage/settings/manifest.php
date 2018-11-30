<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manifest.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'storage',
    'version' => '4.2.3',
    'revision' => '$Revision: 9747 $',
    'path' => 'application/modules/Storage',
    'repository' => 'socialengine.com',
    'title' => 'Storage',
    'description' => 'Storage',
    'author' => 'Webligo Developments',
    'changeLog' => 'settings/changelog.php',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '4.2.0',
      ),
    ),
    'tests' => array(
      array(
        'type' => 'MysqlEngine',
        'name' => 'MySQL MyISAM Storage Engine',
        'engine' => 'myisam',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       //'enable',
       //'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Storage/settings/install.php',
      'class' => 'Storage_Installer',
      'priority' => 5000,
    ),
    'directories' => array(
      'application/modules/Storage',
    ),
    'files' => array(
      'application/languages/en/storage.csv',
    ),
    'tests' => array(
      // FTP support
      array(
        'type' => 'PhpExtension',
        'name' => 'FTP',
        'extension' => 'ftp',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The FTP extension is recommend for CDNs that use FTP. An emulation layer will be used in the absence of this extension.',
        ),
      ),
      // SSH support
      array(
        'type' => 'PhpExtension',
        'name' => 'SSH2',
        'extension' => 'ssh2',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The SSH2 extension is required for CDNs that use SSH or SCP.',
        ),
      ),
      // HMAC support for S3
      array(
        'type' => 'Multi',
        'name' => 'Hash',
        'allForOne' => true,
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'allTestsFailed' => 'HMAC Encryption Support for Amazon S3 requires either the hash or mhash PHP extension.',
        ),
        'tests' => array(
          array(
            'type' => 'PhpExtension',
            'extension' => 'hash',
          ),
          array(
            'type' => 'PhpExtension',
            'extension' => 'mhash',
          ),
        ),
      ),
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onItemDeleteBefore',
      'resource' => 'Storage_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'storage_file',
  )
  // Routes --------------------------------------------------------------------
) ?>