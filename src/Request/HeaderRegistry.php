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
 * Registry holding a request's header data
 *
 * @since  __DEPLOY_VERSION__
 */
class HeaderRegistry implements \Countable
{
	/**
	 * Object to use when filtering request data
	 *
	 * @var    InputFilter
	 * @since  __DEPLOY_VERSION__
	 */
	protected $filter;

	/**
	 * Header storage
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $headers;

	/**
	 * Registry constructor
	 *
	 * @param   InputFilter  $filter   An InputFilter instance to use for filtering the request data
	 * @param   array        $headers  The headers to store in this registry
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(InputFilter $filter, array $headers = [])
	{
		$this->filter = $filter;

		$this->add($headers);
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
	 * Get the headers of this registry
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all(): array
	{
		return $this->headers;
	}

	/**
	 * Add headers to the registry, replacing existing headers
	 *
	 * @param   array  $headers  The headers to add to this registry
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function add(array $headers = []): void
	{
		foreach ($headers as $key => $value)
		{
			$this->set($key, $value);
		}
	}

	/**
	 * Check if a header exists
	 *
	 * @param   string  $key  The header to check
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function exists(string $key): bool
	{
		return array_key_exists($this->formatKey($key), $this->all());
	}

	/**
	 * Get the header's value with the specified filter applied
	 *
	 * @param   string  $key      The header to retrieve
	 * @param   mixed   $default  Optional default value, returned if the header is not set
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
	 * Get the header's unfiltered value if it is defined
	 *
	 * @param   string  $key      The header to retrieve
	 * @param   mixed   $default  Optional default value, returned if the header is not set
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get(string $key, $default = null)
	{
		$key = $this->formatKey($key);

		if (!$this->exists($key))
		{
			return $default === null ? [] : [$default];
		}

		return $this->headers[$key];
	}

	/**
	 * Remove a header from the registry
	 *
	 * @param   string  $key  The header to remove
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove(string $key): void
	{
		unset($this->headers[$this->formatKey($key)]);
	}

	/**
	 * Set the header's value
	 *
	 * @param   string           $key     The header to set
	 * @param   string|string[]  $values  The value of the header
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set(string $key, $values): void
	{
		$key = $this->formatKey($key);

		if (is_array($values))
		{
			$values = array_values($values);

			if (!isset($this->headers[$key]))
			{
				$this->headers[$key] = $values;
			}
			else
			{
				$this->headers[$key] = array_merge($this->headers[$key], $values);
			}
		}
		else
		{
			if (!isset($this->headers[$key]))
			{
				$this->headers[$key] = [$values];
			}
			else
			{
				$this->headers[$key][] = $values;
			}
		}
	}

	/**
	 * Format the header key for internal use
	 *
	 * @param   string  $key  The key to format
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function formatKey(string $key): string
	{
		return str_replace('_', '-', strtolower($key));
	}
}
