<?php
/**
 * The Pulse application.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Server;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Web\WebClient;
use Joomla\DI\Container;
use Joomla\Http;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Router\RestRouter;
use Joomla\DI\ContainerAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Joomla\Data\DataObject;
use Joomla\Event\Dispatcher;
use Joomla\Event\Event;

/**
 * The Pulse application class.
 *
 * @since  1.0
 */
class Application extends AbstractWebApplication
{
	/**
	 * The name of the applciation.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const NAME = 'Joomla Rest Server';

	/**
	 * The application version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const VERSION = '1.0';

	/**
	 * Response mime type.  By default this application returns JSON.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $mimeType = 'application/json';

	/**
	 * The application's DI container.
	 *
	 * @var    Di\Container
	 * @since  1.0
	 */
	private $container;

	/**
	 * The application's dispatcher
	 *
	 * @var    Dispatcher
	 * @since  1.0
	 */
	private $dispatcher;

	/**
	 * A router object for the application to use.
	 *
	 * @var    RestRouter
	 * @since  1.0
	 */
	private $router;

	/**
	 * The start time for measuring the execution time.
	 *
	 * @var    float
	 * @since  1.0
	 */
	private $startTime;

	/**
	 * The application's vendor string for the Accept header.
	 *
	 * @var    float
	 * @since  1.0
	 */
	private $vendor = 'vnd.joomla-rest-server';

	/**
	 * Class constructor.
	 *
	 * @param   Input      $input   An optional argument to provide dependency injection for the application's
	 *                              input object.  If the argument is a Input object that object will become
	 *                              the application's input object, otherwise a default input object is created.
	 * @param   Registry   $config  An optional argument to provide dependency injection for the application's
	 *                              config object.  If the argument is a Registry object that object will become
	 *                              the application's config object, otherwise a default config object is created.
	 * @param   WebClient  $client  An optional argument to provide dependency injection for the application's
	 *                              client object.  If the argument is a Web\WebClient object that object will become
	 *                              the application's client object, otherwise a default client object is created.
	 *
	 * @since   1.0
	 */
	public function __construct(Input $input = null, Registry $config = null, WebClient $client = null)
	{
		$this->startTime = microtime(true);

		parent::__construct($input, $config, $client);
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		// Trigger onBeforeExecute event.
		$event = new Event('onBeforeExecute');
		$event->addArgument('app', $this);
		$this->dispatcher->triggerEvent($event);

		try
		{
			$controller = $this->router->getController($this->get('uri.route'));
			$controller->setApplication($this);

			if ($controller instanceof ContainerAwareInterface)
			{
				$controller->setContainer($this->container);
			}

			if ($controller instanceof LoggerAwareInterface)
			{
				$controller->setLogger($this->getLogger());
			}

			$controller->execute();
		}
		catch (\Exception $e)
		{
			$debug = $this->get('debug');
			$body = new \stdClass;
			$body->message = $e->getMessage();
			$body->code = $e->getCode();
			$body->type = get_class($e);
			$body->trace = array();

			if ($debug)
			{
				foreach ($e->getTrace() as $i => $trace)
				{
					$body->trace[] = sprintf(
						'%2d. %s %s:%d',
						$i + 1,
						$trace['function'],
						str_ireplace(array(dirname(__DIR__), dirname(dirname(__DIR__))), '', $trace['file']),
						$trace['line']
					);
				}
			}

			$this->setHeader('status', '400', true);
			$this->setBody(json_encode($body));
		}

		// Trigger onAfterExecute event.
		$event = new Event('onAfterExecute');
		$event->addArgument('app', $this);
		$this->dispatcher->triggerEvent($event);
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the AbstractApplication::__construct method. This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function initialise()
	{
		$this->dispatcher = new Dispatcher;

		// New DI stuff!
		$container = new Container;
		$input = $this->input;

		$container->share('input', function (Container $c) use ($input) {
			return $input;
		}, true);

		$container->registerServiceProvider(new \Providers\ConfigServiceProvider);
		$container->registerServiceProvider(new \Providers\DatabaseServiceProvider);
		$container->registerServiceProvider(new \Providers\LoggerServiceProvider);

		$this->container = $container;

		$this->setConfiguration($container->get('config'));
		$this->setLogger($container->get('logger'));

		// Determine the API version.
		$apiVersion = 1;
		$httpAccept = new Http\Accept($this->input->get->getString('_accept', null));
		$media = $httpAccept->getTypes('application/' . $this->vendor);

		if (!empty($media))
		{
			$media = array_shift($media);
			preg_match('#^' . $this->vendor . '\.v(\d+)#i', $media->subType, $m);

			if (isset($m[1]))
			{
				$apiVersion = $m[1];
			}
		}

		$this->router = new RestRouter;
		$this->router->setControllerPrefix('\\Services\\V' . $apiVersion . '\\')
			->setDefaultController('')
			->addMaps(json_decode(file_get_contents(APPLICATION_ROUTES), true));
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main
	 * application output data.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function respond()
	{
		// Trigger onBeforeRespond event.
		$event = new Event('onBeforeRespond');
		$event->addArgument('app', $this);
		$this->dispatcher->triggerEvent($event);

		// Stop the timer.
		$runtime = microtime(true) - $this->startTime;

		// Send the content-type header.
		$this->setHeader('Content-Type', $this->mimeType . '; charset=' . $this->charSet);

		// Set the Server and X-Powered-By Header.
		$this->setHeader('Server', '', true);
		$this->setHeader('X-Powered-By', self::NAME . '/' . self::VERSION, true);
		$this->setHeader('X-Runtime', $runtime, true);

		// Send the response.
		$this->sendHeaders();

		$meta = new DataObject;

		foreach ($this->getHeaders() as $header)
		{
			if (strtolower($header['name'][0]) == 'x' || strtolower($header['name']) == 'status')
			{
				$meta->{$header['name']} = $header['value'];
			}
		}

		$callback = $this->input->get->getCmd('callback');

		// TODO - Do this a better way.
		$apiType = 'json';

		if ($apiType == 'json' && $callback)
		{
			echo sprintf(
				'%s({"meta":%s,"data":%s})',
				$callback,
				$meta,
				$this->getBody()
			);
		}
		else
		{
			echo $this->getBody();
		}

		// Trigger onAfterRespond event.
		$event = new Event('onAfterRespond');
		$event->addArgument('app', $this);
		$this->dispatcher->triggerEvent($event);
	}
}
