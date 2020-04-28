<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Request;

use Joomla\Filter\InputFilter;

/**
 * Simple registry holding key/value pairs of request data
 *
 * @since  __DEPLOY_VERSION__
 */
class Registry implements \Countable
{
	/**
	 * Object to use when filtering request data
	 *
	 * @var    InputFilter
	 * @since  __DEPLOY_VERSION__
	 */
	protected $filter;

	/**
	 * Parameter storage
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $parameters;

	/**
	 * Registry constructor
	 *
	 * @param   InputFilter  $filter      An InputFilter instance to use for filtering the request data
	 * @param   array        $parameters  The parameters to store in this registry
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(InputFilter $filter, array $parameters = [])
	{
		$this->filter     = $filter;
		$this->parameters = $parameters;
	}

	/**
	 * Count elements of the data object
	 *
	 * @return  integer
	 *
	 * @link    https://www.php.net/manual/en/countable.count.php
	 * @since   __DEPLOY_VERSION__
	 */
	public function count(): int
	{
		return count($this->all());
	}

	/**
	 * Get the parameters of this registry
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all(): array
	{
		return $this->parameters;
	}

	/**
	 * Add parameters to the registry, replacing existing parameters
	 *
	 * @param   array  $parameters  The parameters to add to this registry
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function add(array $parameters = []): void
	{
		$this->parameters = array_replace($this->parameters, $parameters);
	}

	/**
	 * Check if a parameter key exists
	 *
	 * @param   string  $key  The parameter key to check
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function exists(string $key): bool
	{
		return array_key_exists($key, $this->all());
	}

	/**
	 * Get the parameter's value with the specified filter applied
	 *
	 * @param   string  $key      The parameter key to retrieve
	 * @param   mixed   $default  Optional default value, returned if the parameter is not set
	 * @param   string  $filter   Filter to apply to the value
	 *
	 * @return  mixed
	 *
	 * @see     \Joomla\Filter\InputFilter::clean()
	 * @since   __DEPLOY_VERSION__
	 */
	public function filter(string $key, $default = null, string $filter = 'cmd')
	{
		return $this->filter->clean($this->get($key, $default), $filter);
	}

	/**
	 * Get the parameter's unfiltered value if it is defined
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
		return $this->parameters[$key] ?? $default;
	}

	/**
	 * Remove a parameter from the registry
	 *
	 * @param   string  $key  The parameter key to remove
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove(string $key): void
	{
		unset($this->parameters[$key]);
	}

	/**
	 * Set the parameter's value
	 *
	 * @param   string  $key    The parameter key to set
	 * @param   mixed   $value  The value of the parameter
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set(string $key, $value): void
	{
		$this->parameters[$key] = $value;
	}
}
