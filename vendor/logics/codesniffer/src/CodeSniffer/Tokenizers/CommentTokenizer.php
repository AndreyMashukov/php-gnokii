<?php

/**
 * Tokenizes doc block comments.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Tokenizes doc block comments.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Tokenizers/CommentTokenizer.php $
 */

class CommentTokenizer
    {

	/**
	 * Creates an array of tokens when given some PHP code.
	 *
	 * Starts by using token_get_all() but does a lot of extra processing
	 * to insert information about the context of the token.
	 *
	 * @param string $string   The string to tokenize.
	 * @param string $eolChar  The EOL character to use for splitting strings.
	 * @param int    $stackPtr The position of the first token in the file.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_OPEN_TAG  T_DOC_COMMENT_OPEN_TAG token
	 * @internalconst T_DOC_COMMENT_CLOSE_TAG T_DOC_COMMENT_CLOSE_TAG token
	 * @internalconst T_DOC_COMMENT_TAG       T_DOC_COMMENT_TAG token
	 * @internalconst T_DOC_COMMENT_STAR      T_DOC_COMMENT_STAR token
	 *
	 * @untranslatable T_DOC_COMMENT_OPEN_TAG
	 * @untranslatable T_DOC_COMMENT_CLOSE_TAG
	 * @untranslatable : T_DOC_COMMENT_OPEN_TAG =>
	 * @untranslatable : T_DOC_COMMENT_WHITESPACE =>
	 * @untranslatable T_DOC_COMMENT_STAR
	 * @untranslatable : T_DOC_COMMENT_STAR => *
	 * @untranslatable : T_DOC_COMMENT_CLOSE_TAG =>
	 */

	public function tokenizeString($string, $eolChar, $stackPtr)
	    {
		Console::report("*** " . _("START COMMENT TOKENIZING") . " ***", 2, 1);

		$tokens   = array();
		$numChars = strlen($string);

		// Doc block comments start with /*, but typically contain an extra star when they are used for function and class comments.
		for ($c = 0; $c < $numChars; $c++)
		    {
			if ($string[$c] !== "/" && $string[$c] !== "*")
			    {
				break;
			    }
		    } //end for

		$openTag           = substr($string, 0, $c);
		$tokens[$stackPtr] = array(
				      "content"      => $openTag,
				      "code"         => T_DOC_COMMENT_OPEN_TAG,
				      "type"         => "T_DOC_COMMENT_OPEN_TAG",
				      "comment_tags" => array(),
				     );

		$openPtr = $stackPtr;
		$stackPtr++;

		Console::report(_("Create comment token") . ": T_DOC_COMMENT_OPEN_TAG => " . Console::prepareForOutput($openTag), 2, 1);

		// Strip off the close tag so it doesn't interfere with any of our comment line processing.
		// The token will be added to the stack just before we return it.
		for ($i = ($numChars - 1); $i > $c; $i--)
		    {
			if ($string[$i] !== "/" && $string[$i] !== "*")
			    {
				break;
			    }
		    } //end for

		$i++;
		$closeTag = array(
			     "content"        => substr($string, $i),
			     "code"           => T_DOC_COMMENT_CLOSE_TAG,
			     "type"           => "T_DOC_COMMENT_CLOSE_TAG",
			     "comment_opener" => $openPtr,
			    );

		$string   = substr($string, 0, $i);
		$numChars = strlen($string);

		// Process each line of the comment.
		while ($c < $numChars)
		    {
			$lineTokens = $this->_processLine($string, $eolChar, $c, $numChars);
			foreach ($lineTokens as $lineToken)
			    {
				$tokens[$stackPtr] = $lineToken;
				Console::report(_("Create comment token") . ":" . " " . $lineToken["type"] . " => " . Console::prepareForOutput($lineToken["content"]), 2, 1);

				if ($lineToken["code"] === T_DOC_COMMENT_TAG)
				    {
					$tokens[$openPtr]["comment_tags"][] = $stackPtr;
				    }

				$c += strlen($lineToken["content"]);
				$stackPtr++;
			    } //end foreach

			if ($c === $numChars)
			    {
				break;
			    }

			// We've started a new line, so process the indent.
			$space = $this->_collectWhitespace($string, $c, $numChars);
			if ($space !== null)
			    {
				$tokens[$stackPtr] = $space;
				$stackPtr++;
				Console::report(_("Create comment token") . ": T_DOC_COMMENT_WHITESPACE => " . Console::prepareForOutput($space["content"]), 2, 1);

				$c += strlen($space["content"]);
				if ($c === $numChars)
				    {
					break;
				    }
			    } //end if

			if ($string[$c] === "*")
			    {
				// This is a function or class doc block line.
				$c++;
				$tokens[$stackPtr] = array(
						      "content" => "*",
						      "code"    => T_DOC_COMMENT_STAR,
						      "type"    => "T_DOC_COMMENT_STAR",
						     );

				$stackPtr++;

				Console::report(_("Create comment token") . ": T_DOC_COMMENT_STAR => *", 2, 1);
			    } //end if

			// Now we are ready to process the actual content of the line. So round we go.
		    } //end while

		$tokens[$stackPtr]                  = $closeTag;
		$tokens[$openPtr]["comment_closer"] = $stackPtr;
		Console::report(_("Create comment token") . ": T_DOC_COMMENT_CLOSE_TAG => " . Console::prepareForOutput($closeTag["content"]), 2, 1);

		Console::report("*** " . _("END COMMENT TOKENIZING") . " ***", 2, 1);

		return $tokens;
	    } //end tokenizeString()


	/**
	 * Process a single line of a comment.
	 *
	 * @param string $string  The comment string being tokenized.
	 * @param string $eolChar The EOL character to use for splitting strings.
	 * @param int    $start   The position in the string to start processing.
	 * @param int    $end     The position in the string to end processing.
	 *
	 * @return array
	 *
	 * @internalconst T_DOC_COMMENT_TAG        T_DOC_COMMENT_TAG tonken
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 *
	 * @untranslatable T_DOC_COMMENT_TAG
	 * @untranslatable T_DOC_COMMENT_STRING
	 * @untranslatable T_DOC_COMMENT_WHITESPACE
	 */

	private function _processLine($string, $eolChar, $start, $end)
	    {
		$tokens = array();

		// Collect content padding.
		$space = $this->_collectWhitespace($string, $start, $end);
		if ($space !== null)
		    {
			$tokens[] = $space;
			$start   += strlen($space["content"]);
		    }

		if (isset($string[$start]) === true)
		    {
			if ($string[$start] === "@")
			    {
				// The content up until the first whitespace is the tag name.
				$matches = array();
				preg_match("/@[^\s]+/", $string, $matches, 0, $start);
				if (isset($matches[0]) === true)
				    {
					$tagName  = $matches[0];
					$start   += strlen($tagName);
					$tokens[] = array(
						     "content" => $tagName,
						     "code"    => T_DOC_COMMENT_TAG,
						     "type"    => "T_DOC_COMMENT_TAG",
						    );

					// Then there will be some whitespace.
					$space = $this->_collectWhitespace($string, $start, $end);
					if ($space !== null)
					    {
						$tokens[] = $space;
						$start   += strlen($space["content"]);
					    }
				    } //end if
			    } //end if

			// Process the rest of the line.
			$eol = strpos($string, $eolChar, $start);
			if ($eol === false)
			    {
				$eol = $end;
			    }

			if ($eol > $start)
			    {
				$tokens[] = array(
					     "content" => substr($string, $start, ($eol - $start)),
					     "code"    => T_DOC_COMMENT_STRING,
					     "type"    => "T_DOC_COMMENT_STRING",
					    );
			    }

			if ($eol !== $end)
			    {
				$tokens[] = array(
					     "content" => substr($string, $eol, strlen($eolChar)),
					     "code"    => T_DOC_COMMENT_WHITESPACE,
					     "type"    => "T_DOC_COMMENT_WHITESPACE",
					    );
			    }
		    } //end if

		return $tokens;
	    } //end _processLine()


	/**
	 * Collect consecutive whitespace into a single token.
	 *
	 * @param string $string The comment string being tokenized.
	 * @param int    $start  The position in the string to start processing.
	 * @param int    $end    The position in the string to end processing.
	 *
	 * @return array|null
	 *
	 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
	 *
	 * @untranslatable T_DOC_COMMENT_WHITESPACE
	 */

	private function _collectWhitespace($string, $start, $end)
	    {
		$space = "";
		for ($start; $start < $end; $start++)
		    {
			if ($string[$start] !== " " && $string[$start] !== "\t")
			    {
				break;
			    }

			$space .= $string[$start];
		    } //end for

		if ($space === "")
		    {
			return null;
		    }
		else
		    {
			$token = array(
				  "content" => $space,
				  "code"    => T_DOC_COMMENT_WHITESPACE,
				  "type"    => "T_DOC_COMMENT_WHITESPACE",
				 );

			return $token;
		    } //end if
	    } //end _collectWhitespace()


    } //end class

?>
