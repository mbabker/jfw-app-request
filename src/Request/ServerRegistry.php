<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Request;

/**
 * Registry holding the request's data based on the $_SERVER global
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerRegistry extends Registry
{
	/**
	 * Gets the request headers
	 *
	 * This method is based on Symfony\Component\HttpFoundation\ServerBag::getHeaders()
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getHeaders(): array
	{
		$headers = [];

		// Explicit list of headers that aren't prefixed with HTTP_
		$contentHeaders = ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'];

		foreach ($this->all() as $key => $value)
		{
			if (strpos($key, 'HTTP_') === 0)
			{
				$headers[substr($key, 5)] = $value;
			}
			elseif (in_array($key, $contentHeaders))
			{
				$headers[$key] = $value;
			}
		}

		// Extract authentication based headers if able
		if ($this->exists('PHP_AUTH_USER'))
		{
			$headers['PHP_AUTH_USER'] = $this->get('PHP_AUTH_USER');
			$headers['PHP_AUTH_PW']   = $this->get('PHP_AUTH_PW', '');
		}
		else
		{
			$authorizationHeader = null;

			if ($this->exists('HTTP_AUTHORIZATION'))
			{
				$authorizationHeader = $this->get('HTTP_AUTHORIZATION');
			}
			elseif ($this->exists('REDIRECT_HTTP_AUTHORIZATION'))
			{
				$authorizationHeader = $this->get('REDIRECT_HTTP_AUTHORIZATION');
			}

			if ($authorizationHeader !== null)
			{
				if (stripos($authorizationHeader, 'basic ') === 0)
				{
					// Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
					$exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);

					if (count($exploded) === 2)
					{
						list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
					}
				}
				elseif (empty($this->get('PHP_AUTH_DIGEST')) && stripos($authorizationHeader, 'digest ') === 0)
				{
					// In some circumstances PHP_AUTH_DIGEST needs to be set
					$headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
					$this->set('PHP_AUTH_DIGEST', $authorizationHeader);
				}
				elseif (stripos($authorizationHeader, 'bearer ') === 0)
				{
					// There is no standard "PHP_AUTH_BEARER" header so just set this value to the Authorization header
					$headers['AUTHORIZATION'] = $authorizationHeader;
				}
			}
		}

		if (isset($headers['AUTHORIZATION']))
		{
			return $headers;
		}

		// PHP_AUTH_USER/PHP_AUTH_PW
		if (isset($headers['PHP_AUTH_USER']))
		{
			$headers['AUTHORIZATION'] = 'Basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
		}
		elseif (isset($headers['PHP_AUTH_DIGEST']))
		{
			$headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
		}

		return $headers;
	}
}
