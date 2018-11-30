<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Module.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Installer_Module extends Engine_Package_Installer_Abstract
{
  protected $_scripts;

  protected $_databaseOperationType;

  protected $_currentVersion;

  protected $_targetVersion;

  protected $_selectedScripts;

  public function onPreInstall()
  {
    $this->_targetVersion = $this->_getVersionTarget();
    $this->_currentVersion = $this->_getVersionDatabase();

    // Get operation type
    $operationType = $this->getOperation()->getOperationType();
    $dbOperationType = null;
    switch( true ) {
      case ( (null !== $this->_currentVersion || null === $this->_targetVersion) && $dbOperationType == 'remove' ):
        $dbOperationType = 'remove';
        break;
      case ( null === $this->_targetVersion ):
        throw new Engine_Package_Exception('Missing version info');
        break;
      case ( null === $this->_currentVersion ):
        $dbOperationType = 'install';
        break;
      case ( version_compare($this->_targetVersion, $this->_currentVersion, '=') ):
        $dbOperationType = 'ignore';
        break;
      case ( version_compare($this->_targetVersion, $this->_currentVersion, '>') ):
        $dbOperationType = 'upgrade';
        break;
      case ( version_compare($this->_targetVersion, $this->_currentVersion, '<') ):
        $dbOperationType = 'downgrade';
        break;
      default:
        throw new Engine_Package_Exception('Unable to find database operation resolution.');
        break;
    }
    $this->_databaseOperationType = $dbOperationType;
    
    // Select scripts
    $scripts = $this->_listSqlScripts();
    if( empty($scripts[$this->_databaseOperationType]) ) {
      return;
    }
    $scripts = $scripts[$this->_databaseOperationType];

    
    // This is a type that requires resolution
    if( in_array($dbOperationType, array('upgrade', 'downgrade')) ) {
      try {
        $scripts = $this->_resolveOperationMap($scripts, $this->_currentVersion, $this->_targetVersion);
      } catch( Exception $e ) {
        return $this->_error($e->getMessage());
      }
    }
    // This is a simple type
    else {
      if( !isset($scripts[$this->_targetVersion]) ) {
        return $this->_error(sprintf('No database script for action %s to version %s', $this->_databaseOperationType, $this->_targetVersion));
      }
      $scripts = array($scripts[$this->_targetVersion]);
    }
    $this->_selectedScripts = $scripts;
  }
  
  public function onInstall()
  {
    $package = $this->_operation->getPrimaryPackage();
    $db = $this->getDb();
    $successCount = 0;
    $errors = array();
    $currentDbVersion = $this->_currentVersion;
    
    // Run selected scripts
    if( !empty($this->_selectedScripts) ) {
      foreach( $this->_selectedScripts as $selectedScript ) {
        $contents = file_get_contents($selectedScript['path']);
        foreach( Engine_Package_Utilities::sqlSplit($contents) as $sqlFragment ) {
          try {
            $db->query($sqlFragment);
            $successCount++;
          } catch( Exception $e ) {
            return $this->_error('Query failed with error: ' . $e->getMessage());
          }
        }
        // Update version for this upgrade
        $currentDbVersion = (isset($selectedScript['version2']) ? $selectedScript['version2'] : (isset($selectedScript['version']) ? $selectedScript['version'] : null) );
        if( $currentDbVersion ) {
          try {
            $count = $db->update('engine4_core_modules', array(
              'version' => $currentDbVersion,
            ), array(
              'name = ?' => $package->getName(),
            ));
            if( $count <= 0 ) {
              try {
                $db->insert('engine4_core_modules', array(
                  'name' => $package->getName(),
                  'version' => $currentDbVersion,
                  'title' => $package->getTitle(),
                  'description' => $package->getDescription(),
                  'enabled' => 1,
                ));
              } catch( Exception $e ) {}
            }
          } catch( Exception $e ) {
            // Silence?
          }
        }
      }
    }

    // Run custom
    if( method_exists($this, '_runCustomQueries') ) {
      try {
        $r = $this->_runCustomQueries();
        if( is_int($r) ) {
          $successCount += $r;
        } else {
          $successCount++;
        }
      } catch( Exception $e ) {
        return $this->_error('Query failed with error: ' . $e->getMessage());
      }
    }

    // Update version
    if( !$this->hasError() ) {
      if( !$package ) {
        $package = $this->getOperation()->getPrimaryPackage();
      }
      if( $package ) {
        $updateData = array(
          'version' => $this->_targetVersion,
          'title' => $package->getTitle(),
          'description' => $package->getDescription(),
        );
      } else {
        $updateData = array(
          'version' => $this->_targetVersion,
        );
      }
      $count = $this->getDb()->update('engine4_core_modules', $updateData, array(
        'name = ?' => $package->getName(),
        //'version = ?' => $this->_currentVersion,
      ));
      if( $count <= 0 ) {
        try {
          $db->insert('engine4_core_modules', array(
            'name' => $package->getName(),
            'version' => $package->getVersion(),
            'title' => $package->getTitle(),
            'description' => $package->getDescription(),
            'enabled' => 1,
          ));
        } catch( Exception $e ) {}
      }
    }

    // Log success messages
    $this->_message(sprintf('%1$d queries succeeded.', $successCount));

    if( !$this->_currentVersion ) {
      $this->_message(sprintf('%1$s installed.', $this->_targetVersion));
    } else if( !$this->_targetVersion ) {
      $this->_message(sprintf('%1$s removed.', $this->_currentVersion));
    } else {
      $this->_message(sprintf('%1$s to %2$s applied.', $this->_currentVersion, $this->_targetVersion));
    }

    $this->_writeEnabledModulesFile();
    return $this;
  }

  public function onEnable()
  {
    $db = $this->getDb();
    
    $db->update('engine4_core_modules', array(
      'enabled' => 1,
    ), array(
      'name = ?' => $this->getOperation()->getPrimaryPackage()->getName(),
    ));

    $this->_writeEnabledModulesFile();
    return $this;
  }

  public function onDisable()
  {
    $db = $this->getDb();

    $db->update('engine4_core_modules', array(
      'enabled' => 0,
    ), array(
      'name = ?' => $this->getOperation()->getPrimaryPackage()->getName(),
    ));

    $this->_writeEnabledModulesFile();
    return $this;
  }

  
  // Utility

  protected function _getVersionDatabase()
  {
    $info = $this->_getInfoDatabase();
    if( null === $info ) {
      return null;
    } else {
      return $info['version'];
    }
  }

  protected function _getInfoDatabase()
  {
    try {
      $select = new Zend_Db_Select($this->getDb());
      $select
        ->from('engine4_core_modules')
        ->where('name = ?', $this->_name)
        ->limit(1)
        ;
      $row = $select->query()->fetch();
    } catch( Exception $e ) {
      $row = null;
    }

    return $row;
  }

  protected function _listSqlScripts()
  {
    if( null === $this->_scripts ) {

      $path = $this->_operation->getPrimaryPackage()->getBasePath() . '/'
            . $this->_operation->getPrimaryPackage()->getPath() . '/'
            . 'settings';

      $files = @scandir($path);
      if (!empty($files)) {
        foreach( $files as $file ) {
          if( strtolower(substr($file, -4)) !== '.sql' ) {
            continue;
          }

          $baseName = substr($file, 0, -4);
          $parts = explode('-', $baseName);

          // Install (backwards compatibility
          if( count($parts) === 1 ) {
            $this->_scripts['install'][$this->_getVersionTarget()] = array(
              'path' => $path . '/' . $file,
              'adapter' => $parts[0],
              'version' => $this->_getVersionTarget(),
            );

          // Remove (backwards compatibility)
          } else if( count($parts) === 2 && $parts[1] === 'remove' ) {
            $this->_scripts['remove'][$this->_getVersionTarget()] = array(
              'path' => $path . '/' . $file,
              'adapter' => $parts[0],
              'version' => $this->_getVersionTarget(),
            );

          // Install
          } else if( count($parts) === 3 && $parts[1] === 'install' ) {
            $this->_scripts['install'][$parts[2]] = array(
              'path' => $path . '/' . $file,
              'adapter' => $parts[0],
              'version' => $parts[2],
            );

          // Remove
          } else if( count($parts) === 3 && $parts[1] === 'remove' ) {
            $this->_scripts['remove'][$parts[2]] = array(
              'path' => $path . '/' . $file,
              'adapter' => $parts[0],
              'version' => $parts[2],
            );

          // Upgrade
          } else if( count($parts) === 4 && $parts[1] === 'upgrade' ) {
            $this->_scripts['upgrade'][] = array(
              'path' => $path . '/' . $file,
              'adapter' => $parts[0],
              'version1' => $parts[2],
              'version2' => $parts[3],
            );

          // Downgrade
          } else if( count($parts) === 4 && $parts[1] === 'downgrade' ) {
            $this->_scripts['downgrade'][] = array(
              'path' => $path . '/' . $file,
              'adapter' => $parts[0],
              'version1' => $parts[2],
              'version2' => $parts[3],
            );
          } else {
            // wth?
            continue;
          }
        }
      }
    }

    return $this->_scripts;
  }

  protected function _resolveOperationMap($scripts, $startVersion, $endVersion)
  {
    $leftMap = array();
    $rightMap = array();

    // Build left/right maps
    foreach( $scripts as $index => $script ) {
      $leftMap[$index] = $script['version1'];
      $rightMap[$index] = $script['version2'];
    }

    $results = self::resolveSegments($leftMap, $rightMap, $startVersion, $endVersion);
    if( !$results ) {
      $results = self::resolveVersionSegments($leftMap, $rightMap, $startVersion, $endVersion);
      if( !$results ) {
        $results = array();
        //throw new Engine_Package_Installer_Exception(sprintf('Unable to resolve database upgrade path from version %s to version %s', $startVersion, $endVersion));
      }
    }

    // Resolve to scripts
    $resultScripts = array();
    foreach( $results as $resultIndex ) {
      $resultScripts[] = $scripts[$resultIndex];
    }

    return $resultScripts;
  }
  
  protected function _addGenericPage($page, $title, $displayname, $description)
  {
    $db = $this->getDb();
    
    // check page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', $page)
      ->limit(1)
      ->query()
      ->fetchColumn();
    
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => $page,
        'displayname' => $displayname,
        'title' => $title,
        'description' => $description,
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
      ));
      $middle_id = $db->lastInsertId();
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $middle_id,
      ));
    }
    
    return $page_id;
  }

  protected function _writeEnabledModulesFile()
  {
    $db = $this->getDb();
    // get enabled modules list
    $enabledModuleNames = $db->select()
      ->from('engine4_core_modules', 'name')
      ->where('enabled = ?', 1)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);
    $enabled_modules_file = APPLICATION_PATH . '/application/settings/enabled_module_directories.php';
    if( (is_file($enabled_modules_file) && is_writable($enabled_modules_file)) ||
        (is_dir(dirname($enabled_modules_file)) && is_writable(dirname($enabled_modules_file))) ) {

      foreach( $enabledModuleNames as $module ) {
        $modulesInflected[] = Engine_Api::inflect($module);
      }

      $file_contents = "<?php defined('_ENGINE') or die('Access Denied'); return ";
      $file_contents .= var_export($modulesInflected, true);
      $file_contents .= "; ?>";
      file_put_contents($enabled_modules_file, $file_contents);
      chmod($enabled_modules_file, 0777);
    }
  }

  static public function resolveVersionSegments(array $left, array $right, $start, $end, array $ignore = array())
  {
    // Do this to track recursion level
    static $ext;
    if( $ext === null ) {
      $ext = 1;
    } else {
      $ext++;
    }
    
    if( count($left) != count($right) ) {
      $ext = null;
      throw new Exception('Right count != left count');
    }

    $solutions = array();
    foreach( $left as $index => $leftValue ) {
      // Ignore parent indexes
      if( in_array($index, $ignore) ) continue;
      
      $rightValue = $right[$index];
      $childIgnore = array_merge($ignore, array($index));

      if( version_compare($leftValue, $start, '>=') && version_compare($rightValue, $end, '<=') ) {
        // Add this as solution
        $solutions[] = array($index);
        // Get child solutions
        $childSolutions = self::resolveVersionSegments($left, $right, $rightValue, $end, $childIgnore);
        if( is_array($childSolutions) && !empty($childSolutions) ) {
          foreach( $childSolutions as $childSolution ) {
            array_unshift($childSolution, $index);
            $solutions[] = $childSolution;
          }
        }
      }
    }

    // Decrement recursion level?
    $ext--;

    // If being called internally, just return
    if( $ext > 0 ) {
      return $solutions;
    }

    // Otherwise, find the solution with the largest span
    else
    {
      $ext = null;
      
      $currentSolution = null;
      $minVersion = false;
      $maxVersion = false;
      foreach( $solutions as $i => $solution ) {
        $solutionStart = $left[$solution[0]];
        $solutionEnd = $right[$solution[count($solution)-1]];

        if( null === $currentSolution ) {
          $minVersion = $solutionStart;
          $maxVersion = $solutionEnd;
          $currentSolution = $solution;
          continue;
        }

        $startCmp = version_compare($solutionStart, $minVersion);
        $endCmp = version_compare($solutionEnd, $maxVersion);

        // Ignore if start > min || end < max
        if( $startCmp == 1 || $endCmp == -1 ) {
          continue;
        }

        // If the start/end are equal, skip if count is less
        if( $startCmp === 0 && $endCmp === 0 && count($solution) < count($currentSolution) ) {
          continue;
        }

        // Otherwise this is the new solution
        $minVersion = $solutionStart;
        $maxVersion = $solutionEnd;
        $currentSolution = $solution;
      }
      return $currentSolution;
      //return $solutions;
    }
  }

  static public function resolveSegments(array $left, array $right, $start, $end, array $ignore = array())
  {
    if( count($left) != count($right) ) {
      throw new Exception('Right count != left count');
    }
    
    $solutions = array();
    foreach( $left as $index => $leftValue ) {
      $rightValue = $right[$index];
      
      // Ignore parent indexes
      if( in_array($index, $ignore) ) continue;

      // Ignore if left value is not start
      if( $leftValue != $start ) continue;

      if( $rightValue == $end ) {
        $solutions[] = array($index);
      } else {
        $childIgnore = array_merge($ignore, array($index));
        $childSolution = self::resolveSegments($left, $right, $rightValue, $end, $childIgnore);
        if( is_array($childSolution) ) {
          array_unshift($childSolution, $index);
          $solutions[] = $childSolution;
        }
        /*
        $childSolutions = self::resolveSegments($left, $right, $rightValue, $end, $childIgnore);
        if( is_array($childSolutions) ) {
          foreach( $childSolutions as $index => $childSolution ) {
            array_unshift($childSolution, $index);
            $solutions[] = $childSolution;
          }
        }
        */
      }
    }
    
    if( count($solutions) == 0 ) {
      return false;
    } else if( count($solutions) == 1 ) {
      return array_shift($solutions);
      //return $solutions;
    } else {
      $minIndex = null;
      $minCount = null;
      foreach( $solutions as $index => $solution ) {
        if( null === $minCount || count($solution) < $minCount ) {
          $minIndex = $index;
          $minCount = count($solution);
        }
      }
      return $solutions[$minIndex];
    }
  }
}