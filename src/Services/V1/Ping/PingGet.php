<?php
/**
 * The Pulse application.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    MIT or LGPL.
 */

namespace Services\V1\Ping;

use Services\V1\Controller;

/**
 * Gets
 */
class PingGet extends Controller
{
	/**
	 * Executes the web service request.
	 *
	 * @return  void
	 *
	 * @since   2.54
	 */
	public function doExecute()
	{
		$app = $this->getApplication();
		$app->setBody(json_encode(array('version' => $app::VERSION)));
	}
}