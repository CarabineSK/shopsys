<?php

namespace SS6\ShopBundle\Model\Product\TopProduct\Exception;

use Exception;
use SS6\ShopBundle\Model\Product\TopProduct\Exception\TopProductException;

class TopProductAlreadyExistsException extends Exception implements TopProductException {

	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message = '', Exception $previous = null) {
		parent::__construct($message, 0, $previous);
	}

}
