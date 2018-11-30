<?php

class Engine_File_Archive_Gzip extends Engine_File_Archive
{
  public function insert($source)
  {
    $from = new Engine_Stream_File($source, 'r');
    $to = new Engine_Stream_Gzip($this->_filename, 'w');
    
    $to->stream_copy($from);

    return $this;
  }

  public function extract($target)
  {
    $from = new Engine_Stream_Gzip($this->_filename, 'r');
    $to = new Engine_Stream_File($target, 'w');

    $to->stream_copy($from);
    
    return $this;
  }
}