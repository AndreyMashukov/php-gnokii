<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \DOMDocument;
use \DOMNode;
use \Exception;
use \Logics\Foundation\VisualXMLEditor\VisualXMLEditorInstance;
use \Logics\Foundation\XML\DOMXPathExtended;

/**
 * VXEDevelopmentTools trait
 *
 * Tools for testing VXE web-forms.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-09-06 23:08:32 +0900 (Tue, 06 Sep 2016) $ $Revision: 236 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/VXEDevelopmentTools.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

trait VXEDevelopmentTools
    {

	/**
	 * Array of hash mapping
	 *
	 * @var array
	 */
	protected $hashMappingArray = array();

	/**
	 * Instance ID
	 *
	 * @var string
	 */
	protected $instanceid;

	/**
	 * Acts like web-browser at the web-form pre-submit data collection.
	 *
	 * @param string $html             Source HTML document
	 * @param array  $replacementarray Changes that have to be done to the data array
	 *
	 * @return array
	 *
	 * @throws Exception VXE instance ID is empty
	 *
	 * @exceptioncode EXCEPTION_VXE_INSTANCE_ID_IS_EMPTY
	 */

	protected function getFormData($html, array $replacementarray = array())
	    {
		if (empty($this->instanceid) === true)
		    {
			throw new Exception(
			    _("VisualXMLEditor instance id can not be empty, please set the 'instanceid' property before using this method"),
			    EXCEPTION_VXE_INSTANCE_ID_IS_EMPTY
			);
		    }

		$document = new DOMDocument("1.0", "utf-8");
		$document->loadHTML($html);
		$formdocumentxpath = new DOMXPathExtended($document);

		$dataarray    = array();
		$datacarriers = $formdocumentxpath->query("//input | //select | //textarea");
		foreach ($datacarriers as $datacarrier)
		    {
			if (empty($datacarrier->getAttribute("name")) === false)
			    {
				$value = $this->_getValue($datacarrier, $formdocumentxpath);
				if ($value !== false)
				    {
					$dataarray[$datacarrier->getAttribute("name")] = $value;
				    }
			    } //end if
		    } //end foreach

		foreach ($replacementarray as $key => $value)
		    {
			$dataarray[$this->instanceid . "[" . $this->encodeKey($key) . "]"] = $value;
		    } //end foreach

		return $dataarray;
	    } //end getFormData()


	/**
	 * Extracts the value from the element datacarrier
	 *
	 * @param DOMNode          $datacarrier       Node with data
	 * @param DOMXPathExtended $formdocumentxpath XPath for the form document
	 *
	 * @return string
	 */

	private function _getValue(DOMNode $datacarrier, DOMXPathExtended $formdocumentxpath)
	    {
		$value = false;
		if ($datacarrier->nodeName === "input")
		    {
			if ($datacarrier->getAttribute("type") === "checkbox" && empty($datacarrier->getAttribute("checked")) === true)
			    {
				$valuenode = $formdocumentxpath->query("//input[@type=\"hidden\" and @name=\"" . $datacarrier->getAttribute("name") . "\"]");
				$value     = $valuenode->item(0)->getAttribute("value");
			    }
			else
			    {
				$value = $datacarrier->getAttribute("value");
			    }
		    }
		else if ($datacarrier->nodeName === "select")
		    {
			$options = $datacarrier->childNodes;
			foreach ($options as $option)
			    {
				if (empty($option->getAttribute("selected")) === false)
				    {
					$value = $option->getAttribute("value");
				    } //end if
			    } //end foreach
		    }
		else if ($datacarrier->nodeName === "textarea")
		    {
			$value = $datacarrier->nodeValue;
		    } //end if

		return $value;
	    } //end _getValue()


	/**
	 * Encodes key according to rules
	 *
	 * @param string $key Key that needs to be encoded
	 *
	 * @return string
	 */

	protected function encodeKey($key)
	    {
		$encodedkey = $key;
		if ($key !== "rootselector")
		    {
			$encodedkey = "vxe" . md5($key);
		    } //end if

		$this->hashMappingArray[$encodedkey] = $key;
		return $encodedkey;
	    } //end encodeKey()


	/**
	 * Decodes key according to rules
	 *
	 * @param string $encodedkey Key that needs to be decoded, can be in the INSTANCEID[HASHEDNAMETRACE] form
	 *
	 * @return string
	 *
	 * @throws Exception VXE instance ID is empty
	 *
	 * @exceptioncode EXCEPTION_VXE_INSTANCE_ID_IS_EMPTY
	 */

	protected function decodeKey($encodedkey)
	    {
		if (empty($this->instanceid) === true)
		    {
			throw new Exception(
			    _("VisualXMLEditor instance id can not be empty, please set the 'instanceid' property before using this method"),
			    EXCEPTION_VXE_INSTANCE_ID_IS_EMPTY
			);
		    }

		$result = false;
		if (preg_match("/^\w*\[(?P<indexedname>\w*)\]$/", $encodedkey, $matches) > 0)
		    {
			$encodedkey = $matches["indexedname"];
		    }

		if (class_exists("VisualXMLEditorHashMapper") === false)
		    {
			include "visualxmleditor/utilities/VisualXMLEditorHashMapper.php";
		    }

		if (isset($_SESSION[$this->instanceid]["storedcontrolstructures"]["hashmapper"]) === true)
		    {
			$hashmapper = $_SESSION[$this->instanceid]["storedcontrolstructures"]["hashmapper"];
			if ($hashmapper->translateSingle($encodedkey) !== false)
			    {
				$result = $hashmapper->translateSingle($encodedkey);
			    }
		    }

		if ($result !== false)
		    {
			return $result;
		    }
		else if (array_key_exists($encodedkey, $this->hashMappingArray) === true)
		    {
			return $this->hashMappingArray[$encodedkey];
		    }
		else
		    {
			throw new Exception("Unable to resolve key " . $encodedkey, 0);
		    } //end if
	    } //end decodeKey()


	/**
	 * Update current VisualXMLEditor instance form.
	 *
	 * @param VisualXMLEditorInstance $visualxmleditor  VisualXMLEditor instance
	 * @param array                   $inputdata        Input data
	 * @param string                  $rootindex        Optional root index
	 * @param string                  $partialnametrace Partial nametrace that specifies when iteration should stop
	 *
	 * @return VisualXMLEditorInstance
	 */

	protected function updateInstance(VisualXMLEditorInstance $visualxmleditor, array $inputdata = array(), $rootindex = false, $partialnametrace = false)
	    {
		$formdata         = $this->getFormData($visualxmleditor->form(), $inputdata);
		$transformeddata  = $this->prepareData($formdata);
		$requestvalidator = $this->getRequestValidator($transformeddata);
		$visualxmleditor->update($requestvalidator, $rootindex, $partialnametrace);

		return $visualxmleditor;
	    } //end updateInstance()


	/**
	 * Initializes instance of RequestValidator in order to pass it to VisualXMLEditor form() method.
	 *
	 * @param array $params Data that should be substituted as $_POST input
	 *
	 * @return RequestValidator
	 *
	 * @throws Exception Only strings are valid as parameters for RequestValidator
	 */

	protected function getRequestValidator(array $params)
	    {
		$s = "";
		foreach ($params as $key => $value)
		    {
			if (is_string($value) === false)
			    {
				throw new Exception("Only strings may be used as request validator parameters: " . $key . " is not a string", 0);
			    }

			$s .= (($s !== "") ? "&" : "") . $key . "=" . urlencode($value);
		    }

		parse_str($s, $parsed);

		$GLOBALS["_POST"] = $parsed;
		$requestvalidator = new RequestValidator();
		return $requestvalidator;
	    } //end getRequestValidator()


	/**
	 * Transforms plain data array as it would look like after the submition of web-form.
	 *
	 * @param array $formdata Data array - key / value pairs
	 *
	 * @return array
	 */

	protected function prepareData(array $formdata)
	    {
		$finaldata = array();
		foreach ($formdata as $key => $value)
		    {
			$hashednametrace = substr($key, (strpos($key, "[") + 1), (strpos($key, "]") - strpos($key, "[") - 1));

			$finaldata[$this->instanceid . "[" . $hashednametrace . "]"] = (string) $value;
		    } //end foreach

		return $finaldata;
	    } //end prepareData()


	/**
	 * Converting value arrays to proper string format.
	 *
	 * @param array $formdata Data, fetched from the form
	 *
	 * @return array
	 */

	protected function prepareFormData(array $formdata)
	    {
		foreach ($formdata as $formdatakey => $formdatavalue)
		    {
			if (is_array($formdatavalue) === true)
			    {
				unset($formdata[$formdatakey]);
				$numberofvalues = count($formdatavalue);
				for ($i = 0; $i < $numberofvalues; $i++)
				    {
					$formdata[str_replace("[]", "[" . $i . "]", $formdatakey)] = $formdatavalue[$i];
				    }
			    }
		    }

		return $formdata;
	    } //end prepareFormData()


    } //end trait

?>
