<?php

/**
 * The token definitions provides extended tokens and new types of tokens to older PHP versions.
 *
 * PHP version 5
 *
 * @package Logics\BuildTools\CodeSniffer
 */

/**
 * Define extended set of tokens as internal PHP tokens are not that fine grained
 *
 * @internalconst T_COLON                  T_COLON token
 * @internalconst T_STRING_CONCAT          T_STRING_CONCAT token
 * @internalconst T_INLINE_THEN            T_INLINE_THEN token
 * @internalconst T_INLINE_ELSE            T_INLINE_ELSE token
 * @internalconst T_NULL                   T_NULL token
 * @internalconst T_FALSE                  T_FALSE token
 * @internalconst T_TRUE                   T_TRUE token
 * @internalconst T_SEMICOLON              T_SEMICOLON token
 * @internalconst T_ARRAY_HINT             T_ARRAY_HINT token
 * @internalconst T_BOOLEAN_NOT            T_BOOLEAN_NOT token
 * @internalconst T_SELF                   T_SELF token
 * @internalconst T_PARENT                 T_PARENT token
 * @internalconst T_COMMA                  T_COMMA token
 * @internalconst T_THIS                   T_THIS token
 * @internalconst T_BACKTICK               T_BACKTICK token
 * @internalconst T_OPEN_SHORT_ARRAY       T_OPEN_SHORT_ARRAY const
 * @internalconst T_CLOSE_SHORT_ARRAY      T_CLOSE_SHORT_ARRAY const
 * @internalconst T_EQUAL                  T_EQUAL token
 * @internalconst T_LESS_THAN              T_LESS_THAN token
 * @internalconst T_GREATER_THAN           T_GREATER_THAN token
 * @internalconst T_BINARY_CAST            T_BINARY_CAST token
 * @internalconst T_CLOSURE                T_CLOSURE token
 * @internalconst T_PROPERTY               T_PROPERTY token
 * @internalconst T_MINUS                  T_MINUS token
 * @internalconst T_PLUS                   T_PLUS token
 * @internalconst T_MULTIPLY               T_MULTIPLY token
 * @internalconst T_DIVIDE                 T_DIVIDE token
 * @internalconst T_MODULUS                T_MODULUS token
 * @internalconst T_POWER                  T_POWER token
 * @internalconst T_BITWISE_AND            T_BITWISE_AND token
 * @internalconst T_BITWISE_OR             T_BITWISE_OR token
 * @internalconst T_OBJECT                 T_OBJECT token
 * @internalconst T_DOC_COMMENT_STAR       T_DOC_COMMENT_STAR token
 * @internalconst T_DOC_COMMENT_WHITESPACE T_DOC_COMMENT_WHITESPACE token
 * @internalconst T_DOC_COMMENT_TAG        T_DOC_COMMENT_TAG token
 * @internalconst T_DOC_COMMENT_OPEN_TAG   T_DOC_COMMENT_OPEN_TAG token
 * @internalconst T_DOC_COMMENT_CLOSE_TAG  T_DOC_COMMENT_CLOSE_TAG token
 * @internalconst T_DOC_COMMENT_STRING     T_DOC_COMMENT_STRING token
 * @internalconst T_DOUBLE_QUOTED_STRING   T_DOUBLE_QUOTED_STRING token
 * @internalconst T_OPEN_CURLY_BRACKET     T_OPEN_CURLY_BRACKET token
 * @internalconst T_CLOSE_CURLY_BRACKET    T_CLOSE_CURLY_BRACKET token
 * @internalconst T_OPEN_SQUARE_BRACKET    T_OPEN_SQUARE_BRACKET token
 * @internalconst T_CLOSE_SQUARE_BRACKET   T_CLOSE_SQUARE_BRACKET token
 * @internalconst T_OPEN_PARENTHESIS       T_OPEN_PARENTHESIS token
 * @internalconst T_CLOSE_PARENTHESIS      T_CLOSE_PARENTHESIS token
 * @internalconst T_HEREDOC                T_HEREDOC token
 *
 * @untranslatable PHPCS_T_NONE
 * @untranslatable PHPCS_T_OPEN_CURLY_BRACKET
 * @untranslatable PHPCS_T_CLOSE_CURLY_BRACKET
 * @untranslatable PHPCS_T_OPEN_SQUARE_BRACKET
 * @untranslatable PHPCS_T_CLOSE_SQUARE_BRACKET
 * @untranslatable PHPCS_T_OPEN_PARENTHESIS
 * @untranslatable PHPCS_T_CLOSE_PARENTHESIS
 * @untranslatable PHPCS_T_COLON
 * @untranslatable PHPCS_T_STRING_CONCAT
 * @untranslatable PHPCS_T_INLINE_THEN
 * @untranslatable PHPCS_T_INLINE_ELSE
 * @untranslatable PHPCS_T_NULL
 * @untranslatable PHPCS_T_FALSE
 * @untranslatable PHPCS_T_TRUE
 * @untranslatable PHPCS_T_SEMICOLON
 * @untranslatable PHPCS_T_EQUAL
 * @untranslatable PHPCS_T_MULTIPLY
 * @untranslatable PHPCS_T_DIVIDE
 * @untranslatable PHPCS_T_PLUS
 * @untranslatable PHPCS_T_MINUS
 * @untranslatable PHPCS_T_MODULUS
 * @untranslatable PHPCS_T_POWER
 * @untranslatable PHPCS_T_BITWISE_AND
 * @untranslatable PHPCS_T_BITWISE_OR
 * @untranslatable PHPCS_T_ARRAY_HINT
 * @untranslatable PHPCS_T_GREATER_THAN
 * @untranslatable PHPCS_T_LESS_THAN
 * @untranslatable PHPCS_T_BOOLEAN_NOT
 * @untranslatable PHPCS_T_SELF
 * @untranslatable PHPCS_T_PARENT
 * @untranslatable PHPCS_T_DOUBLE_QUOTED_STRING
 * @untranslatable PHPCS_T_COMMA
 * @untranslatable PHPCS_T_HEREDOC
 * @untranslatable PHPCS_T_PROTOTYPE
 * @untranslatable PHPCS_T_THIS
 * @untranslatable PHPCS_T_REGULAR_EXPRESSION
 * @untranslatable PHPCS_T_PROPERTY
 * @untranslatable PHPCS_T_LABEL
 * @untranslatable PHPCS_T_OBJECT
 * @untranslatable PHPCS_T_CLOSE_OBJECT
 * @untranslatable PHPCS_T_COLOUR
 * @untranslatable PHPCS_T_HASH
 * @untranslatable PHPCS_T_URL
 * @untranslatable PHPCS_T_STYLE
 * @untranslatable PHPCS_T_ASPERAND
 * @untranslatable PHPCS_T_DOLLAR
 * @untranslatable PHPCS_T_TYPEOF
 * @untranslatable PHPCS_T_CLOSURE
 * @untranslatable PHPCS_T_BACKTICK
 * @untranslatable PHPCS_T_START_NOWDOC
 * @untranslatable PHPCS_T_NOWDOC
 * @untranslatable PHPCS_T_END_NOWDOC
 * @untranslatable PHPCS_T_OPEN_SHORT_ARRAY
 * @untranslatable PHPCS_T_CLOSE_SHORT_ARRAY
 * @untranslatable PHPCS_T_GOTO_LABEL
 * @untranslatable PHPCS_T_BINARY_CAST
 * @untranslatable PHPCS_T_EMBEDDED_PHP
 * @untranslatable PHPCS_T_DOC_COMMENT_STAR
 * @untranslatable PHPCS_T_DOC_COMMENT_WHITESPACE
 * @untranslatable PHPCS_T_DOC_COMMENT_TAG
 * @untranslatable PHPCS_T_DOC_COMMENT_OPEN_TAG
 * @untranslatable PHPCS_T_DOC_COMMENT_CLOSE_TAG
 * @untranslatable PHPCS_T_DOC_COMMENT_STRING
 * @untranslatable T_NAMESPACE
 * @untranslatable T_NS_SEPARATOR
 * @untranslatable T_GOTO
 * @untranslatable T_TRAIT
 * @untranslatable T_INSTEADOF
 * @untranslatable T_CALLABLE
 * @untranslatable T_FINALLY
 */

namespace Logics\BuildTools\CodeSniffer;

define("T_NONE", "PHPCS_T_NONE");
define("T_OPEN_CURLY_BRACKET", "PHPCS_T_OPEN_CURLY_BRACKET");
define("T_CLOSE_CURLY_BRACKET", "PHPCS_T_CLOSE_CURLY_BRACKET");
define("T_OPEN_SQUARE_BRACKET", "PHPCS_T_OPEN_SQUARE_BRACKET");
define("T_CLOSE_SQUARE_BRACKET", "PHPCS_T_CLOSE_SQUARE_BRACKET");
define("T_OPEN_PARENTHESIS", "PHPCS_T_OPEN_PARENTHESIS");
define("T_CLOSE_PARENTHESIS", "PHPCS_T_CLOSE_PARENTHESIS");
define("T_COLON", "PHPCS_T_COLON");
define("T_STRING_CONCAT", "PHPCS_T_STRING_CONCAT");
define("T_INLINE_THEN", "PHPCS_T_INLINE_THEN");
define("T_INLINE_ELSE", "PHPCS_T_INLINE_ELSE");
define("T_NULL", "PHPCS_T_NULL");
define("T_FALSE", "PHPCS_T_FALSE");
define("T_TRUE", "PHPCS_T_TRUE");
define("T_SEMICOLON", "PHPCS_T_SEMICOLON");
define("T_EQUAL", "PHPCS_T_EQUAL");
define("T_MULTIPLY", "PHPCS_T_MULTIPLY");
define("T_DIVIDE", "PHPCS_T_DIVIDE");
define("T_PLUS", "PHPCS_T_PLUS");
define("T_MINUS", "PHPCS_T_MINUS");
define("T_MODULUS", "PHPCS_T_MODULUS");
define("T_POWER", "PHPCS_T_POWER");
define("T_BITWISE_AND", "PHPCS_T_BITWISE_AND");
define("T_BITWISE_OR", "PHPCS_T_BITWISE_OR");
define("T_ARRAY_HINT", "PHPCS_T_ARRAY_HINT");
define("T_GREATER_THAN", "PHPCS_T_GREATER_THAN");
define("T_LESS_THAN", "PHPCS_T_LESS_THAN");
define("T_BOOLEAN_NOT", "PHPCS_T_BOOLEAN_NOT");
define("T_SELF", "PHPCS_T_SELF");
define("T_PARENT", "PHPCS_T_PARENT");
define("T_DOUBLE_QUOTED_STRING", "PHPCS_T_DOUBLE_QUOTED_STRING");
define("T_COMMA", "PHPCS_T_COMMA");
define("T_HEREDOC", "PHPCS_T_HEREDOC");
define("T_PROTOTYPE", "PHPCS_T_PROTOTYPE");
define("T_THIS", "PHPCS_T_THIS");
define("T_REGULAR_EXPRESSION", "PHPCS_T_REGULAR_EXPRESSION");
define("T_PROPERTY", "PHPCS_T_PROPERTY");
define("T_LABEL", "PHPCS_T_LABEL");
define("T_OBJECT", "PHPCS_T_OBJECT");
define("T_CLOSE_OBJECT", "PHPCS_T_CLOSE_OBJECT");
define("T_COLOUR", "PHPCS_T_COLOUR");
define("T_HASH", "PHPCS_T_HASH");
define("T_URL", "PHPCS_T_URL");
define("T_STYLE", "PHPCS_T_STYLE");
define("T_ASPERAND", "PHPCS_T_ASPERAND");
define("T_DOLLAR", "PHPCS_T_DOLLAR");
define("T_TYPEOF", "PHPCS_T_TYPEOF");
define("T_CLOSURE", "PHPCS_T_CLOSURE");
define("T_BACKTICK", "PHPCS_T_BACKTICK");
define("T_START_NOWDOC", "PHPCS_T_START_NOWDOC");
define("T_NOWDOC", "PHPCS_T_NOWDOC");
define("T_END_NOWDOC", "PHPCS_T_END_NOWDOC");
define("T_OPEN_SHORT_ARRAY", "PHPCS_T_OPEN_SHORT_ARRAY");
define("T_CLOSE_SHORT_ARRAY", "PHPCS_T_CLOSE_SHORT_ARRAY");
define("T_GOTO_LABEL", "PHPCS_T_GOTO_LABEL");
define("T_BINARY_CAST", "PHPCS_T_BINARY_CAST");
define("T_EMBEDDED_PHP", "PHPCS_T_EMBEDDED_PHP");

// Some PHP 5.3 tokens, replicated for lower versions.
if (defined("T_NAMESPACE") === false)
    {
	define("T_NAMESPACE", 1052);
    }

if (defined("T_NS_SEPARATOR") === false)
    {
	define("T_NS_SEPARATOR", 1053);
    }

if (defined("T_GOTO") === false)
    {
	define("T_GOTO", 1054);
    }

// Some PHP 5.4 tokens, replicated for lower versions.
if (defined("T_TRAIT") === false)
    {
	define("T_TRAIT", 1055);
    }

if (defined("T_INSTEADOF") === false)
    {
	define("T_INSTEADOF", 1056);
    }

if (defined("T_CALLABLE") === false)
    {
	define("T_CALLABLE", 1057);
    }

// Some PHP 5.5 tokens, replicated for lower versions.
if (defined("T_FINALLY") === false)
    {
	define("T_FINALLY", 1058);
    }

// Tokens used for parsing doc blocks.
define("T_DOC_COMMENT_STAR", "PHPCS_T_DOC_COMMENT_STAR");
define("T_DOC_COMMENT_WHITESPACE", "PHPCS_T_DOC_COMMENT_WHITESPACE");
define("T_DOC_COMMENT_TAG", "PHPCS_T_DOC_COMMENT_TAG");
define("T_DOC_COMMENT_OPEN_TAG", "PHPCS_T_DOC_COMMENT_OPEN_TAG");
define("T_DOC_COMMENT_CLOSE_TAG", "PHPCS_T_DOC_COMMENT_CLOSE_TAG");
define("T_DOC_COMMENT_STRING", "PHPCS_T_DOC_COMMENT_STRING");

/**
 * The TokenDefinitions contains weightings for tokens based on their
 * probability of occurrence in a file.
 *
 * The less the chance of a high occurrence of an arbitrary token, the higher
 * the weighting.
 *
 * Tokens are grouped together by purpose to make reference to a particular type of tokens easier.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   SVN: $Date: 2016-08-16 23:39:01 +0900 (Tue, 16 Aug 2016) $ $Revision: 26 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/codesniffer/tags/0.1.3/src/CodeSniffer/TokenDefinitions.php $
 */

trait TokenDefinitions
    {

	/**
	 * The token weightings.
	 *
	 * @var array(int => int)
	 */
	public static $weightings = array(
				     T_CLASS               => 1000,
				     T_INTERFACE           => 1000,
				     T_TRAIT               => 1000,
				     T_NAMESPACE           => 1000,
				     T_FUNCTION            => 100,
				     T_CLOSURE             => 100,

				     /*
					 Conditions.
				     */

				     T_WHILE               => 50,
				     T_FOR                 => 50,
				     T_FOREACH             => 50,
				     T_IF                  => 50,
				     T_ELSE                => 50,
				     T_ELSEIF              => 50,
				     T_WHILE               => 50,
				     T_DO                  => 50,
				     T_TRY                 => 50,
				     T_CATCH               => 50,
				     T_SWITCH              => 50,

				     T_SELF                => 25,
				     T_PARENT              => 25,

				     /*
					 Operators and arithmetic.
				     */

				     T_BITWISE_AND         => 8,
				     T_BITWISE_OR          => 8,

				     T_MULTIPLY            => 5,
				     T_DIVIDE              => 5,
				     T_PLUS                => 5,
				     T_MINUS               => 5,
				     T_MODULUS             => 5,
				     T_POWER               => 5,

				     T_EQUAL               => 5,
				     T_AND_EQUAL           => 5,
				     T_CONCAT_EQUAL        => 5,
				     T_DIV_EQUAL           => 5,
				     T_MINUS_EQUAL         => 5,
				     T_MOD_EQUAL           => 5,
				     T_MUL_EQUAL           => 5,
				     T_OR_EQUAL            => 5,
				     T_PLUS_EQUAL          => 5,
				     T_XOR_EQUAL           => 5,

				     T_BOOLEAN_AND         => 5,
				     T_BOOLEAN_OR          => 5,

				     /*
					 Equality.
				     */

				     T_IS_EQUAL            => 5,
				     T_IS_NOT_EQUAL        => 5,
				     T_IS_IDENTICAL        => 5,
				     T_IS_NOT_IDENTICAL    => 5,
				     T_IS_SMALLER_OR_EQUAL => 5,
				     T_IS_GREATER_OR_EQUAL => 5,
				    );

	/**
	 * The token weightings.
	 *
	 * @var array(int => int)
	 */
	public static $knownLengths = array(
				       T_ABSTRACT                 => 8,
				       T_AND_EQUAL                => 2,
				       T_ARRAY                    => 5,
				       T_AS                       => 2,
				       T_BOOLEAN_AND              => 2,
				       T_BOOLEAN_OR               => 2,
				       T_BREAK                    => 5,
				       T_CALLABLE                 => 8,
				       T_CASE                     => 4,
				       T_CATCH                    => 5,
				       T_CLASS                    => 5,
				       T_CLASS_C                  => 9,
				       T_CLONE                    => 5,
				       T_CONCAT_EQUAL             => 2,
				       T_CONST                    => 5,
				       T_CONTINUE                 => 8,
				       T_CURLY_OPEN               => 2,
				       T_DEC                      => 2,
				       T_DECLARE                  => 7,
				       T_DEFAULT                  => 7,
				       T_DIR                      => 7,
				       T_DIV_EQUAL                => 2,
				       T_DO                       => 2,
				       T_DOLLAR_OPEN_CURLY_BRACES => 2,
				       T_DOUBLE_ARROW             => 2,
				       T_DOUBLE_COLON             => 2,
				       T_ECHO                     => 4,
				       T_ELSE                     => 4,
				       T_ELSEIF                   => 6,
				       T_EMPTY                    => 5,
				       T_ENDDECLARE               => 10,
				       T_ENDFOR                   => 6,
				       T_ENDFOREACH               => 10,
				       T_ENDIF                    => 5,
				       T_ENDSWITCH                => 9,
				       T_ENDWHILE                 => 8,
				       T_EVAL                     => 4,
				       T_EXTENDS                  => 7,
				       T_FILE                     => 8,
				       T_FINAL                    => 5,
				       T_FINALLY                  => 7,
				       T_FOR                      => 3,
				       T_FOREACH                  => 7,
				       T_FUNCTION                 => 8,
				       T_FUNC_C                   => 12,
				       T_GLOBAL                   => 6,
				       T_GOTO                     => 4,
				       T_HALT_COMPILER            => 15,
				       T_IF                       => 2,
				       T_IMPLEMENTS               => 10,
				       T_INC                      => 2,
				       T_INCLUDE                  => 7,
				       T_INCLUDE_ONCE             => 12,
				       T_INSTANCEOF               => 10,
				       T_INSTEADOF                => 9,
				       T_INTERFACE                => 9,
				       T_ISSET                    => 5,
				       T_IS_EQUAL                 => 2,
				       T_IS_GREATER_OR_EQUAL      => 2,
				       T_IS_IDENTICAL             => 3,
				       T_IS_NOT_EQUAL             => 2,
				       T_IS_NOT_IDENTICAL         => 3,
				       T_IS_SMALLER_OR_EQUAL      => 2,
				       T_LINE                     => 8,
				       T_LIST                     => 4,
				       T_LOGICAL_AND              => 3,
				       T_LOGICAL_OR               => 2,
				       T_LOGICAL_XOR              => 3,
				       T_METHOD_C                 => 10,
				       T_MINUS_EQUAL              => 2,
				       T_MOD_EQUAL                => 2,
				       T_MUL_EQUAL                => 2,
				       T_NAMESPACE                => 9,
				       T_NS_C                     => 13,
				       T_NS_SEPARATOR             => 1,
				       T_NEW                      => 3,
				       T_OBJECT_OPERATOR          => 2,
				       T_OPEN_TAG_WITH_ECHO       => 3,
				       T_OR_EQUAL                 => 2,
				       T_PLUS_EQUAL               => 2,
				       T_PRINT                    => 5,
				       T_PRIVATE                  => 7,
				       T_PUBLIC                   => 6,
				       T_PROTECTED                => 9,
				       T_REQUIRE                  => 7,
				       T_REQUIRE_ONCE             => 12,
				       T_RETURN                   => 6,
				       T_STATIC                   => 6,
				       T_SWITCH                   => 6,
				       T_THROW                    => 5,
				       T_TRAIT                    => 5,
				       T_TRAIT_C                  => 9,
				       T_TRY                      => 3,
				       T_UNSET                    => 5,
				       T_USE                      => 3,
				       T_VAR                      => 3,
				       T_WHILE                    => 5,
				       T_XOR_EQUAL                => 2,
				       T_YIELD                    => 5,
				       T_OPEN_CURLY_BRACKET       => 1,
				       T_CLOSE_CURLY_BRACKET      => 1,
				       T_OPEN_SQUARE_BRACKET      => 1,
				       T_CLOSE_SQUARE_BRACKET     => 1,
				       T_OPEN_PARENTHESIS         => 1,
				       T_CLOSE_PARENTHESIS        => 1,
				       T_COLON                    => 1,
				       T_STRING_CONCAT            => 1,
				       T_INLINE_THEN              => 1,
				       T_INLINE_ELSE              => 1,
				       T_NULL                     => 4,
				       T_FALSE                    => 5,
				       T_TRUE                     => 4,
				       T_SEMICOLON                => 1,
				       T_EQUAL                    => 1,
				       T_MULTIPLY                 => 1,
				       T_DIVIDE                   => 1,
				       T_PLUS                     => 1,
				       T_MINUS                    => 1,
				       T_MODULUS                  => 1,
				       T_POWER                    => 1,
				       T_BITWISE_AND              => 1,
				       T_BITWISE_OR               => 1,
				       T_SL                       => 2,
				       T_SR                       => 2,
				       T_SL_EQUAL                 => 3,
				       T_SR_EQUAL                 => 3,
				       T_ARRAY_HINT               => 5,
				       T_GREATER_THAN             => 1,
				       T_LESS_THAN                => 1,
				       T_BOOLEAN_NOT              => 1,
				       T_SELF                     => 4,
				       T_PARENT                   => 6,
				       T_COMMA                    => 1,
				       T_THIS                     => 4,
				       T_CLOSURE                  => 8,
				       T_BACKTICK                 => 1,
				       T_OPEN_SHORT_ARRAY         => 1,
				       T_CLOSE_SHORT_ARRAY        => 1,
				      );

	/**
	 * Tokens that represent assignments.
	 *
	 * @var array(int)
	 */
	public static $assignmentTokens = array(
					   T_EQUAL        => T_EQUAL,
					   T_AND_EQUAL    => T_AND_EQUAL,
					   T_CONCAT_EQUAL => T_CONCAT_EQUAL,
					   T_DIV_EQUAL    => T_DIV_EQUAL,
					   T_MINUS_EQUAL  => T_MINUS_EQUAL,
					   T_MOD_EQUAL    => T_MOD_EQUAL,
					   T_MUL_EQUAL    => T_MUL_EQUAL,
					   T_PLUS_EQUAL   => T_PLUS_EQUAL,
					   T_XOR_EQUAL    => T_XOR_EQUAL,
					   T_DOUBLE_ARROW => T_DOUBLE_ARROW,
					  );

	/**
	 * Tokens that represent equality comparisons.
	 *
	 * @var array(int)
	 */
	public static $equalityTokens = array(
					 T_IS_EQUAL            => T_IS_EQUAL,
					 T_IS_NOT_EQUAL        => T_IS_NOT_EQUAL,
					 T_IS_IDENTICAL        => T_IS_IDENTICAL,
					 T_IS_NOT_IDENTICAL    => T_IS_NOT_IDENTICAL,
					 T_IS_SMALLER_OR_EQUAL => T_IS_SMALLER_OR_EQUAL,
					 T_IS_GREATER_OR_EQUAL => T_IS_GREATER_OR_EQUAL,
					);

	/**
	 * Tokens that represent comparison operator.
	 *
	 * @var array(int)
	 */
	public static $comparisonTokens = array(
					   T_IS_EQUAL            => T_IS_EQUAL,
					   T_IS_IDENTICAL        => T_IS_IDENTICAL,
					   T_IS_NOT_EQUAL        => T_IS_NOT_EQUAL,
					   T_IS_NOT_IDENTICAL    => T_IS_NOT_IDENTICAL,
					   T_LESS_THAN           => T_LESS_THAN,
					   T_GREATER_THAN        => T_GREATER_THAN,
					   T_IS_SMALLER_OR_EQUAL => T_IS_SMALLER_OR_EQUAL,
					   T_IS_GREATER_OR_EQUAL => T_IS_GREATER_OR_EQUAL,
					  );

	/**
	 * Tokens that represent arithmetic operators.
	 *
	 * @var array(int)
	 */
	public static $arithmeticTokens = array(
					   T_PLUS     => T_PLUS,
					   T_MINUS    => T_MINUS,
					   T_MULTIPLY => T_MULTIPLY,
					   T_DIVIDE   => T_DIVIDE,
					   T_MODULUS  => T_MODULUS,
					  );

	/**
	 * Tokens that represent casting.
	 *
	 * @var array(int)
	 */
	public static $castTokens = array(
				     T_INT_CAST    => T_INT_CAST,
				     T_STRING_CAST => T_STRING_CAST,
				     T_DOUBLE_CAST => T_DOUBLE_CAST,
				     T_ARRAY_CAST  => T_ARRAY_CAST,
				     T_BOOL_CAST   => T_BOOL_CAST,
				     T_OBJECT_CAST => T_OBJECT_CAST,
				     T_UNSET_CAST  => T_UNSET_CAST,
				     T_BINARY_CAST => T_BINARY_CAST,
				    );

	/**
	 * Token types that open parenthesis.
	 *
	 * @var array(int)
	 */
	public static $parenthesisOpeners = array(
					     T_ARRAY    => T_ARRAY,
					     T_FUNCTION => T_FUNCTION,
					     T_CLOSURE  => T_CLOSURE,
					     T_WHILE    => T_WHILE,
					     T_FOR      => T_FOR,
					     T_FOREACH  => T_FOREACH,
					     T_SWITCH   => T_SWITCH,
					     T_IF       => T_IF,
					     T_ELSEIF   => T_ELSEIF,
					     T_CATCH    => T_CATCH,
					    );

	/**
	 * Tokens that are allowed to open scopes.
	 *
	 * @var array(int)
	 */
	public static $scopeOpeners = array(
				       T_CLASS     => T_CLASS,
				       T_INTERFACE => T_INTERFACE,
				       T_TRAIT     => T_TRAIT,
				       T_NAMESPACE => T_NAMESPACE,
				       T_FUNCTION  => T_FUNCTION,
				       T_CLOSURE   => T_CLOSURE,
				       T_IF        => T_IF,
				       T_SWITCH    => T_SWITCH,
				       T_CASE      => T_CASE,
				       T_DEFAULT   => T_DEFAULT,
				       T_WHILE     => T_WHILE,
				       T_ELSE      => T_ELSE,
				       T_ELSEIF    => T_ELSEIF,
				       T_FOR       => T_FOR,
				       T_FOREACH   => T_FOREACH,
				       T_DO        => T_DO,
				       T_TRY       => T_TRY,
				       T_CATCH     => T_CATCH,
				       T_FINALLY   => T_FINALLY,
				       T_PROPERTY  => T_PROPERTY,
				       T_OBJECT    => T_OBJECT,
				       T_USE       => T_USE,
				      );

	/**
	 * Tokens that represent scope modifiers.
	 *
	 * @var array(int)
	 */
	public static $scopeModifiers = array(
					 T_PRIVATE   => T_PRIVATE,
					 T_PUBLIC    => T_PUBLIC,
					 T_PROTECTED => T_PROTECTED,
					);

	/**
	 * Tokens that can prefix a method name
	 *
	 * @var array(int)
	 */
	public static $methodPrefixes = array(
					 T_PRIVATE   => T_PRIVATE,
					 T_PUBLIC    => T_PUBLIC,
					 T_PROTECTED => T_PROTECTED,
					 T_ABSTRACT  => T_ABSTRACT,
					 T_STATIC    => T_STATIC,
					 T_FINAL     => T_FINAL,
					);

	/**
	 * Tokens that perform operations.
	 *
	 * @var array(int)
	 */
	public static $operators = array(
				    T_MINUS       => T_MINUS,
				    T_PLUS        => T_PLUS,
				    T_MULTIPLY    => T_MULTIPLY,
				    T_DIVIDE      => T_DIVIDE,
				    T_MODULUS     => T_MODULUS,
				    T_POWER       => T_POWER,
				    T_BITWISE_AND => T_BITWISE_AND,
				    T_BITWISE_OR  => T_BITWISE_OR,
				    T_SL          => T_SL,
				    T_SR          => T_SR,
				   );

	/**
	 * Tokens that perform boolean operations.
	 *
	 * @var array(int)
	 */
	public static $booleanOperators = array(
					   T_BOOLEAN_AND => T_BOOLEAN_AND,
					   T_BOOLEAN_OR  => T_BOOLEAN_OR,
					   T_LOGICAL_AND => T_LOGICAL_AND,
					   T_LOGICAL_OR  => T_LOGICAL_OR,
					   T_LOGICAL_XOR => T_LOGICAL_XOR,
					  );

	/**
	 * Tokens that open code blocks.
	 *
	 * @var array(int)
	 */
	public static $blockOpeners = array(
				       T_OPEN_CURLY_BRACKET  => T_OPEN_CURLY_BRACKET,
				       T_OPEN_SQUARE_BRACKET => T_OPEN_SQUARE_BRACKET,
				       T_OPEN_PARENTHESIS    => T_OPEN_PARENTHESIS,
				       T_OBJECT              => T_OBJECT,
				      );

	/**
	 * Tokens that don't represent code.
	 *
	 * @var array(int)
	 */
	public static $emptyTokens = array(
				      T_WHITESPACE             => T_WHITESPACE,
				      T_COMMENT                => T_COMMENT,
				      T_DOC_COMMENT            => T_DOC_COMMENT,
				      T_DOC_COMMENT_STAR       => T_DOC_COMMENT_STAR,
				      T_DOC_COMMENT_WHITESPACE => T_DOC_COMMENT_WHITESPACE,
				      T_DOC_COMMENT_TAG        => T_DOC_COMMENT_TAG,
				      T_DOC_COMMENT_OPEN_TAG   => T_DOC_COMMENT_OPEN_TAG,
				      T_DOC_COMMENT_CLOSE_TAG  => T_DOC_COMMENT_CLOSE_TAG,
				      T_DOC_COMMENT_STRING     => T_DOC_COMMENT_STRING,
				     );

	/**
	 * Tokens that are comments.
	 *
	 * @var array(int)
	 */
	public static $commentTokens = array(
					T_COMMENT                => T_COMMENT,
					T_DOC_COMMENT            => T_DOC_COMMENT,
					T_DOC_COMMENT_STAR       => T_DOC_COMMENT_STAR,
					T_DOC_COMMENT_WHITESPACE => T_DOC_COMMENT_WHITESPACE,
					T_DOC_COMMENT_TAG        => T_DOC_COMMENT_TAG,
					T_DOC_COMMENT_OPEN_TAG   => T_DOC_COMMENT_OPEN_TAG,
					T_DOC_COMMENT_CLOSE_TAG  => T_DOC_COMMENT_CLOSE_TAG,
					T_DOC_COMMENT_STRING     => T_DOC_COMMENT_STRING,
				       );

	/**
	 * Tokens that represent strings.
	 *
	 * Note that T_STRINGS are NOT represented in this list.
	 *
	 * @var array(int)
	 */
	public static $stringTokens = array(
				       T_CONSTANT_ENCAPSED_STRING => T_CONSTANT_ENCAPSED_STRING,
				       T_DOUBLE_QUOTED_STRING     => T_DOUBLE_QUOTED_STRING,
				      );

	/**
	 * Tokens that represent brackets and parenthesis.
	 *
	 * @var array(int)
	 */
	public static $bracketTokens = array(
					T_OPEN_CURLY_BRACKET   => T_OPEN_CURLY_BRACKET,
					T_CLOSE_CURLY_BRACKET  => T_CLOSE_CURLY_BRACKET,
					T_OPEN_SQUARE_BRACKET  => T_OPEN_SQUARE_BRACKET,
					T_CLOSE_SQUARE_BRACKET => T_CLOSE_SQUARE_BRACKET,
					T_OPEN_PARENTHESIS     => T_OPEN_PARENTHESIS,
					T_CLOSE_PARENTHESIS    => T_CLOSE_PARENTHESIS,
				       );

	/**
	 * Tokens that include files.
	 *
	 * @var array(int)
	 */
	public static $includeTokens = array(
					T_REQUIRE_ONCE => T_REQUIRE_ONCE,
					T_REQUIRE      => T_REQUIRE,
					T_INCLUDE_ONCE => T_INCLUDE_ONCE,
					T_INCLUDE      => T_INCLUDE,
				       );

	/**
	 * Tokens that make up a heredoc string.
	 *
	 * @var array(int)
	 */
	public static $heredocTokens = array(
					T_START_HEREDOC => T_START_HEREDOC,
					T_END_HEREDOC   => T_END_HEREDOC,
					T_HEREDOC       => T_HEREDOC,
				       );

	/**
	 * Tokens that represent the names of called functions.
	 *
	 * Mostly, these are just strings. But PHP tokenizes some language
	 * constructs and functions using their own tokens.
	 *
	 * @var array(int)
	 */
	public static $functionNameTokens = array(
					     T_STRING       => T_STRING,
					     T_EVAL         => T_EVAL,
					     T_EXIT         => T_EXIT,
					     T_INCLUDE      => T_INCLUDE,
					     T_INCLUDE_ONCE => T_INCLUDE_ONCE,
					     T_REQUIRE      => T_REQUIRE,
					     T_REQUIRE_ONCE => T_REQUIRE_ONCE,
					     T_ISSET        => T_ISSET,
					     T_UNSET        => T_UNSET,
					     T_EMPTY        => T_EMPTY,
					    );


    } //end trait

?>
