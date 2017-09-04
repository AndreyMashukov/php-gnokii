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
 * This sniff class detected empty statement.
 *
 * This sniff implements the common algorithm for empty statement body detection.
 * A body is considered as empty if it is completely empty or it only contains
 * whitespace characters and|or comments.
 *
 * <code>
 * stmt {
 *   // foo
 * }
 * stmt (conditions) {
 *   // foo
 * }
 * </code>
 *
 * Statements covered by this sniff are <b>catch</b>, <b>do</b>, <b>else</b>,
 * <b>elsif</b>, <b>for</b>, <b>foreach<b>, <b>if</b>, <b>switch</b>, <b>try</b>
 * and <b>while</b>. We don't complain about empty functions here.
 *
 * @author    Manuel Pichler <mapi@manuel-pichler.de>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2007-2008 Manuel Pichler. All rights reserved.
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Standards/Generic/Sniffs/CodeAnalysis/EmptyStatementSniff.php $
 */

class EmptyStatementSniff implements Sniff
    {

	/**
	 * List of block tokens that this sniff covers.
	 *
	 * The key of this hash identifies the required token while the boolean
	 * value says mark an error or mark a warning.
	 *
	 * @var array
	 */
	protected $checkedTokens = array(
				    T_CATCH   => false,
				    T_DO      => true,
				    T_ELSE    => true,
				    T_ELSEIF  => true,
				    T_FOR     => true,
				    T_FOREACH => true,
				    T_IF      => true,
				    T_SWITCH  => true,
				    T_TRY     => true,
				    T_WHILE   => true,
				   );

	/**
	 * Registers the tokens that this sniff wants to listen for.
	 *
	 * @return array(int)
	 */

	public function register()
	    {
		return array_keys($this->checkedTokens);
	    } //end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 *
	 * @untranslatable %s
	 * @untranslatable NotAllowed
	 * @untranslatable NotAllowedWarning
	 */

	public function process(File $phpcsFile, $stackPtr)
	    {
		$tokens = &$phpcsFile->tokens;
		$token  = $tokens[$stackPtr];

		// Skip for-statements without body.
		if (isset($token["scope_opener"]) === true)
		    {
			$next = ++$token["scope_opener"];
			$end  = --$token["scope_closer"];

			$emptyBody = true;
			for (; $next <= $end; ++$next)
			    {
				if (in_array($tokens[$next]["code"], Tokens::$emptyTokens) === false)
				    {
					$emptyBody = false;
					break;
				    }
			    }

			if ($emptyBody === true)
			    {
				// Get token identifier.
				$name  = $phpcsFile->getTokensAsString($stackPtr, 1);
				$error = _("Empty") . " %s " . _("statement detected");
				$data  = array(strtoupper($name));
				if ($this->checkedTokens[$token["code"]] === true)
				    {
					$phpcsFile->addError($error, $stackPtr, "NotAllowed", $data);
				    }
				else
				    {
					$phpcsFile->addWarning($error, $stackPtr, "NotAllowedWarning", $data);
				    }
			    }
		    } //end if
	    } //end process()


    } //end class

?>
