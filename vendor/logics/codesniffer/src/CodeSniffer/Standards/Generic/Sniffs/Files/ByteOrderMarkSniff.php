<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer\Generic
 * @see     http://en.wikipedia.org/wiki/Byte_order_mark
 */

namespace Logics\BuildTools\CodeSniffer\Generic;

use \Logics\BuildTools\CodeSniffer\File;
use \Logics\BuildTools\CodeSniffer\Sniff;

/**
 * ByteOrderMarkSniff.
 *
 * A simple sniff for detecting BOMs that may corrupt application work.
 *
 * @author    Piotr Karas <office@mediaself.pl>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2010-2011 mediaSELF Sp. z o.o.
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/Files/ByteOrderMarkSniff.php $
 *
 * @untranslatable efbbbf
 * @untranslatable feff
 * @untranslatable fffe
 */

class ByteOrderMarkSniff implements Sniff
    {

	/**
	 * List of supported BOM definitions.
	 *
	 * Use encoding names as keys and hex BOM representations as values.
	 *
	 * @var array
	 */
	public $bomDefinitions = array(
				  "UTF-8"       => "efbbbf",
				  "UTF-16 (BE)" => "feff",
				  "UTF-16 (LE)" => "fffe",
				 );

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(T_INLINE_HTML);
	    } //end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable Found
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		// The BOM will be the very first token in the file.
		if ($stackPtr === 0)
		    {
			$tokens = &$phpcsFile->tokens;

			foreach ($this->bomDefinitions as $bomName => $expectedBomHex)
			    {
				$bomByteLength = (strlen($expectedBomHex) / 2);
				$htmlBomHex    = bin2hex(substr($tokens[$stackPtr]["content"], 0, $bomByteLength));
				if ($htmlBomHex === $expectedBomHex)
				    {
					$errorData = array($bomName);
					$error     = _("File contains") . " %s " . _("byte order mark, which may corrupt your application");
					$phpcsFile->addError($error, $stackPtr, "Found", $errorData);
					break;
				    }
			    }
		    }
	    } //end process()


    } //end class

?>