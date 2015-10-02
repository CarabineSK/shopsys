<?php

namespace SS6\ShopBundle\Component\Image\Exception;

use Exception;
use SS6\ShopBundle\Component\Image\Exception\ImageException;

class ImageNotFoundException extends Exception implements ImageException {

	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message = '', Exception $previous = null) {
		parent::__construct($message, 0, $previous);
	}

}
