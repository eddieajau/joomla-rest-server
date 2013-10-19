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
use Joomla\Database\DatabaseFactory;

/**
 * Registers the Database Driver service provider.
 *
 * Depends on the following configuration values:
 *
 * {
 *     "database" : {
 *         "driver" : "mysqli",
 *         "host" : "localhost",
 *         "username" : "username",
 *         "password" : "password",
 *         "name" : "database-name",
 *         "prefix" : "database-table-name-prefix"
 *     }
 * }
 *
 * @since  1.0
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \LogicException if the database node in the configuration has not been set.
	 */
	public function register(Container $container)
	{
		$container->share('database', function(Container $c) {

			$config = $c->get('config');

			if (null == $config->get('database'))
			{
				throw new \LogicException('Database connection not configured.', 500);
			}

			$factory = new DatabaseFactory();
			$db = $factory->getDriver(
				$config->get('database.driver'),
				array(
					'driver' => $config->get('database.driver'),
					'host' => $config->get('database.host'),
					'user' => $config->get('database.username'),
					'password' => $config->get('database.password'),
					'database' => $config->get('database.name'),
					'prefix' => $config->get('database.prefix')
				)
			);

			// Select the database.
			$db->select($config->get('database.name'));

			return $db;
		}, true);
	}
}
