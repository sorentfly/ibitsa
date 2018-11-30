<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Package_Manager_Operation_Abstract
{
  /**
   * @var Engine_Package_Manager
   */
  protected $_manager;

  /**
   * The target package (the one being installed)
   * @var Engine_Package_Manifest
   */
  protected $_targetPackage;

  /**
   * The original package (the one being replaced)
   * @var Engine_Package_Manifest
   */
  protected $_currentPackage;

  /**
   * Generally, APPLICATION_PATH . '/temporary/package/packages/' . $packageKey
   * @var string
   */
  protected $_sourcePath;

  /**
   * Generally, APPLICATION_PATH
   * @var string
   */
  protected $_destinationPath;

  /**
   *
   * @var Engine_File_Diff_Batch
   */
  protected $_diff;

  /**
   * List of file operations to perform
   * 
   * @var array
   */
  protected $_fileOperations;

  /**
   * Summary of diff operation
   *
   * @var array
   */
  protected $_diffSummary;

  // Deprecated, backwards compatibility
  protected $_package;
  protected $_previousPackage;
  protected $_previousPackageSource;





  // General
  
  public function __construct(Engine_Package_Manager $manager,
      Engine_Package_Manifest $targetPackage, $currentPackage = null, $options = null)
  {
    $this->_manager = $manager;

    if( is_array($currentPackage) ) {
      $options = $currentPackage;
    }
    if( !($currentPackage instanceof Engine_Package_Manifest) ) {
      $currentPackage = null;
    }
    if( is_array($options) ) {
      $this->setOptions($options);
    }

    $this->_setPackages($targetPackage, $currentPackage);

    if( null === $this->_destinationPath ) {
      /* if( null !== $this->_currentPackage ) {
        $this->_destinationPath = $this->_currentPackage->getSourcePath();
      } else */ if( defined('APPLICATION_PATH') ) {
        $this->_destinationPath = APPLICATION_PATH;
      }
    }
    if( null === $this->_sourcePath ) {
      $this->_sourcePath = $this->getPrimaryPackage()->getSourcePath();
    }
  }

  protected function _setPackages(Engine_Package_Manifest $targetPackage,
      Engine_Package_Manifest $currentPackage = null)
  {
    $this->_targetPackage = $targetPackage;
    $this->_currentPackage = $currentPackage;
  }

  public function __sleep()
  {
    return array('_sourcePath', '_destinationPath', '_targetPackage',
      '_currentPackage', '_fileOperations', '_diffSummary'
      /* , '_diff' */);
  }

  public function __wakeup()
  {
    // Backwards compatibility
    if( null !== $this->_package &&
        null === $this->_targetPackage &&
        null === $this->_currentPackage ) {
      $this->_setPackages($this->_package, $this->_previousPackage);
    }
    $this->_previousPackageSource = null;

    /*
    $this->_package = new Engine_Package_Manifest($this->_sourcePath);
    if( null !== $this->_previousPackageSource ) {
      $this->_previousPackage = new Engine_Package_Manifest($this->_previousPackageSource);
    }
    */
  }

  public function getOperationType()
  {
    return strtolower(ltrim(strrchr(get_class($this), '_'), '_'));
  }

  public function getKey()
  {
    if( null !== $this->_targetPackage ) {
      return $this->_targetPackage->getKey();
    } else {
      return $this->_currentPackage->getKey();
    }
  }

  public function getGuid()
  {
    if( null !== $this->_targetPackage ) {
      return $this->_targetPackage->getGuid();
    } else {
      return $this->_currentPackage->getGuid();
    }
  }



  // Manager

  public function getManager()
  {
    if( null === $this->_manager ) {
      throw new Engine_Package_Manager_Operation_Exception('No manager defined');
    }
    return $this->_manager;
  }

  public function setManager(Engine_Package_Manager $manager)
  {
    //if( null !== $this->_manager ) {
    //  throw new Engine_Package_Manager_Operation_Exception('Manager already defined');
    //}
    $this->_manager = $manager;
    return $this;
  }



  // Package

  public function getTargetPackage()
  {
    return $this->_targetPackage;
  }

  public function getCurrentPackage()
  {
    return $this->_currentPackage;
  }

  public function getPrimaryPackage()
  {
    if( null !== $this->_targetPackage ) {
      return $this->_targetPackage;
    } else if( null !== $this->_currentPackage ) {
      return $this->_currentPackage;
    } else {
      throw new Engine_Package_Manager_Operation_Exception('No primary package');
    }
  }
  
  // START DEPRECATED
  public function getPackage()
  {
    trigger_error('Deprecated', defined('E_USER_DEPRECATED') ? constant('E_USER_DEPRECATED') : E_USER_NOTICE);
    return $this->getTargetPackage();
  }

  public function getPreviousPackage()
  {
    trigger_error('Deprecated', defined('E_USER_DEPRECATED') ? constant('E_USER_DEPRECATED') : E_USER_NOTICE);
    return $this->getCurrentPackage();
  }

  public function getSourcePackage()
  {
    trigger_error('Deprecated', defined('E_USER_DEPRECATED') ? constant('E_USER_DEPRECATED') : E_USER_NOTICE);
    return $this->getCurrentPackage();
  }

  public function getResultantPackage()
  {
    trigger_error('Deprecated', defined('E_USER_DEPRECATED') ? constant('E_USER_DEPRECATED') : E_USER_NOTICE);
    return $this->getTargetPackage();
  }

  public function getPackageGuid()
  {
    trigger_error('Deprecated', defined('E_USER_DEPRECATED') ? constant('E_USER_DEPRECATED') : E_USER_NOTICE);
    return $this->getGuid();
  }
  // END DEPRECATED



  // Options
  
  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }

  public function setSourcePath($path)
  {
    $this->_sourcePath = $path;
    return $this;
  }

  public function getSourcePath()
  {
    if( null === $this->_sourcePath ) {
      throw new Engine_Package_Manager_Operation_Exception('No source path defined');
    }
    return $this->_sourcePath;
  }

  public function setDestinationPath($path)
  {
    $this->_destinationPath = $path;
    return $this;
  }

  public function getDestinationPath()
  {
    if( null === $this->_destinationPath ) {
      throw new Engine_Package_Manager_Operation_Exception('No destination path defined');
    }
    return $this->_destinationPath;
  }



  // Dependencies



  // Tests

  public function getTests()
  {
    // No resultant package
    $targetPackage = $this->getTargetPackage();
    if( !$targetPackage ) {
      return false;
    }

    // No tests
    $tests = $targetPackage->getTests();
    if( empty($tests) ) {
      return false;
    }

    // Make battery
    $battery = new Engine_Sanity(array(
      'name' => $targetPackage->getKey(),
    ));
    foreach( $tests as $test ) {
      $battery->addTest($test->toArray());
    }

    return $battery;
  }



  // File Ops

  public function getFileOperations($skipErrors = false, $showAll = false)
  {
    if( null === $this->_fileOperations ) {
      $this->_fileOperations = $this->_buildFileOperations($showAll);
    }

    // Remove error types?
    if( $skipErrors ) {
      $errorTypes = array('different', 'impossible', 'different_removed',
        'different_different', 'added_added');
      $fileOperations = array();
      foreach( $this->_fileOperations as $path => $code ) {
        // Skip diff errors if selected
        if( $skipErrors && in_array($code, $errorTypes) ) {
          continue;
        }
        $fileOperations[$path] = $code;
      }
      return $fileOperations;
    }

    return $this->_fileOperations;
  }

  protected function _buildFileOperations($showAll = false)
  {
    $rightFiles = $this->getDiffTargetFiles();
    $originalFiles = $this->getDiffCurrentFiles();
    //$leftFiles = $this->getDiffMasterFiles();
    $leftFiles = array_unique(array_merge(
      array_keys($originalFiles),
      array_keys($rightFiles)
    ));

    sort($leftFiles);

    $leftPath = $this->getManager()->getBasePath();
    $rightPath = null !== $this->getTargetPackage() ? $this->getTargetPackage()->getBasePath() : null;
    $originalPath = null !== $this->getCurrentPackage() ? $this->getCurrentPackage()->getBasePath() : null;

    $hasErrors = false;
    $fileOperations = array();
    foreach( $leftFiles as $file ) {
      
      // Format left
      $left = $file;

      // Format right
      $right = null;
      if( isset($rightFiles[$file]) ) {
        if( isset($rightFiles[$file]['dir']) && $rightFiles[$file]['dir'] ) continue;
        $right = $rightFiles[$file];
      }

      // Format original
      $original = null;
      if( !empty($originalFiles) && isset($originalFiles[$file]) ) {
        if( isset($originalFiles[$file]['dir']) && $originalFiles[$file]['dir'] ) continue;
        $original = $originalFiles[$file];
      }

      $leftFormatted = $this->_formatFileData($left, $leftPath, $file);
      $rightFormatted = $this->_formatFileData($right, $rightPath, $file);
      $originalFormatted = null;
      if( !empty($originalFiles) ) {
        $originalFormatted = $this->_formatFileData($original, $originalPath, $file);
      }

      // Diff
      if( null === $originalFormatted ) {
        $diffResult = Engine_File_Diff::compare($leftFormatted, $rightFormatted);
      } else {
        $diffResult = Engine_File_Diff3::compare($leftFormatted, $rightFormatted, $originalFormatted);
      }

      // Show all?
      if( !$showAll &&
          ($diffResult == Engine_File_Diff::IDENTICAL ||
          $diffResult == Engine_File_Diff::IGNORE) ) {
        continue;
      }

      // Err
      $hasError = ( null !== Engine_File_Diff::getErrorCodeKey($diffResult) );
      $hasErrors = $hasErrors || $hasError;

      $diffInfo = array(
        'error' => (bool) $hasError,
        'key' => Engine_File_Diff::getCodeKey($diffResult),
        'result' => $diffResult,
        'relPath' => $left,
        'leftPath' => ( is_array($leftFormatted) ? $leftFormatted['path'] : ( is_string($leftFormatted) ? $leftFormatted : null) ),
        'rightPath' => ( is_array($rightFormatted) ? $rightFormatted['path'] : ( is_string($rightFormatted) ? $rightFormatted : null) ),
        'originalPath' => ( is_array($originalFormatted) ? $originalFormatted['path'] : ( is_string($originalFormatted) ? $originalFormatted : null) ),
      );

      $fileOperations[$left] = $diffInfo;
    }
    
    return array(
      'key' => $this->getKey(),
      'error' => (bool) $hasErrors,
      'operations' => $fileOperations,
    );
  }


  
  // Diff

  public function getDiffCurrentFiles()
  {
    $currentPackage = $this->getCurrentPackage();
    if( !$currentPackage ) {
      return array();
    }

    // Get files
    $files = $this->_getPackageFiles($currentPackage);

    // Add package file?
    if( $this->getPrimaryPackage()->getType() != 'patch' ) {
      $packageFile = 'application/packages/' . $currentPackage->getKey() . '.json';
      $files[$packageFile] = Engine_File_Diff_File::build($currentPackage->getBasePath() . DIRECTORY_SEPARATOR . $packageFile);
      $files[$packageFile]['path'] = $packageFile;
    }
    
    return $files;
  }

  public function getDiffTargetFiles()
  {
    $resultantPackage = $this->getTargetPackage();
    if( !$resultantPackage ) {
      return array();
    }

    // Get files
    $files = $this->_getPackageFiles($resultantPackage);

    // Add package file?
    if( $this->getPrimaryPackage()->getType() != 'patch' ) {
      $packageFile = 'application/packages/' . $resultantPackage->getKey() . '.json';
      $files[$packageFile] = Engine_File_Diff_File::build($resultantPackage->getBasePath() . DIRECTORY_SEPARATOR . $packageFile);
      $files[$packageFile]['path'] = $packageFile;
    }

    return $files;
  }

  public function getDiffMasterFiles()
  {
    return array_unique(array_merge(
      array_keys($this->getDiffCurrentFiles()),
      array_keys($this->getDiffTargetFiles())
    ));
  }

  public function getDiffBatch()
  {
    $rightFilesRaw = $this->getDiffTargetFiles();
    $originalFilesRaw = $this->getDiffCurrentFiles();
    //$leftFilesRaw = $this->getDiffMasterFiles();
    $leftFilesRaw = array_unique(array_merge(
      array_keys($originalFilesRaw),
      array_keys($originalFilesRaw)
    ));

    sort($leftFilesRaw);

    $leftFiles = array();
    $rightFiles = array();
    $originalFiles = array();

    $leftPath = $this->getManager()->getBasePath();
    $rightPath = null !== $this->getTargetPackage() ? $this->getTargetPackage()->getBasePath() : null;
    $originalPath = null !== $this->getCurrentPackage() ? $this->getCurrentPackage()->getBasePath() : null;

    foreach( $leftFilesRaw as $file ) {

      // Skip directories

      // Format left
      $left = $file;

      // Format right
      $right = null;
      if( isset($rightFilesRaw[$file]) ) {
        if( isset($rightFilesRaw[$file]['dir']) && $rightFilesRaw[$file]['dir'] ) continue;
        $right = $rightFilesRaw[$file];
        //$right = $this->_formatFileData($rightFilesRaw[$file], $rightPath, $file);
      }

      // Format original
      $original = null;
      if( isset($originalFilesRaw[$file]) ) {
        if( isset($originalFilesRaw[$file]['dir']) && $originalFilesRaw[$file]['dir'] ) continue;
        $original = $originalFilesRaw[$file];
        //$original = $this->_formatFileData($originalFilesRaw[$file], $originalPath, $file);
      }

      $leftFiles[] = $this->_formatFileData($left, $leftPath, $file);
      $rightFiles[] = $this->_formatFileData($right, $rightPath, $file);
      $originalFiles[] = $this->_formatFileData($original, $originalPath, $file);
    }

    // Skip the three-way (since we don't have any source info)
    if( empty($originalFilesRaw) ) {
      $originalFiles = null;
    }

    $diff = Engine_File_Diff_Batch::factory($leftFiles, $rightFiles, $originalFiles);

    //$diff->targetPackage = $this->getTargetPackage();
    //$diff->currentPackage = $this->getCurrentPackage();
    $diff->packageKey = $this->getKey();
    $diff->packageGuid = $this->getGuid();

    // Backwards compatibility
    //$diff->package = $diff->targetPackage;
    //$diff->previousPackage = $diff->currentPackage;

    // Execute now?
    $diff->execute();

    // set file ops and diff summary
    $this->_setFileOperations($diff);
    $this->_setDiffSummary($diff);
    
    return $diff;
  }



  // Cleanup

  public function cleanup()
  {
    $manager = $this->getManager();
    $basePath = $manager->getBasePath();
    $tempVfs = Engine_Vfs::factory('system', array(
      'path' => $basePath,
    ));

    $archivesPath = $manager->getAbsPath(Engine_Package_Manager::PATH_ARCHIVES);
    $packagesPath = $manager->getAbsPath(Engine_Package_Manager::PATH_PACKAGES);
    
    $currentPackage = $this->getCurrentPackage();
    $targetPackage = $this->getTargetPackage();

    // Key-based
    if( $currentPackage ) {
      try {
        $archivePath = $archivesPath . '/' . $currentPackage->getKey() . '.tar';
        if( $tempVfs->exists($archivePath) ) {
          $tempVfs->unlink($archivePath);
        }
      } catch( Exception $e ) {}
      try {
        $extractedPath = $packagesPath . '/' . $currentPackage->getKey();
        if( $tempVfs->exists($extractedPath) ) {
          $tempVfs->removeDirectory($extractedPath, true);
        }
      } catch( Exception $e ) {}
    }
    if( $targetPackage ) {
      $archivePath = $archivesPath . '/' . $targetPackage->getKey() . '.tar';
      if( $tempVfs->exists($archivePath) ) {
        $tempVfs->unlink($archivePath);
      }
      $extractedPath = $packagesPath . '/' . $targetPackage->getKey();
      if( $tempVfs->exists($extractedPath) ) {
        $tempVfs->removeDirectory($extractedPath, true);
      }
    }

    // Source-based
    if( $currentPackage ) {
      try {
        $sourcePath = $currentPackage->getSourcePath();
      } catch( Exception $e ) {
        $sourcePath = null;
      }
      if( $sourcePath && strpos($sourcePath, dirname($archivesPath)) !== false ) {
        $extractedPath = dirname($sourcePath);
        $archivePath = $archivesPath . '/' . basename($extractedPath) . '.tar';
        try {
          if( $tempVfs->exists($extractedPath) ) {
            $tempVfs->removeDirectory($extractedPath, true);
          }
        } catch( Exception $e ) {}
        try {
          if( $tempVfs->exists($archivePath) ) {
            $tempVfs->unlink($archivePath);
          }
        } catch( Exception $e ) {}
      }
    }
    if( $targetPackage ) {
      try {
        $sourcePath = $targetPackage->getSourcePath();
      } catch( Exception $e ) {
        $sourcePath = null;
      }
      if( $sourcePath && strpos($sourcePath, dirname($archivesPath)) !== false ) {
        $extractedPath = dirname($sourcePath);
        $archivePath = $archivesPath . '/' . basename($extractedPath) . '.tar';
        try {
          if( $tempVfs->exists($extractedPath) ) {
            $tempVfs->removeDirectory($extractedPath, true);
          }
        } catch( Exception $e ) {}
        try {
          if( $tempVfs->exists($archivePath) ) {
            $tempVfs->unlink($archivePath);
          }
        } catch( Exception $e ) {}
      }
    }

    return $this;
  }


  
  // Utility

  protected function _getPackageFiles(Engine_Package_Manifest_Entity_Package $package = null)
  {
    if( null === $package ) {
      return array();
    }
    $files = $package->getFileStructure(true);
    return $files;
  }

  protected function _formatFileData($file, $basePath, $filePath)
  {
    if( is_string($file) ) {
      return $basePath . DIRECTORY_SEPARATOR . $file;
    } else if( is_array($file) && isset($file['path']) ) {
      $file['path'] = $basePath . DIRECTORY_SEPARATOR . $filePath;
      $file['exists'] = true;
      if( isset($file['sha1']) && !isset($file['hash']) ) {
        $file['hash'] = $file['sha1'];
      }
      return $file;
    } else if( is_array($file) ) {
      $file['path'] = $basePath . DIRECTORY_SEPARATOR . $filePath;
      $file['exists'] = false;
      return $file;
    } else if( null === $file ) {
      return array(
        'path' => $basePath . DIRECTORY_SEPARATOR . $filePath, // Hm
        'exists' => false,
      );
    } else {
      return $file; // wth
    }
  }
}