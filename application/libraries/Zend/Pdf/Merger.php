<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Parser.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * PDF file merger (GHOSTSCRIPT is required!)
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Merger
{
    /**
     * @param $sources
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public static function usingGhostScript($sources = null)
    {
        // If no files given
        if (!$sources || ($tmp = count($sources)) == 0) return null;
        // If there is only 1 file to merge
        elseif ($tmp == 1) return $sources[0];
        // Leave valid sources only
        $sources = array_filter($sources, function($source) {
            return file_exists($source) && mime_content_type($source) == 'application/pdf';
        });
        // Meta-data to exec GhostScript package
        $sources_list = implode(' ', array_values($sources));
        $output_file = APPLICATION_PATH_TMP . DS . md5(time()) . ".pdf";
        $command = "gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile={$output_file} {$sources_list}";
        shell_exec($command);
        return $output_file;
    }
}