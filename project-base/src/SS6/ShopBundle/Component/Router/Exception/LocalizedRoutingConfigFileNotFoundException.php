<?php

namespace SS6\ShopBundle\Component\Router\Exception;

use Exception;

class LocalizedRoutingConfigFileNotFoundException extends Exception implements RouterException {

	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message = '', $previous = null) {
		parent::__construct($message, 0, $previous);
	}
}
