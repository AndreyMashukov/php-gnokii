<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests
 */

namespace Logics\Tests;

use \DateTime;
use \DateTimeZone;
use \Exception;
use \Logics\Foundation\DAO\DAO;
use \Logics\Foundation\Init\Init;
use \Logics\Foundation\Queue\Deliver;
use \Logics\Foundation\Queue\Queue;
use \Logics\Foundation\Queue\QueueParallelHandler;
use \Logics\Foundation\Queue\QueueRegistry;
use \Logics\Foundation\Queue\QueueRegistryGetters;
use \ReflectionClass;
use \ReflectionProperty;

/**
 * QueueDevelopmentTools trait
 *
 * Tools for queue invokation in tests
 *
 * @author    Vladimir Skorov <voroks@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-21 00:00:17 +0800 (Sat, 21 Jan 2017) $ $Revision: 270 $
 * @link      $HeadURL: https://open.logics.net.au/phpunit-extensions/tags/0.2.5/QueueDevelopmentTools.php $
 *
 * @codeCoverageIgnore
 *
 * @donottranslate
 */

trait QueueDevelopmentTools
    {

	/**
	 * Adding needed folder to include_path
	 * Copying sample config file to service folder
	 * Register queues
	 *
	 * @param string $serviceRootDir Path to actual service folder
	 *
	 * @return void
	 */

	public function setUpQueue($serviceRootDir)
	    {
		$class = new ReflectionClass(__CLASS__);
		$dir   = dirname($class->getFileName());

		ini_set("include_path", get_include_path() . ":" . $dir . ":" . $serviceRootDir);

		QueueRegistry::doNotUseSupervisor();

		Init::application();

		$this->clearQueues();
	    } //end setUpQueue()


	/**
	 * Clear registered queues and remove config file from service folder
	 *
	 * @return void
	 */

	public function tearDownQueue()
	    {
		$dao = DAO::get("queue");

		$tablesList = array(
			       "qDischargingTime",
			       "qHandlingTime",
			       "qQueuesCommunication",
			       "qQueuesLog",
			       "qQueuesMeta",
			       "qServicesMeta",
			       "qDynamicQueuesParameters",
			       "qAddressing",
			       "qPolicy",
			       "wsAddressing",
			      );

		foreach ($tablesList as $tableName)
		    {
			$dao->dropTable($tableName);
		    }

		$query = $dao->showTables();
		foreach ($query as $row)
		    {
			if ((substr($row["table"], 0, 6) === "qQueue") &&
			    ((substr($row["table"], -4) === "Data") ||
			    (substr($row["table"], -7) === "Details")))
			    {
				$dao->dropTable($row["table"]);
			    }
		    }
	    } //end tearDownQueue()


	/**
	 * Prepare instance of queue
	 *
	 * @param mixed $queue Name of queue or queue instance to clear
	 *
	 * @return Queue
	 *
	 * @throws Exception When obtain errors while checking queue
	 */

	private function _checkQueueInstanceQDT($queue)
	    {
		if (($queue instanceof Queue) === false)
		    {
			if (is_string($queue) === true)
			    {
				$queue = new Queue($queue);
			    }
			else
			    {
				throw new Exception(_("Please specify name of queue or queue instance"), 0);
			    }
		    }

		return $queue;
	    } //end _checkQueueInstanceQDT()


	/**
	 * Clear regisered queue
	 *
	 * @param mixed $queue Name of queue or queue instance to clear
	 *
	 * @return void
	 */

	private function _clearQueueQDT($queue)
	    {
		$queue = $this->_checkQueueInstanceQDT($queue);
		foreach ($queue as $data)
		    {
			$queue->remove($data->hash);
		    }
	    } //end _clearQueueQDT()


	/**
	 * Clear all regisered queues
	 *
	 * @param mixed $queues List of queues to clear
	 *
	 * @return void
	 */

	public function clearQueues($queues = false)
	    {
		$queuesList = QueueRegistryGetters::getAllQueuesParameters();
		if ($queues === false)
		    {
			foreach ($queuesList as $parameters)
			    {
				$this->_clearQueueQDT($parameters["queueName"]);
			    }
		    }
		else
		    {
			if (is_array($queues) === false)
			    {
				$queues = array($queues);
			    }

			foreach ($queues as $queue)
			    {
				$this->_clearQueueQDT($queue);
			    }
		    }
	    } //end clearQueues()


	/**
	 * Invoke queue to test handle class
	 *
	 * @param mixed $queue        Name of queue or queue instance: "queueName"
	 * @param mixed $sampleData   Data to store: "data"
	 * @param mixed $customSender List of type and host of sender service:
	 *                             array("type" => "host")
	 *                             or
	 *                             array(
	 *                              "type1" => array(
	 *                                "host1",
	 *                                "host2",
	 *                              ),
	 *                              "type2" => "host3",
	 *                             )
	 *
	 * @return Queue
	 */

	public function invokeQueue($queue, $sampleData, $customSender = false)
	    {
		QueueRegistryGetters::loadDynamicQueues();

		$friendsTypes = array();
		$dataDetails  = array();

		if ($customSender !== false)
		    {
			$dataDetails = array();
			foreach ($customSender as $type => $hosts)
			    {
				$friendsTypes[] = $type;

				$dataDetails[$type][Deliver::TO_HOST] = $hosts;
			    }
		    }

		if (is_array($sampleData) === false)
		    {
			$sampleData = array($sampleData);
		    }

		$queueName = $queue;

		$queue  = $this->_checkQueueInstanceQDT($queue);
		$status = true;
		foreach ($sampleData as $data)
		    {
			$status = $status && $queue->push($data, $friendsTypes, $dataDetails);
		    }

		$this->assertTrue($status);

		$this->dischargeQueues($queueName);

		return $queue;
	    } //end invokeQueue()


	/**
	 * Discharge all registered queues
	 *
	 * @param mixed $queues List of queues to discharge
	 *
	 * @return void
	 */

	public function dischargeQueues($queues = array())
	    {
		$queues = (array) $queues;

		$allAvailableQueuesParameters     = QueueRegistryGetters::getAllQueuesParameters();
		$queueParallelHandlerForSerialRun = new QueueParallelHandler($allAvailableQueuesParameters);
		while ($args = $queueParallelHandlerForSerialRun->factory())
		    {
			$discharge = true;
			if (count($queues) !== 0)
			    {
				$discharge = in_array($args[3]["queueName"], $queues, true);
			    }

			if ($discharge === true)
			    {
				list($entryHandler, $exitHandler, , $parameters) = $args;
				$result = call_user_func($entryHandler, $parameters);
				call_user_func($exitHandler, $parameters, $result);
			    }
		    }
	    } //end dischargeQueues()


	/**
	 * Inject friend service parameters to existed queue
	 *
	 * @param string $queueName      Name of queue
	 * @param array  $friendServices Friend services parameters
	 *
	 * @return void
	 */

	public function injectCommunications($queueName, array $friendServices)
	    {
		$dao = DAO::get("queue");

		$parameters = QueueRegistryGetters::getQueueParameters($queueName);

		foreach ($friendServices as $hosts)
		    {
			if (is_array($hosts) === false)
			    {
				$hosts = array($hosts);
			    }

			$defaultTimeZone = new DateTimeZone("UTC");
			$defaultDateTime = DateTime::createFromFormat("Y-m-d H:i:s", "0001-01-01 00:00:00", $defaultTimeZone);

			foreach ($hosts as $host)
			    {
				$dao->insertIntoQueuesCommunicationByWSA(
				    $parameters["queueID"],
				    -1,
				    $host,
				    "username",
				    "password",
				    true,
				    $defaultDateTime->format("Y-m-d H:i:s")
				);
			    }
		    } //end foreach

		include "manifest/queues.php";
	    } //end injectCommunications()


	/**
	 * Redefine handling class
	 *
	 * @param string $queueName Name of queue
	 * @param mixed  $classes   Array, name or instance of class
	 * @param mixed  $db        DB to operate
	 *
	 * @return void
	 */

	public function redefineHandlingClass($queueName, $classes, $db = false)
	    {
		$registryReflection = new ReflectionProperty(QueueRegistry::CLASS, "_instance");
		$registryReflection->setAccessible(true);
		$registryHandler = $registryReflection->getValue();

		list($serviceID, $queueID) = $registryHandler->queuesNames[$queueName];
		$classManager              = $registryHandler->queuesParameters[$serviceID][$queueID]["classManager"];

		$classesReflection = new ReflectionProperty($classManager, "_classes");
		$classesReflection->setAccessible(true);
		$classesReflection->setValue($classManager, $classes);

		if ($db !== false)
		    {
			$classesReflection = new ReflectionProperty($classManager, "_db");
			$classesReflection->setAccessible(true);
			$classesReflection->setValue($classManager, $db);
		    }
	    } //end redefineHandlingClass()


    } //end trait

?>
