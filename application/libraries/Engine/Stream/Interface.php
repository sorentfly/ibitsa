<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Interface.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Stream
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
interface Engine_Stream_Interface
{
  const OPT_USE_PATH = STREAM_USE_PATH; // 1
  const OPT_REPORT_ERROR = STREAM_REPORT_ERRORS; // 8
  const OPT_THROW_EXCEPTIONS = 16;
  
  public function stream_close();

  public function stream_eof();

  public function stream_flush();

  public function stream_lock($operation);
  
  public function stream_open($path, $mode, $options, &$opened_path);
  
  public function stream_read($count);

  public function stream_seek($offset, $whence);

  public function stream_set_option($option, $arg1, $arg2);

  public function stream_stat();

  public function stream_tell();

  public function stream_write($data);
}