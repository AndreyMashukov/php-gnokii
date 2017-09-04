<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

require_once "AbstractChecker.php";

/**
 * Class redefines tmp folder
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 22:45:36 +0900 (Wed, 17 Aug 2016) $ $Revision: 232 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/environment/TemporaryDirectoryChecker.php $
 *
 * @donottranslate
 */

class TemporaryDirectoryChecker extends AbstractChecker
    {
	const DEFAULT_TMP = "/tmp";

	const DEFAULT_SLASH = "/";

	/**
	 * Temporary directory name
	 *
	 * @var string
	 */
	private $_tmpDirectoryName;

	/**
	 * Warnings
	 *
	 * @var array
	 */
	private $_warnings;

	/**
	 * Initialize private variables
	 *
	 * @return void
	 */

	public function __construct()
	    {
		parent::__construct();

		$this->_tmpDirectoryName = false;
		$this->_warnings         = array();
	    } //end __construct()


	/**
	 * Get default tmp directory name
	 *
	 * @return string
	 */

	private function _getSystemTmp()
	    {
		$possibleTmp = ini_get("sys_temp_dir");
		if (($possibleTmp === false) || ($possibleTmp === ""))
		    {
			$possibleTmp = getenv("TMPDIR");
			if ($possibleTmp === false)
			    {
				$possibleTmp = self::DEFAULT_TMP;
			    }
		    }

		if (substr($possibleTmp, -1) === self::DEFAULT_SLASH)
		    {
			$possibleTmp = substr($possibleTmp, 0, -1);
		    }

		return $possibleTmp;
	    } //end _getSystemTmp()


	/**
	 * Check existence of tmp folder, it's owner, mode etc
	 *
	 * @param string $userTmp   Expected tmp directory name
	 * @param int    $userID    Current system user ID
	 * @param int    $iteration Counter of reqursion calls of function
	 *
	 * @return string
	 */

	private function _checkTmpDir($userTmp, $userID, $iteration = 0)
	    {
		if ($iteration !== 0)
		    {
			$userTmp = sprintf("%s%06d", $userTmp, $iteration);
		    }

		if (file_exists($userTmp) === true)
		    {
			if (is_dir($userTmp) === true)
			    {
				if (fileowner($userTmp) === $userID)
				    {
					if (0750 !== (fileperms($userTmp) & 0777))
					    {
						if (chmod($userTmp, 0750) === false)
						    {
							$this->_warnings[] = "chmod for directory '" . $userTmp . "' returns false";

							$userTmp = $this->_checkTmpDir($userTmp, $userID, ++$iteration);
						    }
					    }
				    }
				else
				    {
					$this->_warnings[] = "'" . $userTmp . "' has another owner";

					$userTmp = $this->_checkTmpDir($userTmp, $userID, ++$iteration);
				    }
			    }
			else
			    {
				$this->_warnings[] = "'" . $userTmp . "' is a file not a directory";

				$userTmp = $this->_checkTmpDir($userTmp, $userID, ++$iteration);
			    } //end if
		    }
		else
		    {
			if (mkdir($userTmp, 0750, true) === false)
			    {
				$this->_warnings[] = "mkdir for directory '" . $userTmp . "' returns false";

				$userTmp = $this->_checkTmpDir($userTmp, $userID, ++$iteration);
			    }
		    } //end if

		return $userTmp;
	    } //end _checkTmpDir()


	/**
	 * Attempts to change system tmp directory name to depending on user directory name
	 *
	 * @return bool
	 */

	public function setUp()
	    {
		if ($this->getRequestedUserName() === false)
		    {
			$userID   = $this->getUserID();
			$userName = $this->getUserName();
			if ($userName !== false)
			    {
				$tmpSystemDirectoryName = $this->_getSystemTmp();
				if (substr($tmpSystemDirectoryName, (-1 * strlen($userName))) !== $userName)
				    {
					$tmpUserDirectoryName = $tmpSystemDirectoryName . "/" . $userName;
					$tmpDirectoryName     = $this->_checkTmpDir($tmpUserDirectoryName, $userID);

					if (putenv("TMPDIR=" . $tmpDirectoryName) === true)
					    {
						if (sys_get_temp_dir() === $tmpDirectoryName)
						    {
							$this->_tmpDirectoryName = $tmpDirectoryName;
							return true;
						    }
						else
						    {
							$this->_warnings[] = "wanted and real temporary directory names are not equals";
							return false;
						    }
					    }
					else
					    {
						$this->_warnings[] = "'putenv' returns false";
						return false;
					    }
				    } //end if
			    }
			else
			    {
				$this->_warnings[] = "user name for current user id not found";
				return false;
			    } //end if
		    } //end if
	    } //end setUp()


	/**
	 * Display all warnings collected while setting up
	 *
	 * @return void
	 */

	public function displayWarnings()
	    {
		if (count($this->_warnings) !== 0)
		    {
			echo "Warnings while setting up temporary directory:\n";
			foreach ($this->_warnings as $warning)
			    {
				echo " - " . $warning . ";\n";
			    }
		    }
	    } //end displayWarnings()


	/**
	 * Display information about tmp directory name
	 *
	 * @return void
	 */

	public function displayTemporaryDirectoryName()
	    {
		$tmpDirectoryNameToPrint = "";
		if ($this->_tmpDirectoryName !== false)
		    {
			$tmpDirectoryNameToPrint = $this->_tmpDirectoryName;
		    }
		else
		    {
			$tmpDirectoryNameToPrint = sys_get_temp_dir();
		    }

		echo "Your temporary directory name: " . $tmpDirectoryNameToPrint . "\n";
	    } //end displayTemporaryDirectoryName()


    } //end class

?>
