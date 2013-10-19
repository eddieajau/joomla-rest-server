<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * Class to parse the Accept headers in a request.
 *
 * @link   http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 * @link   http://www.ietf.org/rfc/rfc3023.txt
 * @since  1.0
 */
class Accept
{
	/**
	 * The original header.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $header;

	/**
	 * The media types parsed from the Accept header.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $types;

	/**
	 * Class constructor.
	 *
	 * @param   string  $header  An optional header string to inject into the class.
	 *
	 * @since   1.0
	 */
	public function __construct($header = null)
	{
		$this->header = $header ? $header : (isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '');
		$this->parse();
	}

	/**
	 * Gets and array of the raw media types in the Accept header.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getRawTypes()
	{
		return array_keys($this->types);
	}

	/**
	 * Get a list of all media types in the Accept header or search for types that match a regular expression.
	 *
	 * Example:
	 * <code>use Joomla\Http\Accept;
	 * $accept = new Http\Accept;
	 * $accept->getTypes('application/vnd');</code>
	 *
	 * @param   string  $regex  An optional regular expression search pattern.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getTypes($regex = null)
	{
		if ($regex)
		{
			$keys = preg_grep("#$regex#i", array_keys($this->types));

			return array_intersect_key($this->types, array_flip($keys));
		}
		else
		{
			return $this->types;
		}
	}

	/**
	 * Parses the Accept header.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function parse()
	{
		foreach (preg_split('/\s*,\s*/', $this->header) as $type)
		{
			$this->types[$type] = $this->parseType($type);
		}
	}

	/**
	 * Parses a single media type in the Accept header.
	 *
	 * @param   string  $type  The media type to parse.
	 *
	 * @return  \stdClass
	 *
	 * @since   1.0
	 */
	private function parseType($type)
	{
		$accept = array();

		$result = new \stdClass;

		if (preg_match('#^(\S+)\s*;\s*(?:q|level)=([0-9\.]+)#i', $type, $parts))
		{
			$result->media = $parts[1];
			$result->q = (double) $parts[2];
		}
		else
		{
			$result->media = $type;
			$result->q = 1;
		}

		// Matching (type)/(subtype)+(mime)
		preg_match('#([^\/]+)/([^\+]+)\+?(.*)#i', $result->media, $parts);

		$result->type = isset($parts[1]) ? $parts[1] : '';
		$result->subType = isset($parts[2]) ? $parts[2] : '';
		$result->mimeType = isset($parts[3]) ? $parts[3] : '';

		return $result;
	}
}
