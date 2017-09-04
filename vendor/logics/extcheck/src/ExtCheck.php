<?php

/**
 * PHP version 5.6
 *
 * @package Logics\BuildTools\ExtCheck
 */

namespace Logics\BuildTools\ExtCheck;

use \Exception;
use \Logics\Foundation\CLI\CLI;
use \Logics\Foundation\CLI\Parser;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \ReflectionExtension;
use \stdClass;

/**
 * ExtCheck parses .php and .inc files in folders referenced by composer.json autoload and autoload-dev and looks for
 * used PHP extensions. Reports them if extension is used in PHP code but has no appropriate "require" for
 * "ext-example": "*" in composer.json
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-05 01:02:59 +0900 (Mon, 05 Sep 2016) $ $Revision: 10 $
 * @link      $HeadURL: https://open.logics.net.au/buildtools/extcheck/tags/0.1.3/src/ExtCheck.php $
 */

class ExtCheck extends CLI
    {

	/**
	 * Dictionary
	 *
	 * @var array
	 */
	private $_dictionary;

	/**
	 * Package directory
	 *
	 * @var string
	 */
	private $_packagedir;

	/**
	 * Ignored extensions
	 *
	 * @var array
	 */
	private $_ignored;

	/**
	 * Instantiate this class
	 *
	 * @return void
	 *
	 * @untranslatable Core
	 * @untranslatable date
	 * @untranslatable ereg
	 * @untranslatable pcre
	 * @untranslatable Reflection
	 * @untranslatable SPL
	 * @untranslatable standard
	 */

	public function __construct()
	    {
		$this->_dictionary = array();

		$alwayspresent = array(
				  "Core",
				  "date",
				  "ereg",
				  "pcre",
				  "Reflection",
				  "SPL",
				  "standard",
				 );

		$extensions = get_loaded_extensions();
		foreach ($extensions as $extension)
		    {
			if (in_array($extension, $alwayspresent) === false)
			    {
				$reflection = new ReflectionExtension($extension);

				$names = $reflection->getClassNames();
				foreach ($names as $name)
				    {
					$this->_dictionary[$name] = $extension;
				    }

				$names = $reflection->getFunctions();
				foreach ($names as $function)
				    {
					$this->_dictionary[$function->name] = $extension;
				    }

				$names = $reflection->getConstants();
				foreach (array_keys($names) as $name)
				    {
					$this->_dictionary[$name] = $extension;
				    }
			    } //end if
		    } //end foreach
	    } //end __construct()


	/**
	 * Register options and arguments on the given $parser object
	 *
	 * @param Parser $parser Command line parser
	 *
	 * @return void
	 *
	 * @untranslatable v*
	 * @untranslatable i|ignore+<extension>=string
	 * @untranslatable composerjson?=file/./composer.json
	 */

	protected function setup(Parser $parser)
	    {
		$parser->setIntro(_("Extension checker"));
		$parser->setHelp(_("ExtCheck parses .php and .inc files in folders referenced by composer.json autoload and autoload-dev and " .
				   "looks for used PHP extensions. Reports them if extension is used in PHP code but has no appropriate " .
				   "\"require\" for \"ext-example\": \"*\" in composer.json"));
		$parser->registerOption("v*", _("Increase verbosity level"));
		$parser->registerOption("i|ignore+<extension>=string", _("Do not check given extension"));
		$parser->registerArgument("composerjson?=file/./composer.json", _("Root composer.json file"));
	    } //end setup()


	/**
	 * Generate .config
	 *
	 * @param Parser $parser Command line parser
	 *
	 * @return void
	 *
	 * @throws Exception Unusable composer.json
	 *
	 * @exceptioncode EXCEPTION_INVALID_COMPOSER_JSON
	 * @exceptioncode EXCEPTION_CANNOT_FIND_COMPOSER_JSON
	 *
	 * @untranslatable composerjson
	 * @untranslatable i
	 * @untranslatable v
	 * @untranslatable composer.json
	 */

	public function main(Parser $parser)
	    {
		$composerjson      = realpath($parser->getArgument("composerjson"));
		$this->_ignored    = $parser->getOption("i");
		$this->_packagedir = dirname($composerjson);
		if (file_exists($composerjson) === true)
		    {
			$json = json_decode(file_get_contents($composerjson));

			if ($json !== null)
			    {
				$used = $this->_findUsedExtensions($json);
				if ($this->_checkAgainstComposerJson($used, $json, $parser->getOption("v")) === true)
				    {
					exit(1);
				    }
			    }
			else
			    {
				throw new Exception(_("Invalid") . " " . $composerjson . " " . _("file"), EXCEPTION_INVALID_COMPOSER_JSON);
			    }
		    }
		else
		    {
			throw new Exception(_("Cannot find") . " composer.json " . _("file"), EXCEPTION_CANNOT_FIND_COMPOSER_JSON);
		    }
	    } //end main()


	/**
	 * Find used extensions in the package
	 *
	 * @param stdClass $json Package composer.json
	 *
	 * @return array Used extensions
	 *
	 * @untranslatable autoload
	 * @untranslatable bin
	 * @untranslatable autoload-dev
	 */

	private function _findUsedExtensions(stdClass $json)
	    {
		$production = array_merge($this->_collectFromSection($json, "autoload"), $this->_collectFromSubsection($json, "bin"));
		$production = array_unique($production);

		$dev = $this->_collectFromSection($json, "autoload-dev");
		$dev = array_unique($dev);
		$dev = array_diff($dev, $production);

		$productionexts = $this->_checkFiles($production);
		$devexts        = $this->_checkFiles($dev);
		foreach (array_keys($productionexts) as $extension)
		    {
			if (isset($devexts[$extension]) === true)
			    {
				$productionexts[$extension] = array_merge($productionexts[$extension], $devexts[$extension]);
				unset($devexts[$extension]);
			    }
		    }

		$extensions = array(
			       "production" => $productionexts,
			       "dev"        => $devexts,
			      );

		return $extensions;
	    } //end _findUsedExtensions()


	/**
	 * Collect files identified by particular section
	 *
	 * @param stdClass $json    Package composer.json
	 * @param string   $section Section in composer.json
	 *
	 * @return array Collected files
	 *
	 * @untranslatable psr-4
	 * @untranslatable psr-0
	 * @untranslatable classmap
	 * @untranslatable files
	 */

	private function _collectFromSection(stdClass $json, $section)
	    {
		$collected = array();

		if (isset($json->{$section}) === true)
		    {
			$collected = array_merge($collected, $this->_collectFromSubsection($json->{$section}, "psr-4", $collected));
			$collected = array_merge($collected, $this->_collectFromSubsection($json->{$section}, "psr-0", $collected));
			$collected = array_merge($collected, $this->_collectFromSubsection($json->{$section}, "classmap", $collected));
			$collected = array_merge($collected, $this->_collectFromSubsection($json->{$section}, "files", $collected));

			$collected = array_unique($collected);
		    }

		return $collected;
	    } //end _collectFromSection()


	/**
	 * Coolect files from subsection
	 *
	 * @param stdClass $section    Section in composer.json
	 * @param string   $subsection Subsection name
	 *
	 * @return array Collected files
	 *
	 * @untranslatable realpath
	 */

	private function _collectFromSubsection(stdClass $section, $subsection)
	    {
		$collected = array();
		if (isset($section->{$subsection}) === true)
		    {
			foreach ($section->{$subsection} as $folders)
			    {
				$folders = (array) $folders;

				foreach ($folders as $folder)
				    {
					$folder = (($folder !== "") ? $this->_packagedir . DIRECTORY_SEPARATOR : "") . $folder;
					if (is_dir($folder) === true)
					    {
						$recursivedirectoryiterator = new RecursiveDirectoryIterator($folder);
						$iterator                   = new RecursiveIteratorIterator($recursivedirectoryiterator, RecursiveIteratorIterator::SELF_FIRST);

						$files = array();
						foreach ($iterator as $file)
						    {
							$files[] = (string) $file;
						    }

						$scripts = array_filter(
								(array) $files,
								function($file)
								    {
									return preg_match("/\.(php|inc)$/", $file) > 0;
								    }
							   );

						$collected = array_merge($collected, array_map("realpath", $scripts));
					    }
					else if (is_file($folder) === true)
					    {
						$collected[] = realpath($folder);
					    } //end if
				    } //end foreach
			    } //end foreach
		    } //end if

		return $collected;
	    } //end _collectFromSubsection()


	/**
	 * Check files for extension uses
	 *
	 * @param array $files Files to check
	 *
	 * @return array Extension uses
	 */

	private function _checkFiles(array $files)
	    {
		$extensions = array();
		foreach ($files as $file)
		    {
			$extensions = array_merge($extensions, $this->_getExtensions($file));
		    }

		return $extensions;
	    } //end _checkFiles()


	/**
	 * Get extensions uses
	 *
	 * @param string $file Source file to check
	 *
	 * @return array Extensions uses
	 *
	 * @untranslatable T_STRING
	 */

	private function _getExtensions($file)
	    {
		$tokens     = token_get_all(file_get_contents($file));
		$extensions = array();
		foreach ($tokens as $token)
		    {
			if (is_array($token) === true && token_name($token[0]) === "T_STRING" && isset($this->_dictionary[$token[1]]) === true)
			    {
				$extensions[$this->_dictionary[$token[1]]][] = array(
										"used" => $token[1],
										"file" => $file,
										"line" => $token[2],
									       );
			    }
		    } //end foreach

		return $extensions;
	    } //end _getExtensions()


	/**
	 * Check used extension against composer.json
	 *
	 * @param array    $used      Extension uses
	 * @param stdClass $json      Representation of composer.json
	 * @param int      $verbosity Verbosity level
	 *
	 * @return bool True if errors are found
	 *
	 * @untranslatable require
	 * @untranslatable suggest
	 * @untranslatable require-dev
	 */

	private function _checkAgainstComposerJson(array $used, stdClass $json, $verbosity)
	    {
		$unusedrequire    = $this->_checkAgainstSection($json, "require", $used["production"]);
		$unusedsuggest    = $this->_checkAgainstSection($json, "suggest", $used["production"]);
		$unusedrequiredev = $this->_checkAgainstSection($json, "require-dev", $used["dev"]);
		$unused           = $unusedrequire || $unusedsuggest || $unusedrequiredev;

		$this->_showMissing($used["production"], "require", $verbosity);
		$this->_showMissing($used["dev"], "require-dev", $verbosity);

		return (empty($used["production"]) === false || empty($used["dev"]) === false || $unused);
	    } //end _checkAgainstComposerJson()


	/**
	 * Check used extension against section in composer.json
	 *
	 * @param stdClass $json    Representation of composer.json
	 * @param string   $section Section of composer.json
	 * @param array    $used    Extension uses
	 *
	 * @return bool True if unused extension found in composer.json
	 *
	 * @untranslatable ext-
	 * @untranslatable composer.json
	 */

	private function _checkAgainstSection(stdClass $json, $section, array &$used)
	    {
		$unused = false;

		foreach ($this->_ignored as $extension)
		    {
			unset($used[$extension]);
		    }

		if (isset($json->{$section}) === true)
		    {
			$packages = (array) $json->{$section};
			foreach (array_keys($packages) as $package)
			    {
				if (preg_match("/^ext-(?P<name>.*)/", $package, $m) > 0)
				    {
					if (isset($used[$m["name"]]) === true || in_array($m["name"], $this->_ignored) === true)
					    {
						unset($used[$m["name"]]);
					    }
					else
					    {
						echo _("Extension") . " ext-" . $m["name"] . " " . _("mentioned in") . " " . $section . " " .
						     _("section of") . " composer.json " . _("but not used") . "\n";
						$unused = true;
					    }
				    }
			    }
		    }

		return $unused;
	    } //end _checkAgainstSection()


	/**
	 * Show missing extensions
	 *
	 * @param array  $extensions Extensions uses
	 * @param string $section    Section of composer.json
	 * @param int    $verbosity  Verbosity level
	 *
	 * @return void
	 *
	 * @untranslatable composer.json:
	 * @untranslatable \n\n
	 * @untranslatable ext-
	 */

	private function _showMissing(array $extensions, $section, $verbosity)
	    {
		if (empty($extensions) === false)
		    {
			echo _("Following extensions are missing in") . " " . $section . " " . _("section of") . " composer.json:";
			if ($verbosity === 0)
			    {
				foreach (array_keys($extensions) as $extension)
				    {
					echo " ext-" . $extension;
				    }

				echo "\n";
			    }
			else
			    {
				echo "\n\n";
				foreach ($extensions as $extension => $uses)
				    {
					echo "  ext-" . $extension . "\n";
					foreach ($uses as $used)
					    {
						echo "    " . _("Used") . " " . $used["used"] . " " . _("in") . " " . $used["file"] . ":" . $used["line"] . "\n";
					    }

					echo "\n";
				    }
			    } //end if
		    } //end if
	    } //end _showMissing()


    } //end class

?>
