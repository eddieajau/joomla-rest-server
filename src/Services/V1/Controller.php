<?php
/**
 * The Pulse application.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Services\V1;

use Joomla\Controller\AbstractController;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Gets
 */
abstract class Controller extends AbstractController implements LoggerAwareInterface,
	ContainerAwareInterface
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   2.0
	 */
	public function execute()
	{
		// Do some logging?

		$this->doExecute();

		// Do some logging?
	}

	/**
	 * Get the DI container.
	 *
	 * @return  Di\Container
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException if the container has not been set.
	 */
	public function getContainer()
	{
		if ($this->container)
		{
			return $this->container;
		}

		throw new \UnexpectedValueException('Container not set in ' . __CLASS__);
	}

	/**
	 * Get the logger.
	 *
	 * @return  Log\LoggerInterface
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException if the logger has not been set.
	 */
	public function getLogger()
	{
		if ($this->logger)
		{
			return $this->logger;
		}

		throw new \UnexpectedValueException('Logger not set in ' . __CLASS__);
	}

	/**
	 * Set the DI container.
	 *
	 * @param   Di\Container  $container  The DI container.
	 *
	 * @return  Controller  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * Set the logger.
	 *
	 * @param   Log\LoggerInterface  $logger  The logger.
	 *
	 * @return  Controller  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Inner execution handler for the controller.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected abstract function doExecute();
}