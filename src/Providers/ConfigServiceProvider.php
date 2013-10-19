<?php
/**
 * Configuration service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * Registers the Configuration service provider.
 *
 * Note that the application requires the `PULSE_CONFIG` constant to be set with the path to the JSON configuration file.
 *
 * @since  1.0
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \LogicException if the PULSE_CONFIG constant is not defined.
	 */
	public function register(Container $container)
	{
		$container->share('config', function(Container $c) {

			if (!defined('APPLICATION_CONFIG'))
			{
				throw new \LogicException('Application configuration path not defined.', 500);
			}

			$json = json_decode(file_get_contents(APPLICATION_CONFIG));

			if (null === $json)
			{
				throw new \UnexpectedValueException('Configuration file could not be parsed.', 500);
			}

			$config = new Registry(json_decode(file_get_contents(APPLICATION_CONFIG)));

			return $config;
		}, true);
	}
}
