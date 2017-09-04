<?php

/**
 * Tokenizes JS code.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

namespace Logics\BuildTools\CodeSniffer;

/**
 * Tokenizes JS code.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/Tokenizers/JSTokenizer.php $
 *
 * @internalconst T_CLOSE_CURLY_BRACKET T_CLOSE_CURLY_BRACKET token
 *
 * @untranslatable T_FUNCTION
 * @untranslatable T_PROTOTYPE
 * @untranslatable T_TRY
 * @untranslatable T_CATCH
 * @untranslatable T_RETURN
 * @untranslatable T_THROW
 * @untranslatable T_BREAK
 * @untranslatable T_SWITCH
 * @untranslatable T_CONTINUE
 * @untranslatable T_IF
 * @untranslatable T_ELSE
 * @untranslatable T_DO
 * @untranslatable T_WHILE
 * @untranslatable T_FOR
 * @untranslatable T_VAR
 * @untranslatable T_CASE
 * @untranslatable T_DEFAULT
 * @untranslatable T_TRUE
 * @untranslatable T_FALSE
 * @untranslatable T_NULL
 * @untranslatable T_THIS
 * @untranslatable T_TYPEOF
 * @untranslatable T_OPEN_PARENTHESIS
 * @untranslatable T_CLOSE_PARENTHESIS
 * @untranslatable T_OPEN_CURLY_BRACKET
 * @untranslatable T_CLOSE_CURLY_BRACKET
 * @untranslatable T_OPEN_SQUARE_BRACKET
 * @untranslatable T_CLOSE_SQUARE_BRACKET
 * @untranslatable T_INLINE_THEN
 * @untranslatable T_OBJECT_OPERATOR
 * @untranslatable T_PLUS
 * @untranslatable T_MINUS
 * @untranslatable T_MULTIPLY
 * @untranslatable T_MODULUS
 * @untranslatable T_DIVIDE
 * @untranslatable T_LOGICAL_XOR
 * @untranslatable T_COMMA
 * @untranslatable T_SEMICOLON
 * @untranslatable T_COLON
 * @untranslatable T_LESS_THAN
 * @untranslatable T_GREATER_THAN
 * @untranslatable T_IS_SMALLER_OR_EQUAL
 * @untranslatable T_IS_GREATER_OR_EQUAL
 * @untranslatable T_BOOLEAN_NOT
 * @untranslatable T_BOOLEAN_OR
 * @untranslatable T_BOOLEAN_AND
 * @untranslatable T_BITWISE_OR
 * @untranslatable T_BITWISE_AND
 * @untranslatable T_IS_NOT_EQUAL
 * @untranslatable T_IS_NOT_IDENTICAL
 * @untranslatable T_EQUAL
 * @untranslatable T_IS_EQUAL
 * @untranslatable T_IS_IDENTICAL
 * @untranslatable T_MINUS_EQUAL
 * @untranslatable T_PLUS_EQUAL
 * @untranslatable T_MUL_EQUAL
 * @untranslatable T_DIV_EQUAL
 * @untranslatable T_MOD_EQUAL
 * @untranslatable T_INC
 * @untranslatable T_DEC
 * @untranslatable T_COMMENT
 * @untranslatable T_DOC_COMMENT
 */

class JSTokenizer
    {

	/**
	 * A list of tokens that are allowed to open a scope.
	 *
	 * This array also contains information about what kind of token the scope opener uses to open and close the scope.
	 * If the token strictly requires an opener, if the token can share a scope closer, and who it can be shared with.
	 * An example of a token that shares a scope closer is a CASE scope.
	 *
	 * @var array
	 */
	public $scopeOpeners = array(
				T_IF       => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => false,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_TRY      => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => true,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_CATCH    => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => true,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_ELSE     => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => false,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_FOR      => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => false,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_FUNCTION => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => false,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_WHILE    => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => false,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_DO       => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => true,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_SWITCH   => array(
					       "start"  => array(T_OPEN_CURLY_BRACKET => T_OPEN_CURLY_BRACKET),
					       "end"    => array(T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET),
					       "strict" => true,
					       "shared" => false,
					       "with"   => array(),
					      ),
				T_CASE     => array(
					       "start"  => array(T_COLON => T_COLON),
					       "end"    => array(
							    T_BREAK    => T_BREAK,
							    T_RETURN   => T_RETURN,
							    T_CONTINUE => T_CONTINUE,
							    T_THROW    => T_THROW,
							   ),
					       "strict" => true,
					       "shared" => true,
					       "with"   => array(
							    T_DEFAULT => T_DEFAULT,
							    T_CASE    => T_CASE,
							    T_SWITCH  => T_SWITCH,
							   ),
					      ),
				T_DEFAULT  => array(
					       "start"  => array(T_COLON => T_COLON),
					       "end"    => array(
							    T_BREAK    => T_BREAK,
							    T_RETURN   => T_RETURN,
							    T_CONTINUE => T_CONTINUE,
							    T_THROW    => T_THROW,
							   ),
					       "strict" => true,
					       "shared" => true,
					       "with"   => array(
							    T_CASE   => T_CASE,
							    T_SWITCH => T_SWITCH,
							   ),
					      ),
			       );

	/**
	 * A list of tokens that end the scope.
	 *
	 * This array is just a unique collection of the end tokens from the _scopeOpeners array.
	 * The data is duplicated here to save time during parsing of the file.
	 *
	 * @var array
	 */
	public $endScopeTokens = array(
				  T_CLOSE_CURLY_BRACKET => T_CLOSE_CURLY_BRACKET,
				  T_BREAK               => T_BREAK,
				 );

	/**
	 * A list of special JS tokens and their types.
	 *
	 * @var array
	 */
	protected $tokenValues = array(
				  "function"  => "T_FUNCTION",
				  "prototype" => "T_PROTOTYPE",
				  "try"       => "T_TRY",
				  "catch"     => "T_CATCH",
				  "return"    => "T_RETURN",
				  "throw"     => "T_THROW",
				  "break"     => "T_BREAK",
				  "switch"    => "T_SWITCH",
				  "continue"  => "T_CONTINUE",
				  "if"        => "T_IF",
				  "else"      => "T_ELSE",
				  "do"        => "T_DO",
				  "while"     => "T_WHILE",
				  "for"       => "T_FOR",
				  "var"       => "T_VAR",
				  "case"      => "T_CASE",
				  "default"   => "T_DEFAULT",
				  "true"      => "T_TRUE",
				  "false"     => "T_FALSE",
				  "null"      => "T_NULL",
				  "this"      => "T_THIS",
				  "typeof"    => "T_TYPEOF",
				  "("         => "T_OPEN_PARENTHESIS",
				  ")"         => "T_CLOSE_PARENTHESIS",
				  "{"         => "T_OPEN_CURLY_BRACKET",
				  "}"         => "T_CLOSE_CURLY_BRACKET",
				  "["         => "T_OPEN_SQUARE_BRACKET",
				  "]"         => "T_CLOSE_SQUARE_BRACKET",
				  "?"         => "T_INLINE_THEN",
				  "."         => "T_OBJECT_OPERATOR",
				  "+"         => "T_PLUS",
				  "-"         => "T_MINUS",
				  "*"         => "T_MULTIPLY",
				  "%"         => "T_MODULUS",
				  "/"         => "T_DIVIDE",
				  "^"         => "T_LOGICAL_XOR",
				  ","         => "T_COMMA",
				  ";"         => "T_SEMICOLON",
				  ":"         => "T_COLON",
				  "<"         => "T_LESS_THAN",
				  ">"         => "T_GREATER_THAN",
				  "<="        => "T_IS_SMALLER_OR_EQUAL",
				  ">="        => "T_IS_GREATER_OR_EQUAL",
				  "!"         => "T_BOOLEAN_NOT",
				  "||"        => "T_BOOLEAN_OR",
				  "&&"        => "T_BOOLEAN_AND",
				  "|"         => "T_BITWISE_OR",
				  "&"         => "T_BITWISE_AND",
				  "!="        => "T_IS_NOT_EQUAL",
				  "!=="       => "T_IS_NOT_IDENTICAL",
				  "="         => "T_EQUAL",
				  "=="        => "T_IS_EQUAL",
				  "==="       => "T_IS_IDENTICAL",
				  "-="        => "T_MINUS_EQUAL",
				  "+="        => "T_PLUS_EQUAL",
				  "*="        => "T_MUL_EQUAL",
				  "/="        => "T_DIV_EQUAL",
				  "%="        => "T_MOD_EQUAL",
				  "++"        => "T_INC",
				  "--"        => "T_DEC",
				  "//"        => "T_COMMENT",
				  "/*"        => "T_COMMENT",
				  "/**"       => "T_DOC_COMMENT",
				  "*/"        => "T_COMMENT",
				 );

	/**
	 * A list string delimiters.
	 *
	 * @var array
	 */
	protected $stringTokens = array(
				   "'"  => "'",
				   "\"" => "\"",
				  );

	/**
	 * A list tokens that start and end comments.
	 *
	 * @var array
	 */
	protected $commentTokens = array(
				    "//"  => null,
				    "/*"  => "*/",
				    "/**" => "*/",
				   );

	/**
	 * Creates an array of tokens when given some PHP code.
	 *
	 * Starts by using token_get_all() but does a lot of extra processing
	 * to insert information about the context of the token.
	 *
	 * @param string $string  The string to tokenize.
	 * @param string $eolChar The EOL character to use for splitting strings.
	 *
	 * @return array
	 *
	 * @internalconst T_REGULAR_EXPRESSION T_REGULAR_EXPRESSION token
	 *
	 * @untranslatable T_WHITESPACE
	 * @untranslatable T_STRING
	 * @untranslatable T_CONSTANT_ENCAPSED_STRING
	 * @untranslatable T_REGULAR_EXPRESSION
	 * @untranslatable T_OPEN_TAG
	 * @untranslatable T_CLOSE_TAG
	 * @untranslatable T_LNUMBER
	 * @untranslatable T_DNUMBER
	 */

	public function tokenizeString($string, $eolChar = '\n')
	    {
		Console::report("*** " . _("START JS TOKENIZING") . " ***", 1, 1);

		$maxTokenLength = 0;
		foreach ($this->tokenValues as $token => $values)
		    {
			if (strlen($token) > $maxTokenLength)
			    {
				$maxTokenLength = strlen($token);
			    }
		    } //end foreach

		$tokens          = array();
		$inString        = "";
		$stringChar      = null;
		$inComment       = "";
		$buffer          = "";
		$preStringBuffer = "";
		$cleanBuffer     = false;

		$commentTokenizer = new CommentTokenizer();

		$tokens[] = array(
			     "code"    => T_OPEN_TAG,
			     "type"    => "T_OPEN_TAG",
			     "content" => "",
			    );

		// Convert newlines to single characters for ease of processing. We will change them back later.
		$string = str_replace($eolChar, "\n", $string);

		$chars    = str_split($string);
		$numChars = count($chars);
		for ($i = 0; $i < $numChars; $i++)
		    {
			$char = $chars[$i];

			Console::report(
			    _("Process char") . " " . $i . " => " . Console::prepareForOutput($char) .
			    " (" . _("buffer") . ": " . Console::prepareForOutput($buffer) . ")",
			    ((($inString !== "") ? 1 : 0) + (($inComment !== "") ? 1 : 0) + 1), 1
			);

			if ($inString === "" && $inComment === "" && $buffer !== "")
			    {
				// If the buffer only has whitespace and we are about to add a character, store the whitespace first.
				if (trim($char) !== "" && trim($buffer) === "")
				    {
					$tokens[] = array(
						     "code"    => T_WHITESPACE,
						     "type"    => "T_WHITESPACE",
						     "content" => str_replace("\n", $eolChar, $buffer),
						    );

					Console::report("=> " . _("Added token") . " T_WHITESPACE" . " (" . Console::prepareForOutput($buffer) . ")", 1, 1);

					$buffer = "";
				    } //end if

				// If the buffer is not whitespace and we are about to add a whitespace character, store the content first.
				if ($inString === "" && $inComment === "" && trim($char) === "" && trim($buffer) !== "")
				    {
					$tokens[] = array(
						     "code"    => T_STRING,
						     "type"    => "T_STRING",
						     "content" => str_replace("\n", $eolChar, $buffer),
						    );

					Console::report("=> " . _("Added token") . " T_STRING" . " (" . Console::prepareForOutput($buffer) . ")", 1, 1);

					$buffer = "";
				    } //end if
			    } //end if

			// Process strings.
			if ($inComment === "" && isset($this->stringTokens[$char]) === true)
			    {
				if ($inString === $char)
				    {
					// This could be the end of the string, but make sure it is not escaped first.
					$escapes = 0;
					for ($x = ($i - 1); $x >= 0; $x--)
					    {
						if ($chars[$x] !== "\\")
						    {
							break;
						    }

						$escapes++;
					    } //end for

					if ($escapes === 0 || ($escapes % 2) === 0)
					    {
						// There is an even number escape chars, so this is not escaped, it is the end of the string.
						$tokens[] = array(
							     "code"    => T_CONSTANT_ENCAPSED_STRING,
							     "type"    => "T_CONSTANT_ENCAPSED_STRING",
							     "content" => str_replace("\n", $eolChar, $buffer) . $char,
							    );

						Console::report("* " . _("found end of string") . " *", 2, 1);
						Console::report(
						    "=> " . _("Added token") . " T_CONSTANT_ENCAPSED_STRING" . " (" . Console::prepareForOutput($buffer . $char) . ")",
						    1, 1
						);

						$buffer          = "";
						$preStringBuffer = "";
						$inString        = "";
						$stringChar      = null;
						continue;
					    } //end if
				    }
				else if ($inString === "")
				    {
					$inString        = $char;
					$stringChar      = $i;
					$preStringBuffer = $buffer;

					Console::report("* " . _("looking for string closer") . " *", 2, 1);
				    } //end if
			    } //end if

			if ($inString !== "" && $char === "\n")
			    {
				// Unless this newline character is escaped, the string did not end before the end of the line.
				// Which means it probably wasn't a string at all (maybe a regex).
				if ($chars[($i - 1)] !== "\\")
				    {
					$i               = $stringChar;
					$buffer          = $preStringBuffer;
					$preStringBuffer = "";
					$inString        = "";
					$stringChar      = null;
					$char            = $chars[$i];

					Console::report("* " . _("found newline before end of string, bailing") . " *", 2, 1);
				    } //end if
			    } //end if

			$buffer .= $char;

			// We don't look for special tokens inside strings, so if we are in a string.
			// We can continue here now that the current char is in the buffer.
			if ($inString !== "")
			    {
				continue;
			    }

			// Special case for T_DIVIDE which can actually be the start of a regular expression.
			if ($buffer === $char && $char === "/")
			    {
				$regex = $this->getRegexToken($i, $string, $chars, $tokens, $eolChar);

				if ($regex !== null)
				    {
					$tokens[] = array(
						     "code"    => T_REGULAR_EXPRESSION,
						     "type"    => "T_REGULAR_EXPRESSION",
						     "content" => $regex["content"],
						    );

					Console::report(
					    "=> " . _("Added token") . " T_REGULAR_EXPRESSION" . " (" . Console::prepareForOutput($regex["content"]) . ")",
					    1, 1
					);

					$i           = $regex["end"];
					$buffer      = "";
					$cleanBuffer = false;
					continue;
				    } //end if
			    } //end if

			// Check for known tokens, but ignore tokens found that are not at  the end of a string, like FOR and this.FORmat.
			if (isset($this->tokenValues[strtolower($buffer)]) === true && (preg_match("|[a-zA-z0-9_]|", $char) === 0 ||
			    isset($chars[($i + 1)]) === false || preg_match("|[a-zA-z0-9_]|", $chars[($i + 1)]) === 0) )
			    {
				$matchedToken    = false;
				$lookAheadLength = ($maxTokenLength - strlen($buffer));

				if ($lookAheadLength > 0)
				    {
					// The buffer contains a token type, but we need  to look ahead at the next chars to see if this is  actually part of a larger token.
					Console::report("* " . _("buffer possibly contains token, looking ahead") . " " . $lookAheadLength . " " . _("chars") . " *", 2, 1);

					$charBuffer = $buffer;
					for ($x = 1; $x <= $lookAheadLength; $x++)
					    {
						if (isset($chars[($i + $x)]) === false)
						    {
							break;
						    }

						$charBuffer .= $chars[($i + $x)];

						Console::report(
						    "=> " . _("Looking ahead") . " " . $x . " " . _("chars") . " => " . Console::prepareForOutput($charBuffer),
						    2, 1
						);

						if (isset($this->tokenValues[strtolower($charBuffer)]) === true)
						    {
							// We've found something larger that matches  so we can ignore this char.
							// Except for 1 very specific case where a comment like /**/ needs to tokenize as  T_COMMENT and not T_DOC_COMMENT.
							$oldType = $this->tokenValues[strtolower($buffer)];
							$newType = $this->tokenValues[strtolower($charBuffer)];
							if ($oldType === "T_COMMENT" && $newType === "T_DOC_COMMENT" && $chars[($i + $x + 1)] === "/")
							    {
								Console::report("* " . _("look ahead ignored") . " T_DOC_COMMENT" . ", " . _("continuing") . " *", 2, 1);
							    }
							else
							    {
								Console::report(
								    "* " . _("look ahead found more specific token") . " (" . $newType . "), " .
								    _("ignoring") . " " . $i . " *",
								    2, 1
								);

								$matchedToken = true;
								break;
							    } //end if
						    } //end if
					    } //end for
				    } //end if

				if ($matchedToken === false)
				    {
					if ($lookAheadLength > 0)
					    {
						Console::report("* " . _("look ahead found nothing") . " *", 2, 1);
					    }

					$value    = $this->tokenValues[strtolower($buffer)];
					$tokens[] = array(
						     "code"    => constant($value),
						     "type"    => $value,
						     "content" => $buffer,
						    );

					Console::report("=> " . _("Added token") . " " . $value . " (" . Console::prepareForOutput($buffer) . ")", 1, 1);

					$cleanBuffer = true;
				    } //end if
			    }
			else if (isset($this->tokenValues[strtolower($char)]) === true)
			    {
				// No matter what token we end up using, we don't need the content in the buffer any more because we have found a valid token.
				$newContent = substr(str_replace("\n", $eolChar, $buffer), 0, -1);
				if ($newContent !== "")
				    {
					$tokens[] = array(
						     "code"    => T_STRING,
						     "type"    => "T_STRING",
						     "content" => $newContent,
						    );

					Console::report("=> " . _("Added token") . " T_STRING" . " (" . Console::prepareForOutput(substr($buffer, 0, -1)) . ")", 1, 1);
				    } //end if

				Console::report("* " . _("char is token, looking ahead") . " " . ($maxTokenLength - 1) . " " . _("chars") . " *", 2, 1);

				// The char is a token type, but we need to look ahead at the  next chars to see if this is actually part of a larger token.
				// For example, = and ===.
				$charBuffer   = $char;
				$matchedToken = false;
				for ($x = 1; $x <= $maxTokenLength; $x++)
				    {
					if (isset($chars[($i + $x)]) === false)
					    {
						break;
					    }

					$charBuffer .= $chars[($i + $x)];

					Console::report(
					    "=> " . _("Looking ahead") . " " . $x . " " . _("chars") . " => " . Console::prepareForOutput($charBuffer),
					    2, 1
					);

					if (isset($this->tokenValues[strtolower($charBuffer)]) === true)
					    {
						// We've found something larger that matches so we can ignore this char.
						Console::report(
						    "* " . _("look ahead found more specific token") . " (" . $this->tokenValues[strtolower($charBuffer)] . "), " .
						     _("ignoring") . " " . $i . " *",
						    2, 1
						);

						$matchedToken = true;
						break;
					    }
				    } //end for

				if ($matchedToken === false)
				    {
					$value    = $this->tokenValues[strtolower($char)];
					$tokens[] = array(
						     "code"    => constant($value),
						     "type"    => $value,
						     "content" => $char,
						    );

					Console::report("* " . _("look ahead found nothing") . " *", 2, 1);
					Console::report("=> " . _("Added token") . " " . $value . " (" . Console::prepareForOutput($char) . ")", 1, 1);

					$cleanBuffer = true;
				    }
				else
				    {
					$buffer = $char;
				    } //end if
			    } //end if

			// Keep track of content inside comments.
			if ($inComment === "" && array_key_exists($buffer, $this->commentTokens) === true)
			    {
				// This is not really a comment if the content looks like \// (i.e., it is escaped).
				if (isset($chars[($i - 2)]) === true && $chars[($i - 2)] === "\\")
				    {
					$lastToken   = array_pop($tokens);
					$lastContent = $lastToken["content"];
					Console::report(
					    "=> " . _("Removed token") . " " . $this->tokenValues[strtolower($lastContent)] .
					    " (" . Console::prepareForOutput($lastContent) . ")",
					    1, 1
					);

					$lastChars    = str_split($lastContent);
					$lastNumChars = count($lastChars);
					for ($x = 0; $x < $lastNumChars; $x++)
					    {
						$lastChar = $lastChars[$x];
						$value    = $this->tokenValues[strtolower($lastChar)];
						$tokens[] = array(
							     "code"    => constant($value),
							     "type"    => $value,
							     "content" => $lastChar,
							    );

						Console::report("=> " . _("Added token") . " " . $value . " (" . Console::prepareForOutput($lastChar) . ")", 1, 1);
					    }
				    }
				else
				    {
					// We have started a comment.
					$inComment = $buffer;

					Console::report("* " . _("looking for end of comment") . " *", 2, 1);
				    } //end if
			    }
			else if ($inComment !== "")
			    {
				if ($this->commentTokens[$inComment] === null)
				    {
					// Comment ends at the next newline.
					if (strpos($buffer, "\n") !== false)
					    {
						$inComment = "";
					    }
				    }
				else
				    {
					if ($this->commentTokens[$inComment] === $buffer)
					    {
						$inComment = "";
					    }
				    } //end if

				if ($inComment === "")
				    {
					Console::report("* " . _("found end of comment") . " *", 2, 1);
				    }

				if ($inComment === "" && $cleanBuffer === false)
				    {
					$tokens[] = array(
						     "code"    => T_STRING,
						     "type"    => "T_STRING",
						     "content" => str_replace("\n", $eolChar, $buffer),
						    );

					Console::report("=> " . _("Added token") . " T_STRING" . " (" . Console::prepareForOutput($buffer) . ")", 1, 1);

					$buffer = "";
				    }
			    } //end if

			if ($cleanBuffer === true)
			    {
				$buffer      = "";
				$cleanBuffer = false;
			    }
		    } //end for

		if (empty($buffer) === false)
		    {
			// Buffer contains whitespace from the end of the file.
			$tokens[] = array(
				     "code"    => T_WHITESPACE,
				     "type"    => "T_WHITESPACE",
				     "content" => str_replace("\n", $eolChar, $buffer),
				    );

			Console::report("=> " . _("Added token") . " T_WHITESPACE" . " (" . Console::prepareForOutput($buffer) . ")", 1, 1);
		    } //end if

		$tokens[] = array(
			     "code"    => T_CLOSE_TAG,
			     "type"    => "T_CLOSE_TAG",
			     "content" => "",
			    );

		// Now that we have done some basic tokenizing, we need to modify the tokens to join some together.
		// And split some apart so they match what the PHP tokenizer does.
		$finalTokens = array();
		$newStackPtr = 0;
		$numTokens   = count($tokens);
		for ($stackPtr = 0; $stackPtr < $numTokens; $stackPtr++)
		    {
			$token = $tokens[$stackPtr];

			// Look for comments and join the tokens together.
			if ($token["code"] === T_COMMENT || $token["code"] === T_DOC_COMMENT)
			    {
				$newContent   = "";
				$tokenContent = $token["content"];
				$endContent   = $this->commentTokens[$tokenContent];
				while ($tokenContent !== $endContent)
				    {
					if ($endContent === null && strpos($tokenContent, $eolChar) !== false)
					    {
						// A null end token means the comment ends at the end of the line so we look for newlines and split the token.
						$tokens[$stackPtr]["content"] = substr($tokenContent, (strpos($tokenContent, $eolChar) + strlen($eolChar)));

						$tokenContent = substr($tokenContent, 0, (strpos($tokenContent, $eolChar) + strlen($eolChar)));

						// If the substr failed, skip the token as the content will now be blank.
						if ($tokens[$stackPtr]["content"] !== false && $tokens[$stackPtr]["content"] !== "")
						    {
							$stackPtr--;
						    }

						break;
					    } //end if

					$stackPtr++;
					$newContent .= $tokenContent;
					if (isset($tokens[$stackPtr]) === false)
					    {
						break;
					    }

					$tokenContent = $tokens[$stackPtr]["content"];
				    } //end while

				if ($token["code"] === T_DOC_COMMENT)
				    {
					$commentTokens = $commentTokenizer->tokenizeString($newContent . $tokenContent, $eolChar, $newStackPtr);
					foreach ($commentTokens as $commentToken)
					    {
						$finalTokens[$newStackPtr] = $commentToken;
						$newStackPtr++;
					    }

					continue;
				    }
				else
				    {
					// Save the new content in the current token so the code below can chop it up on newlines.
					$token["content"] = $newContent . $tokenContent;
				    } //end if
			    } //end if

			// If this token has newlines in its content, split each line up and create a new token for each line.
			// We do this so it's easier to ascertain where errors occur on a line. Note that $token[1] is the token's content.
			if (strpos($token["content"], $eolChar) !== false)
			    {
				$tokenLines = explode($eolChar, $token["content"]);
				$numLines   = count($tokenLines);

				for ($i = 0; $i < $numLines; $i++)
				    {
					$newToken["content"] = $tokenLines[$i];
					if ($i === ($numLines - 1))
					    {
						if ($tokenLines[$i] === "")
						    {
							break;
						    }
					    }
					else
					    {
						$newToken["content"] .= $eolChar;
					    } //end if

					$newToken["type"]          = $token["type"];
					$newToken["code"]          = $token["code"];
					$finalTokens[$newStackPtr] = $newToken;
					$newStackPtr++;
				    } //end for
			    }
			else
			    {
				$finalTokens[$newStackPtr] = $token;
				$newStackPtr++;
			    } //end if

			// Convert numbers, including decimals.
			if ($token["code"] === T_STRING || $token["code"] === T_OBJECT_OPERATOR)
			    {
				$newContent  = "";
				$oldStackPtr = $stackPtr;
				while (preg_match("|^[0-9\.]+$|", $tokens[$stackPtr]["content"]) !== 0)
				    {
					$newContent .= $tokens[$stackPtr]["content"];
					$stackPtr++;
				    }

				if ($newContent !== "" && $newContent !== ".")
				    {
					$finalTokens[($newStackPtr - 1)]["content"] = $newContent;
					if (ctype_digit($newContent) === true)
					    {
						$finalTokens[($newStackPtr - 1)]["code"] = constant("T_LNUMBER");
						$finalTokens[($newStackPtr - 1)]["type"] = "T_LNUMBER";
					    }
					else
					    {
						$finalTokens[($newStackPtr - 1)]["code"] = constant("T_DNUMBER");
						$finalTokens[($newStackPtr - 1)]["type"] = "T_DNUMBER";
					    } //end if

					$stackPtr--;
				    }
				else
				    {
					$stackPtr = $oldStackPtr;
				    }
			    } //end if
		    } //end for

		Console::report("*** " . _("END JS TOKENIZING") . " ***", 1, 1);

		return $finalTokens;
	    } //end tokenizeString()


	/**
	 * Tokenizes a regular expression if one is found.
	 *
	 * If a regular expression is not found, NULL is returned.
	 *
	 * @param string $char    The index of the possible regex start character.
	 * @param string $string  The complete content of the string being tokenized.
	 * @param string $chars   An array of characters being tokenized.
	 * @param string $tokens  The current array of tokens found in the string.
	 * @param string $eolChar The EOL character to use for splitting strings.
	 *
	 * @return array Token
	 *
	 * @internalconst T_EQUAL               T_EQUAL token
	 * @internalconst T_OPEN_PARENTHESIS    T_OPEN_PARENTHESIS token
	 * @internalconst T_OPEN_SQUARE_BRACKET T_OPEN_SQUARE_BRACKET token
	 * @internalconst T_BITWISE_OR          T_BITWISE_OR token
	 * @internalconst T_BITWISE_AND         T_BITWISE_AND token
	 * @internalconst T_COMMA               T_COMMA token
	 * @internalconst T_COLON               T_COLON token
	 * @internalconst T_TYPEOF              T_TYPEOF token
	 */

	public function getRegexToken($char, $string, $chars, $tokens, $eolChar)
	    {
		$beforeTokens = array(
				 T_EQUAL               => true,
				 T_OPEN_PARENTHESIS    => true,
				 T_OPEN_SQUARE_BRACKET => true,
				 T_RETURN              => true,
				 T_BOOLEAN_OR          => true,
				 T_BOOLEAN_AND         => true,
				 T_BITWISE_OR          => true,
				 T_BITWISE_AND         => true,
				 T_COMMA               => true,
				 T_COLON               => true,
				 T_TYPEOF              => true,
				);

		$afterTokens = array(
				","      => true,
				")"      => true,
				"]"      => true,
				";"      => true,
				" "      => true,
				"."      => true,
				$eolChar => true,
			       );

		// Find the last non-whitespace token that was added to the tokens array.
		$numTokens = count($tokens);
		for ($prev = ($numTokens - 1); $prev >= 0; $prev--)
		    {
			if (isset(Tokens::$emptyTokens[$tokens[$prev]["code"]]) === false)
			    {
				break;
			    }
		    } //end for

		if (isset($beforeTokens[$tokens[$prev]["code"]]) === false)
		    {
			return null;
		    }

		// This is probably a regular expression, so look for the end of it.
		Console::report("* " . _("token possibly starts a regular expression") . " *", 1, 1);

		$numChars = count($chars);
		for ($next = ($char + 1); $next < $numChars; $next++)
		    {
			if ($chars[$next] === "/")
			    {
				// Just make sure this is not escaped first.
				if ($chars[($next - 1)] !== "\\")
				    {
					// In the simple form: /.../ so we found the end.
					break;
				    }
				else if ($chars[($next - 2)] === "\\")
				    {
					// In the form: /...\\/ so we found the end.
					break;
				    }
			    }
			else
			    {
				$possibleEolChar = substr($string, $next, strlen($eolChar));
				if ($possibleEolChar === $eolChar)
				    {
					// This is the last token on the line and regular expressions need to be defined on a single line, so this is not a regular expression.
					break;
				    }
			    } //end if
		    } //end for

		if ($chars[$next] !== "/")
		    {
			Console::report("* " . _("could not find end of regular expression") . " *", 1, 1);

			return null;
		    } //end if

		while (preg_match("|[a-zA-Z]|", $chars[($next + 1)]) !== 0)
		    {
			// The token directly after the end of the regex can be modifiers like global and case insensitive (.e.g, /pattern/gi).
			$next++;
		    }

		$regexEnd = $next;
		Console::report("* " . _("found end of regular expression at token") . " " . $regexEnd . " *", 1, 1);

		for ($next++; $next < $numChars; $next++)
		    {
			if ($chars[$next] !== " ")
			    {
				break;
			    }
			else
			    {
				$possibleEolChar = substr($string, $next, strlen($eolChar));
				if ($possibleEolChar === $eolChar)
				    {
					// This is the last token on the line.
					break;
				    }
			    }
		    }

		if (isset($afterTokens[$chars[$next]]) === false)
		    {
			Console::report("*" . _(" tokens after regular expression do not look correct") . " *", 1, 1);

			return null;
		    } //end if

		// This is a regular expression, so join all the tokens together.
		$content = "";
		for ($x = $char; $x <= $regexEnd; $x++)
		    {
			$content .= $chars[$x];
		    }

		$token = array(
			  "start"   => $char,
			  "end"     => $regexEnd,
			  "content" => $content,
			 );

		return $token;
	    } //end getRegexToken()


	/**
	 * Performs additional processing after main tokenizing.
	 *
	 * This additional processing looks for properties, closures, labels and objects.
	 *
	 * @param array  $tokens  The array of tokens to process.
	 * @param string $eolChar The EOL character to use for splitting strings.
	 *
	 * @return void
	 *
	 * @internalconst T_OPEN_PARENTHESIS   T_OPEN_PARENTHESIS token
	 * @internalconst T_CLOSURE            T_CLOSURE token
	 * @internalconst T_OPEN_CURLY_BRACKET T_OPEN_CURLY_BRACKET token
	 * @internalconst T_OBJECT             T_OBJECT token
	 * @internalconst T_CLOSE_OBJECT       T_CLOSE_OBJECT token
	 * @internalconst T_COLON              T_COLON token
	 * @internalconst T_INLINE_THEN        T_INLINE_THEN token
	 * @internalconst T_INLINE_ELSE        T_INLINE_ELSE token
	 * @internalconst T_PROPERTY           T_PROPERTY token
	 * @internalconst T_LABEL              T_LABEL token
	 *
	 * @untranslatable T_CLOSURE
	 * @untranslatable T_CLOSE_OBJECT
	 * @untranslatable T_OBJECT
	 * @untranslatable T_INLINE_ELSE
	 * @untranslatable T_PROPERTY
	 * @untranslatable T_STRING
	 * @untranslatable T_LABEL
	 */

	public function processAdditional(array &$tokens, $eolChar)
	    {
		unset($eolChar);

		Console::report("***" . _(" START ADDITIONAL JS PROCESSING") . " ***", 1, 1);

		$numTokens  = count($tokens);
		$classStack = array();

		for ($i = 0; $i < $numTokens; $i++)
		    {
			Console::report(
			    _("Process token ") . $i . ": " . $tokens[$i]["type"] . " => " . Console::prepareForOutput($tokens[$i]["content"]),
			    (count($classStack) + 1), 1
			);

			// Looking for functions that are actually closures.
			if ($tokens[$i]["code"] === T_FUNCTION && isset($tokens[$i]["scope_opener"]) === true)
			    {
				for ($x = ($i + 1); $x < $numTokens; $x++)
				    {
					if (isset(Tokens::$emptyTokens[$tokens[$x]["code"]]) === false)
					    {
						break;
					    }
				    } //end for

				if ($tokens[$x]["code"] === T_OPEN_PARENTHESIS)
				    {
					$tokens[$i]["code"] = T_CLOSURE;
					$tokens[$i]["type"] = "T_CLOSURE";
					Console::report(
					    "* " . _("token") . " " . $i . " " . _("on line") . " " . $tokens[$i]["line"] . " " .
					    _("changed from") . " T_FUNCTION " . _("to") . " T_CLOSURE",
					    (count($classStack) + 1), 1
					);

					for ($x = ($tokens[$i]["scope_opener"] + 1); $x < $tokens[$i]["scope_closer"]; $x++)
					    {
						if (isset($tokens[$x]["conditions"][$i]) === false)
						    {
							continue;
						    }

						$tokens[$x]["conditions"][$i] = T_CLOSURE;
						Console::report(
						    "* " . _("cleaned") . " " . $x . " (" . $tokens[$x]["type"] . ") *",
						    (count($classStack) + 2), 1
						);
					    }
				    } //end if

				continue;
			    }
			else if ($tokens[$i]["code"] === T_OPEN_CURLY_BRACKET && isset($tokens[$i]["scope_condition"]) === false)
			    {
				$classStack[] = $i;

				$closer                  = $tokens[$i]["bracket_closer"];
				$tokens[$i]["code"]      = T_OBJECT;
				$tokens[$i]["type"]      = "T_OBJECT";
				$tokens[$closer]["code"] = T_CLOSE_OBJECT;
				$tokens[$closer]["type"] = "T_CLOSE_OBJECT";

				Console::report(
				    "* " . _("token") . " " . $i . " " . _("converted from") . " T_OPEN_CURLY_BRACKET " . _("to") . " T_OBJECT" . " *",
				    (count($classStack) + 1), 1
				);
				Console::report(
				    "* " . _("token") . " " . $closer . " " . _("converted from") . " T_CLOSE_CURLY_BRACKET " . _("to") . " T_CLOSE_OBJECT" . " *",
				    (count($classStack) + 1), 1
				);

				for ($x = ($i + 1); $x < $closer; $x++)
				    {
					$tokens[$x]["conditions"][$i] = T_OBJECT;
					ksort($tokens[$x]["conditions"], SORT_NUMERIC);
					Console::report(
					    "* " . _("added") . " T_OBJECT " . _("condition to") . " " . $x . " (" . $tokens[$x]["type"] . ") *",
					    (count($classStack) + 2), 1
					);
				    }
			    }
			else if ($tokens[$i]["code"] === T_CLOSE_OBJECT)
			    {
				$opener = array_pop($classStack);
			    }
			else if ($tokens[$i]["code"] === T_COLON)
			    {
				// If it is a scope opener, it belongs to a DEFAULT or CASE statement.
				if (isset($tokens[$i]["scope_condition"]) === true)
				    {
					continue;
				    }

				// Make sure this is not part of an inline IF statement.
				for ($x = ($i - 1); $x >= 0; $x--)
				    {
					if ($tokens[$x]["code"] === T_INLINE_THEN)
					    {
						$tokens[$i]["code"] = T_INLINE_ELSE;
						$tokens[$i]["type"] = "T_INLINE_ELSE";

						Console::report(
						    "* " . _("token") . " " . $i . " " . _("converted from") . " T_COLON " . _("to") . " T_INLINE_THEN" . " *",
						    (count($classStack) + 1), 1
						);

						continue(2);
					    }
					else if ($tokens[$x]["line"] < $tokens[$i]["line"])
					    {
						break;
					    }
				    }

				// The string to the left of the colon is either a property or label.
				for ($label = ($i - 1); $label >= 0; $label--)
				    {
					if (isset(Tokens::$emptyTokens[$tokens[$label]["code"]]) === false)
					    {
						break;
					    }
				    } //end for

				if ($tokens[$label]["code"] !== T_STRING && $tokens[$label]["code"] !== T_CONSTANT_ENCAPSED_STRING)
				    {
					continue;
				    }

				if (empty($classStack) === false)
				    {
					$tokens[$label]["code"] = T_PROPERTY;
					$tokens[$label]["type"] = "T_PROPERTY";

					Console::report(
					    "* " . _("token") . " " . $label . " " . _("converted from") . " T_STRING " . _("to") . " T_PROPERTY" . " *",
					    (count($classStack) + 1), 1
					);
				    }
				else
				    {
					$tokens[$label]["code"] = T_LABEL;
					$tokens[$label]["type"] = "T_LABEL";

					Console::report(
					    "* " . _("token") . " " . $label . " " . _("converted from") . " T_STRING " . _("to") . " T_LABEL" . " *",
					    (count($classStack) + 1), 1
					);
				    } //end if
			    } //end if
		    } //end for

		Console::report("*** " . _("END ADDITIONAL JS PROCESSING") . " ***", 1, 1);
	    } //end processAdditional()


    } //end class

?>
