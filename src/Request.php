<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Application\Request\HeaderRegistry;
use Joomla\Application\Request\Registry;
use Joomla\Application\Request\ServerRegistry;
use Joomla\Filter\InputFilter;

/**
 * Class representing a HTTP request.
 *
 * @since  __DEPLOY_VERSION__
 *
 * @property-read  Registry        $query       Registry containing the query string parameters ($_GET data)
 * @property-read  Registry        $request     Registry containing the request body parameters ($_POST data)
 * @property-read  Registry        $attributes  Registry containing miscellaneous request attributes
 * @property-read  Registry        $cookies     Registry containing the request's cookies ($_COOKIE data)
 * @property-read  ServerRegistry  $server      Registry containing the request's server and environment information ($_SERVER data)
 * @property-read  HeaderRegistry  $headers     Registry containing the request's headers
 */
class Request
{
	/**
	 * Object to use when filtering request data
	 *
	 * @var    InputFilter
	 * @since  __DEPLOY_VERSION__
	 */
	protected $filter;

	/**
	 * Registry containing the query string parameters ($_GET data)
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	protected $query;

	/**
	 * Registry containing the request body parameters ($_POST data)
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	protected $request;

	/**
	 * Registry containing miscellaneous request attributes
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	protected $attributes;

	/**
	 * Registry containing the request's cookies ($_COOKIE data)
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	protected $cookies;

	/**
	 * Registry containing the request's server and environment information ($_SERVER data)
	 *
	 * @var    ServerRegistry
	 * @since  __DEPLOY_VERSION__
	 */
	protected $server;

	/**
	 * Registry containing the request's headers
	 *
	 * @var    HeaderRegistry
	 * @since  __DEPLOY_VERSION__
	 */
	protected $headers;

	/**
	 * Request constructor
	 *
	 * @param   InputFilter           $filter      An InputFilter instance to use for filtering the request data
	 * @param   array                 $query       The GET parameters
	 * @param   array                 $request     The POST parameters
	 * @param   array                 $attributes  Miscellaneous request attributes
	 * @param   array                 $cookies     The COOKIE parameters
	 * @param   array                 $files       The FILES parameters
	 * @param   array                 $server      The SERVER parameters
	 * @param   string|resource|null  $content     The raw body data
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(
		InputFilter $filter,
		array $query = [],
		array $request = [],
		array $attributes = [],
		array $cookies = [],
		array $files = [],
		array $server = [],
		$content = null
	)
	{
		$this->filter     = $filter;
		$this->query      = new Registry($filter, $query);
		$this->request    = new Registry($filter, $request);
		$this->attributes = new Registry($filter, $attributes);
		$this->cookies    = new Registry($filter, $cookies);
		//$this->files      = new FileRegistry($files);
		$this->server     = new ServerRegistry($filter, $server);
		$this->headers    = new HeaderRegistry($filter, $this->server->getHeaders());
		$this->content    = $content;
	}

	/**
	 * Create a new Request instance based on the values of the PHP super globals
	 *
	 * @param   InputFilter  $filter  An InputFilter instance to use for filtering the request data
	 *
	 * @return  Request
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function createFromGlobals(?InputFilter $filter = null): self
	{
		$request = new static($filter ?: new InputFilter, $_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);

		return $request;
	}

	/**
	 * Magic method to allow read access to the request properties
	 *
	 * @param   mixed  $name  Name of the property to access
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \LogicException on unauthorized read access to a protected property
	 */
	public function __get($name)
	{
		// Explicit whitelist to restrict read access
		if (in_array($name, ['query', 'request', 'attributes', 'cookies', 'server', 'headers']))
		{
			return $this->$name;
		}

		// If the class property is set, warn about unauthorized read access
		if (isset($this->$name))
		{
			throw new \LogicException(
				sprintf(
					'Read access to %s::$%s is not allowed',
					get_class($this),
					$name
				)
			);
		}

		// General undefined property notice
		$trace = debug_backtrace();
		trigger_error(
			sprintf(
				'Undefined property via __get(): %s in %s on line %s',
				$name,
				$trace[0]['file'],
				$trace[0]['line']
			),
			E_USER_NOTICE
		);
	}

	/**
	 * Gets a filtered parameter value from any registry source
	 *
	 * This is mainly useful for consumers who are not concerned with the explicit source of the data (generally the same
	 * as reading from $_REQUEST), but can also take into account custom request attributes.  The order of precedence for
	 * this method is the custom attributes, $_GET, and $_POST data.
	 *
	 * @param   string  $key      The parameter key to retrieve
	 * @param   mixed   $default  Optional default value, returned if the parameter is not set
	 * @param   string  $filter   Filter to apply to the value
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function filter(string $key, $default = null, string $filter = 'cmd')
	{
		return $this->filter->clean($this->get($key, $default), $filter);
	}

	/**
	 * Gets an unfiltered parameter value from any registry source
	 *
	 * This is mainly useful for consumers who are not concerned with the explicit source of the data (generally the same
	 * as reading from $_REQUEST), but can also take into account custom request attributes.  The order of precedence for
	 * this method is the custom attributes, $_GET, and $_POST data.
	 *
	 * @param   string  $key      The parameter key to retrieve
	 * @param   mixed   $default  Optional default value, returned if the parameter is not set
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get(string $key, $default = null)
	{
		$result = $this->attributes->get($key, $this);

		if ($result !== $this)
		{
			return $result;
		}

		$result = $this->query->get($key, $this);

		if ($result !== $this)
		{
			return $result;
		}

		$result = $this->request->get($key, $this);

		if ($result !== $this)
		{
			return $result;
		}

		return $default;
	}
}
