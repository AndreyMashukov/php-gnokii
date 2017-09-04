<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;
use \Logics\BuildTools\CodeSniffer\Tokens;

/**
 * FixmeSniff.
 *
 * Warns about "FIX ME" comments.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Sam Graham <php-codesniffer@illusori.co.uk>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Commenting/FixmeSniff.php $
 *
 * @untranslatable PHP
 * @untranslatable JS
 */

class FixmeSniff implements Sniff
    {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
				       "PHP",
				       "JS",
				      );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return Tokens::$commentTokens;
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable CommentFound
	 * @untranslatable TaskFound
	 * @untranslatable \"%s\"
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;

		$content = $tokens[$stackPtr]["content"];
		$matches = array();
		if (preg_match("/(?:\A|[^\p{L}]+)fixme([^\p{L}]+(.*)|\Z)/ui", $content, $matches) !== 0)
		    {
			// Clear whitespace and some common characters not required at
			// the end of a "fix me" message to make the error more informative.
			$type         = "CommentFound";
			$fixmeMessage = trim($matches[1]);
			$fixmeMessage = trim($fixmeMessage, "[]().");
			$error        = _("Comment refers to a FIXME task");
			$data         = array($fixmeMessage);
			if ($fixmeMessage !== "")
			    {
				$type   = "TaskFound";
				$error .= " \"%s\"";
			    }

			$phpcsFile->addError($error, $stackPtr, $type, $data);
		    }
	    } //end process()


    } //end class

?>
