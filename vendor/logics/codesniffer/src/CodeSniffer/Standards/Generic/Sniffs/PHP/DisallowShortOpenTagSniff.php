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
 * DisallowShortOpenTagSniff.
 *
 * Makes sure that shorthand PHP open tags are not used.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php $
 */

class DisallowShortOpenTagSniff implements Sniff
    {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */

	public function register()
	    {
		return array(
			T_OPEN_TAG,
			T_OPEN_TAG_WITH_ECHO,
		       );
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable \"<?php\"
	 * @untranslatable \"%s\"
	 * @untranslatable Found
	 * @untranslatable \"<?php echo %s ...\"
	 * @untranslatable \"%s %s ...\"
	 * @untranslatable EchoFound
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens  = &$phpcsFile->tokens;
		$openTag = $tokens[$stackPtr];

		if ($openTag["content"] === "<?")
		    {
			$error = _("Short PHP opening tag used; expected") . " \"<?php\" " . _("but found") . " \"%s\"";
			$data  = array($openTag["content"]);
			$phpcsFile->addError($error, $stackPtr, "Found", $data);
		    }

		if ($openTag["code"] === T_OPEN_TAG_WITH_ECHO)
		    {
			$nextVar = $tokens[$phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true)];
			$error   = _("Short PHP opening tag used with echo; expected") . " \"<?php echo %s ...\" " . _("but found") . " \"%s %s ...\"";
			$data    = array(
				    $nextVar["content"],
				    $openTag["content"],
				    $nextVar["content"],
				   );
			$phpcsFile->addError($error, $stackPtr, "EchoFound", $data);
		    }
	    } //end process()


    } //end class

?>
