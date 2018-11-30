<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Archive.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Archive
{
  static public function inflate($file, $outputPath)
  {
    // Sanity
    if( !file_exists($file) || !is_file($file) || strtolower(pathinfo($file, PATHINFO_EXTENSION)) != 'tar' ) {
      throw new Engine_Package_Exception('File does not exist or is not a tar file');
    }
    if( !file_exists($outputPath) || !is_dir($outputPath) || !is_writeable($outputPath) ) {
      throw new Engine_Package_Exception('Output path does not exist, is not a directory, or is not writeable');
    }
    self::_loadArchiveClass();

    // Make other paths
    $outputSubPath = substr(basename($file), 0, strrpos(basename($file), '.'));
    $outputFullPath = rtrim($outputPath, '/\\') . DIRECTORY_SEPARATOR . $outputSubPath;

    // If output path already exists, remove
    if( file_exists($outputFullPath) ) {
      self::_rmdirRecursive($outputFullPath, true);
    }

    // Try to make full output path
    if( !is_dir($outputFullPath) ) {
      if( !mkdir($outputFullPath, 0777, true) ) {
        throw new Engine_Package_Exception('Unable to create output folder');
      }
    }

    // Extract
    $archive = new Archive_Tar($file);
    $rval = $archive->extract($outputFullPath);

    // Throw error if failed
    if( $archive->isError($rval) ) {
      throw new Engine_Package_Exception('Error in archive: ' . $rval->getMessage());
    }

    return $outputFullPath;
  }

  static public function deflate(Engine_Package_Manifest $package, $outputPath)
  {
    // Sanity
    if( !file_exists($outputPath) || !is_dir($outputPath) || !is_writeable($outputPath) ) {
      throw new Engine_Package_Exception('Output path does not exist, is not a directory, or is not writeable');
    }
    if( !is_dir($package->getBasePath()) ) {
      throw new Engine_Package_Exception('Missing package base path');
    }
    self::_loadArchiveClass();

    // Make filenames and paths
    $basePath = $package->getBasePath();
    $archiveFile = $package->getKey() . '.tar';
    $archiveFullPath = $outputPath . DIRECTORY_SEPARATOR . $archiveFile;
    if( file_exists($archiveFullPath) && !unlink($archiveFullPath) ) {
      throw new Engine_Package_Exception('Target archive already exists and unable to remove');
    }

    // Start packing
    $archive = new Archive_Tar($archiveFullPath);
    $archive->setIgnoreList(array('CVS', '.svn'));

    // Add all directories, files, and subpackages
    $package->addToArchive($archive);

    return $archiveFullPath;
  }

  static public function readPackageFile($file)
  {
    // Sanity
    if( !file_exists($file) || !is_file($file) || strtolower(pathinfo($file, PATHINFO_EXTENSION)) != 'tar' ) {
      throw new Engine_Package_Exception('File does not exist or is not a tar file');
    }
    self::_loadArchiveClass();

    // Create archive object
    $archive = new Archive_Tar($file);

    // List files
    $fileList = $archive->listContent();
    if( empty($fileList) ) {
      throw new Engine_Package_Exception('Unable to open archive');
    }

    // Check for root package file
    $rootPackageFile = null;
    foreach( $fileList as $arFile ) {
      if( $arFile['filename'] == 'package.json' ) {
        $rootPackageFile = $arFile['filename'];
        break;
      }
    }
    if( null === $rootPackageFile ) {
      throw new Engine_Package_Exception('Root package file not found.');
    }

    // Start building package stuff
    $packageFileObject = new Engine_Package_Manifest();
    $packageFileObject->fromString($archive->extractInString($rootPackageFile), 'json');
    return $packageFileObject;
  }

  static protected function _loadArchiveClass()
  {
    include_once 'Archive/Tar.php';
    if( !class_exists('Archive_Tar', false) ) {
      throw new Engine_Package_Exception('Unable to load Archive_Tar class');
    }
  }

  static protected function _addPackageToArchive(Engine_Package_Manifest $package, Archive_Tar $archive, $basePath)
  {
    $rval = null;
    foreach( $package->getStructure() as $name => $contents ) {
      if( !($contents instanceof Engine_Package_Manifest_Entity_Abstract) ) {
        continue;
      }
      switch( $contents->getType() ) {
        case 'package':
          $subPackageObject = new Engine_Package_Manifest($basePath . DIRECTORY_SEPARATOR . $contents['path'] . DIRECTORY_SEPARATOR . $contents['packageFile']);
          $subPackageObject->setBasePath($basePath);
          self::_addPackageToArchive($subPackageObject, $archive, $basePath);
          break;
        case 'directory':
          // Add directory
          $rval = $archive->addModify($basePath . DIRECTORY_SEPARATOR . $contents['path'], null, $basePath);
          if( $archive->isError($rval) ) {
            throw new Engine_Package_Exception(sprintf('Unable to add path "%s" to archive', $contents['path']));
          }
          break;
        case 'file':
          // Add file
          $rval = $archive->addModify($basePath . DIRECTORY_SEPARATOR . $contents['path'], null, $basePath);
          if( $archive->isError($rval) ) {
            throw new Engine_Package_Exception(sprintf('Unable to add path "%s" to archive', $contents['path']));
          }
          break;
        default:
          throw new Engine_Package_Exception('unknown contents type');
          break;
      }
    }
    
    // Throw error if failed
    if( $archive->isError($rval) ) {
      throw new Engine_Package_Exception('Error in archive: ' . $rval->getMessage());
    }
  }

  static protected function _rmdirRecursive($path, $includeSelf = false)
  {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME), RecursiveIteratorIterator::CHILD_FIRST);
    foreach( $it as $key => $child ) {
      if( $child->getFilename() == '.' || $child->getFilename() == '..' || $child->getFilename() == '.svn' ) {
        continue;
      } else if( $child->isDir() ) {
        if( !rmdir($key) ) {
          throw new Engine_Package_Exception(sprintf('Unable to remove directory: %s', $key));
        }
      } else if( $child->isFile() ) {
        if( !unlink($key) ) {
          throw new Engine_Package_Exception(sprintf('Unable to remove file: %s', $key));
        }
      }
    }
    if( $includeSelf ) {
      if( is_dir($path) && !rmdir($path) ) {
        throw new Engine_Package_Exception(sprintf('Unable to remove directory: %s', $path));
      }
    }
  }
}