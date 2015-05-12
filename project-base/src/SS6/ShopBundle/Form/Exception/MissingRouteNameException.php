<?php

namespace SS6\ShopBundle\Form\Exception;

use Exception;
use SS6\ShopBundle\Form\FormException;

class MissingRouteNameException extends Exception implements FormException {

	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message = '', Exception $previous = null) {
		parent::__construct($message, 0, $previous);
	}
}
