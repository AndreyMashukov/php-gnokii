<?php

/**
 * Represents a CodeSniffer sniff for sniffing coding standards.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Represents a CodeSniffer multi-file sniff for sniffing coding standards.
 *
 * A multi-file sniff is called after all files have been checked using the
 * regular sniffs. The process() method is passed an array of CodeSniffer_File
 * objects, one for each file checked during the script run.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/MultiFileSniff.php $
 */

interface MultiFileSniff
    {

	/**
	 * Called once per script run to allow for processing of this sniff.
	 *
	 * @param array(Logics\BuildTools\CodeSniffer\File) $files The CodeSniffer files processed
	 *                                                         during the script run.
	 *
	 * @return void
	 */

	public function process(array $files);


    } //end interface

?>
