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
 * PDF file converter (GHOSTSCRIPT is required!)
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Converter
{
    public static function usingGhostScript(&$source, $version = 1.4)
    {
        if (!file_exists($source))
            throw new Zend_Pdf_Exception("File '{$source}' does not exists.");
        if (mime_content_type($source) !== 'application/pdf')
            throw new Zend_Pdf_Exception("File '{$source}' is not in pdf extension.");

        exec( "gs -sDEVICE=pdfwrite -dCompatibilityLevel={$version} -dNOPAUSE -dQUIET -dBATCH -sOutputFile={$source} {$source}", $output, $output_vars);

        if (!$output_vars)
            throw new Zend_Pdf_Exception('Something went wrong while converting provided file.');
    }
}