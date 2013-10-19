<?php
/**
 * @package    Datahub\Services\Tests
 * @copyright  Copyright 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later. See LISENCE
 */

namespace Joomla\Http\Accept\Tests;

use Joomla\Http\Accept;
use Joomla\Test\TestHelper;

/**
 * Tests for the Joomla\Http\Accept class.
 *
 * @package  Datahub\Services\Tests
 * @since    1.0
 */
class AcceptTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * An instance of the class to test.
	 *
	 * @var    Accept
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Seeds data for the testParseType method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestParseType()
	{
		return array(
			array('text/html', 'text', 'html'),
			array('audio/*; q=0.2, audio/basic', 'audio', '*', '', '0.2'),
			array('text/html;level=1', 'text', 'html', '', ''),
			array('application/vnd.vendor+json', 'application', 'vnd.vendor', 'json'),
		);
	}

	/**
	 * Tests the Joomla\Http\Accept::getRawTypes method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\Accept::getRawTypes
	 * @since   1.0
	 */
	public function testGetRawTypes()
	{
		$types = $this->instance->getRawTypes();
		$this->assertContains('text/html', $types);
		$this->assertContains('application/vnd.vendor+json', array_keys($types));
		$this->assertContains('*/*', $types);
	}

	/**
	 * Tests the Joomla\Http\Accept::getTypes method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Http\Accept::getTypes
	 * @since   1.0
	 */
	public function testGetTypes()
	{
		$types = $this->instance->getTypes();
		$this->assertContains('text/html', array_keys($types));
		$this->assertContains('application/vnd.vendor+json', array_keys($types));
		$this->assertContains('*/*', array_keys($types));

		$types = $this->instance->getTypes('application/vnd');
		$this->assertCount(1, $types);
		$this->assertContains('application/vnd.vendor+json', array_keys($types));
	}

	/**
	 * Tests the Joomla\Http\Accept::parseType method.
	 *
	 * @param   string  $input    The input media type.
	 * @param   string  $type     The expected type.
	 * @param   string  $subType  The expected type.
	 * @param   string  $mime     The expected mime.
	 * @param   string  $q        The expected q.
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Http\Accept::parseType
	 * @dataProvider  seedTestParseType
	 * @since         1.0
	 */
	public function testParseType($input, $type, $subType, $mime = '', $q = '')
	{
		$result = TestHelper::invoke($this->instance, 'parseType', $input);
		$this->assertEquals($type, $result->type);
		$this->assertEquals($subType, $result->subType);
		$this->assertEquals($mime, $result->mimeType);
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Accept('text/html, application/vnd.vendor+json, */*');
	}
}
