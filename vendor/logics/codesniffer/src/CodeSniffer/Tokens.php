<?php

/**
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * The Tokens class contains functionality to make all tokens consistent and builds on top of standard PHP tokenizer.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Tokens.php $
 */

final class Tokens
    {

	use TokenDefinitions;

	/**
	 * Returns the highest weighted token type.
	 *
	 * Tokens are weighted by their approximate frequency of appearance in code
	 * - the less frequently they appear in the code, the higher the weighting.
	 * For example T_CLASS tokens appear very infrequently in a file, and
	 * therefore have a high weighting.
	 *
	 * Returns false if there are no weightings for any of the specified tokens.
	 *
	 * @param array(int) $tokens The token types to get the highest weighted type for.
	 *
	 * @return int The highest weighted token.
	 */

	public static function getHighestWeightedToken(array $tokens)
	    {
		$highest     = -1;
		$highestType = false;

		$weights = self::$weightings;

		foreach ($tokens as $token)
		    {
			$weight = (isset($weights[$token]) === true) ? $weights[$token] : 0;

			if ($weight > $highest)
			    {
				$highest     = $weight;
				$highestType = $token;
			    }
		    }

		return $highestType;
	    } //end getHighestWeightedToken()


	/**
	 * Takes a token produced from <code>token_get_all()</code> and produces a
	 * more uniform token.
	 *
	 * Note that this method also resolves T_STRING tokens into more discrete
	 * types, therefore there is no need to call resolveTStringToken()
	 *
	 * @param string|array $token The token to convert.
	 *
	 * @return array The new token.
	 *
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 *
	 * @untranslatable T_OPEN_CURLY_BRACKET
	 */

	public static function standardiseToken($token)
	    {
		$token = (array) $token;

		if (isset($token[1]) === false)
		    {
			$newToken = self::resolveSimpleToken($token[0]);
		    }
		else
		    {
			if ($token[0] === T_STRING)
			    {
				// Some T_STRING tokens can be more specific.
				$newToken = self::_resolveTStringToken(strtolower($token[1]));
			    }
			else if ($token[0] === T_CURLY_OPEN)
			    {
				$newToken = array(
					     "code" => T_OPEN_CURLY_BRACKET,
					     "type" => "T_OPEN_CURLY_BRACKET",
					    );
			    }
			else
			    {
				$newToken = array(
					     "code" => $token[0],
					     "type" => token_name($token[0]),
					    );
			    } //end if

			$newToken["content"] = $token[1];
		    } //end if

		return $newToken;
	    } //end standardiseToken()


	/**
	 * Converts T_STRING tokens into more usable token names.
	 *
	 * The token should be produced using the token_get_all() function.
	 * Currently, not all T_STRING tokens are converted.
	 *
	 * @param string $token The T_STRING token to convert as constructed
	 *                      by token_get_all().
	 *
	 * @return array The new token.
	 *
	 * @untranslatable T_FALSE
	 * @untranslatable T_TRUE
	 * @untranslatable T_NULL
	 * @untranslatable T_SELF
	 * @untranslatable T_PARENT
	 * @untranslatable T_STRING
	 */

	private static function _resolveTStringToken($token)
	    {
		$tokens = array(
			   "false"  => "T_FALSE",
			   "true"   => "T_TRUE",
			   "null"   => "T_NULL",
			   "self"   => "T_SELF",
			   "parent" => "T_PARENT",
			  );

		$newToken = array();

		$newToken["type"] = ((isset($tokens[$token]) === true) ? $tokens[$token] : "T_STRING");
		$newToken["code"] = constant($newToken["type"]);

		return $newToken;
	    } //end _resolveTStringToken()


	/**
	 * Converts simple tokens into a format that conforms to complex tokens
	 * produced by token_get_all().
	 *
	 * Simple tokens are tokens that are not in array form when produced from
	 * token_get_all().
	 *
	 * @param string $token The simple token to convert.
	 *
	 * @return array The new token in array format.
	 *
	 * @untranslatable T_OPEN_CURLY_BRACKET
	 * @untranslatable T_CLOSE_CURLY_BRACKET
	 * @untranslatable T_OPEN_SQUARE_BRACKET
	 * @untranslatable T_CLOSE_SQUARE_BRACKET
	 * @untranslatable T_OPEN_PARENTHESIS
	 * @untranslatable T_CLOSE_PARENTHESIS
	 * @untranslatable T_COLON
	 * @untranslatable T_STRING_CONCAT
	 * @untranslatable T_INLINE_THEN
	 * @untranslatable T_SEMICOLON
	 * @untranslatable T_EQUAL
	 * @untranslatable T_MULTIPLY
	 * @untranslatable T_DIVIDE
	 * @untranslatable T_PLUS
	 * @untranslatable T_MINUS
	 * @untranslatable T_MODULUS
	 * @untranslatable T_POWER
	 * @untranslatable T_BITWISE_AND
	 * @untranslatable T_BITWISE_OR
	 * @untranslatable T_LESS_THAN
	 * @untranslatable T_GREATER_THAN
	 * @untranslatable T_BOOLEAN_NOT
	 * @untranslatable T_COMMA
	 * @untranslatable T_ASPERAND
	 * @untranslatable T_DOLLAR
	 * @untranslatable T_BACKTICK
	 * @untranslatable T_NONE
	 */

	public static function resolveSimpleToken($token)
	    {
		$tokens = array(
			   "{" => "T_OPEN_CURLY_BRACKET",
			   "}" => "T_CLOSE_CURLY_BRACKET",
			   "[" => "T_OPEN_SQUARE_BRACKET",
			   "]" => "T_CLOSE_SQUARE_BRACKET",
			   "(" => "T_OPEN_PARENTHESIS",
			   ")" => "T_CLOSE_PARENTHESIS",
			   ":" => "T_COLON",
			   "." => "T_STRING_CONCAT",
			   "?" => "T_INLINE_THEN",
			   ";" => "T_SEMICOLON",
			   "=" => "T_EQUAL",
			   "*" => "T_MULTIPLY",
			   "/" => "T_DIVIDE",
			   "+" => "T_PLUS",
			   "-" => "T_MINUS",
			   "%" => "T_MODULUS",
			   "^" => "T_POWER",
			   "&" => "T_BITWISE_AND",
			   "|" => "T_BITWISE_OR",
			   "<" => "T_LESS_THAN",
			   ">" => "T_GREATER_THAN",
			   "!" => "T_BOOLEAN_NOT",
			   "," => "T_COMMA",
			   "@" => "T_ASPERAND",
			   "$" => "T_DOLLAR",
			   "`" => "T_BACKTICK",
			  );

		$newToken = array();

		$newToken["type"]    = ((isset($tokens[$token]) === true) ? $tokens[$token] : "T_NONE");
		$newToken["code"]    = constant($newToken["type"]);
		$newToken["content"] = $token;

		return $newToken;
	    } //end resolveSimpleToken()


    } //end class

?>
