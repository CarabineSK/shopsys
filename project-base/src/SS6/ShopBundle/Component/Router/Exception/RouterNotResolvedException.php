<?php

namespace SS6\ShopBundle\Component\Router\Exception;

use Exception;

class RouterNotResolvedException extends Exception implements RouterException {

	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message = null, $previous = null) {
		parent::__construct($message, 0, $previous);
	}
}
