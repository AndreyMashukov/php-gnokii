<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Beauty
 */

namespace Logics\BuildTools\CodeSniffer\Beauty;

use \Logics\BuildTools\CodeSniffer\CodeSniffer;
use \Logics\BuildTools\CodeSniffer\Squiz\VariableCommentSniff as Squiz_VariableCommentSniff;

/**
 * Parses and verifies the variable doc comment.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Beauty/Sniffs/Commenting/VariableCommentSniff.php $
 */

class VariableCommentSniff extends Squiz_VariableCommentSniff
    {

	/**
	 * Suggest a type
	 *
	 * @param string $varType Variable type
	 *
	 * @return string Suggested type
	 */

	protected function getSuggestedType($varType)
	    {
		return CodeSniffer::suggestType($varType, true);
	    } //end getSuggestedType()


    } //end class

?>
