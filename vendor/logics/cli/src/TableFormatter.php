<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\CLI
 */

namespace Logics\Foundation\CLI;

use \Exception;

/**
 * Class TableFormatter
 *
 * Output text in multiple columns
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 23:19:29 +0930 (Wed, 17 Aug 2016) $ $Revision: 63 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/CLI/trunk/src/CLI.php $
 */

class TableFormatter
    {

	const DEFAULT_WIDTH = 80;

	/**
	 * The terminal width
	 *
	 * @var int
	 */
	private $_width;

	/**
	 * Table margin
	 *
	 * @var int
	 */
	private $_margin;

	/**
	 * Columns description
	 *
	 * @var array
	 */
	private $_columns;

	/**
	 * Separator between columns
	 *
	 * @var string
	 */
	private $_separator;

	/**
	 * Calculated fixed column sizes
	 *
	 * @var array
	 */
	private $_fixed;

	/**
	 * TableFormatter constructor.
	 *
	 * @param array  $columns   Columns description
	 * @param int    $width     Terminal width
	 * @param int    $margin    Table margin
	 * @param string $separator Columns separator
	 *
	 * @return void
	 *
	 * @untranslatable tput cols 2>/dev/null
	 * @untranslatable tput cols
	 */

	public function __construct(array $columns, $width = 0, $margin = 0, $separator = " ")
	    {
		if ($width === 0)
		    {
			$test = exec("tput cols 2>/dev/null");
			if (empty($test) === false)
			    {
				$width = intval(exec("tput cols"));
			    }
		    }

		$this->_width  = ($width === 0) ? self::DEFAULT_WIDTH : $width;
		$this->_margin = $margin;
		$this->setColumns($columns);
		$this->_separator = $separator;
		$this->_calculateFixedColumns();
	    } //end __construct()


	/**
	 * Set the width of the terminal to assume
	 *
	 * @param int $width Terminal width width
	 *
	 * @return void
	 */

	public function setWidth($width)
	    {
		$this->_witdh = ($width === 0) ? self::DEFAULT_WIDTH : $width;
		$this->_calculateFixedColumns();
	    } //end setWidth()


	/**
	 * Set the table margin
	 *
	 * @param int $margin Table margin
	 *
	 * @return void
	 */

	public function setMargin($margin)
	    {
		$this->_margin = $margin;
		$this->_calculateFixedColumns();
	    } //end setMargin()


	/**
	 * Set the columns widths
	 *
	 * @param array $columns Columns description
	 *
	 * @return void
	 *
	 * @throws Exception Columns sizes must be specified
	 *
	 * @exceptioncode EXCEPTION_COLUMN_SIZES_MUST_BE_SPECIFIED
	 */

	public function setColumns(array $columns)
	    {
		if (empty($columns) === true)
		    {
			throw new Exception(_("Columns sizes must be specified"), EXCEPTION_COLUMN_SIZES_MUST_BE_SPECIFIED);
		    }

		$this->_columns = array_values($columns);
		$this->_calculateFixedColumns();
	    } //end setColumns()


	/**
	 * Set the separator. The separator used between each column. Its width is
	 * added to the column widths.
	 *
	 * @param string $separator Separator
	 *
	 * @return void
	 */

	public function setSeparator($separator)
	    {
		$this->_separator = $separator;
		$this->_calculateFixedColumns();
	    } //end setSeparator()


	/**
	 * Takes an array with columns description, terminal width, table margin and separator and then calculates the columns fixed width
	 *
	 * Column width can be given as fixed char width, percentage and a single * width can be given
	 * for taking the remaining available space. When mixing percentages and fixed widths, percentages
	 * refer to the remaining space after allocating the fixed width
	 *
	 * @return void
	 *
	 * @throws Exception Cannot lay out columns
	 *
	 * @exceptioncode EXCEPTION_ONLY_ONE_FLUID_COLUMN_ALLOWED
	 * @exceptioncode EXCEPTION_UNKNOWN_COLUMN_FORMAT
	 * @exceptioncode EXCEPTION_CANNOT_FIT
	 */

	private function _calculateFixedColumns()
	    {
		$this->_fixed = array();
		$allocated    = (((count($this->_columns) - 1) * Colorize::strlen($this->_separator)) + $this->_margin);
		$fluid        = false;

		foreach ($this->_columns as $idx => $col)
		    {
			if ((string) intval($col) === (string) $col)
			    {
				$allocated         += $col;
				$this->_fixed[$idx] = intval($col);
			    }
			else if (preg_match("/^((?P<max>\d+)<)?(?P<percent>\d+)%$/", $col) !== 1)
			    {
				if ($col === "*")
				    {
					if ($fluid === false)
					    {
						$fluid = $idx;
					    }
					else
					    {
						throw new Exception(_("Only one fluid column allowed"), EXCEPTION_ONLY_ONE_FLUID_COLUMN_ALLOWED);
					    }
				    }
				else
				    {
					throw new Exception(_("Unknown column format ") . $col, EXCEPTION_UNKNOWN_COLUMN_FORMAT);
				    }
			    } //end if
		    } //end foreach

		$remainder = ($this->_width - $allocated);

		foreach ($this->_columns as $idx => $col)
		    {
			if (preg_match("/^((?P<max>\d+)<)?(?P<percent>\d+)%$/", $col, $m) === 1)
			    {
				$width              = (int) floor((floatval($m["percent"]) * $remainder) / 100);
				$max                = (isset($m["max"]) === true && $m["max"] !== "") ? (int) $m["max"] : $width;
				$width              = min($max, $width);
				$this->_fixed[$idx] = $width;
				$allocated         += $width;
			    }
		    }

		$remainder = ($this->_width - $allocated);
		if ($remainder < 0)
		    {
			throw new Exception(_("Wanted column widths exceed available space"), EXCEPTION_CANNOT_FIT);
		    }

		if ($fluid === false)
		    {
			$this->_fixed[$idx] += $remainder;
		    }
		else
		    {
			$this->_fixed[$fluid] = $remainder;
		    }
	    } //end _calculateFixedColumns()


	/**
	 * Make table
	 *
	 * @param array $lines   Array of table rows
	 * @param int   $spacing Vertical spacing between rows
	 *
	 * @return string Formatted table
	 */

	public function table(array $lines, $spacing = 0)
	    {
		$out = "";
		foreach ($lines as $line)
		    {
			$out .= $this->format($line);
			for ($i = 0; $i++; $i < $spacing)
			    {
				$out .= $this->format(array());
			    }
		    }

		return $out;
	    } //end table()


	/**
	 * Displays text in multiple word wrapped columns
	 *
	 * @param array $texts List of texts for each column
	 *
	 * @return string
	 */

	public function format(array $texts)
	    {
		$texts     = array_values($texts);
		$wrapped   = array();
		$maxheight = 0;

		foreach ($this->_fixed as $col => $width)
		    {
			$text          = (isset($texts[$col]) === true) ? $texts[$col] : "";
			$wrapped[$col] = explode("\n", $this->_wordwrap($text, $width));
			$maxheight     = max(count($wrapped[$col]), $maxheight);
		    }

		$last = (count($this->_fixed) - 1);
		$out  = "";
		for ($i = 0; $i < $maxheight; $i++)
		    {
			$spacer = "";
			$out   .= str_repeat(" ", $this->_margin);
			foreach ($this->_fixed as $col => $width)
			    {
				$chunk = (isset($wrapped[$col][$i]) === true) ? $wrapped[$col][$i] : "";
				if ($chunk !== "")
				    {
					$out   .= $spacer;
					$spacer = "";
				    }

				$out .= $chunk;

				if ($col !== $last)
				    {
					$spacer .= str_repeat(" ", max(0, ($width - Colorize::strlen($chunk))));
					$spacer .= $this->_separator;
				    }
			    } //end foreach

			$out .= "\n";
		    } //end for

		return $out;
	    } //end format()


	/**
	 * Multibyte and color safe word wrap
	 *
	 * @param string $str   Text to wrap
	 * @param string $width Text width
	 *
	 * @return string
	 */

	private function _wordwrap($str, $width)
	    {
		$break = "\n";
		$lines = explode($break, $str);
		foreach ($lines as &$line)
		    {
			$line = rtrim($line);
			if (Colorize::strlen($line) > $width)
			    {
				$words  = explode(" ", $line);
				$line   = "";
				$actual = "";
				foreach ($words as $word)
				    {
					$currentcolor = Colorize::getCurrentColor($actual);
					if (Colorize::strlen($actual . $word) <= $width)
					    {
						$actual .= $word . " ";
					    }
					else
					    {
						if ($actual !== "")
						    {
							$line .= Colorize::addReset(rtrim($actual), $currentcolor) . $break;
						    }

						$actual = Colorize::addColor($word, $currentcolor);
						$length = Colorize::strlen($actual);
						while ($length > $width)
						    {
							$chunk        = Colorize::substr($actual, 0, $width);
							$currentcolor = Colorize::getCurrentColor($chunk);
							$line        .= Colorize::addReset($chunk, $currentcolor) . $break;
							$actual       = Colorize::addColor(Colorize::substr($actual, $width), $currentcolor);
							$length       = Colorize::strlen($actual);
						    }

						$actual .= " ";
					    } //end if
				    } //end foreach

				$line .= trim($actual);
			    } //end if

			$currentcolor = Colorize::getCurrentColor($line);
			$line         = Colorize::addReset($line, $currentcolor);
		    } //end foreach

		return implode($break, $lines);
	    } //end _wordwrap()


    } //end class

?>
