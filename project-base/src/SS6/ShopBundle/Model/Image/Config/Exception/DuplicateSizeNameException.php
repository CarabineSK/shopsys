<?php

namespace SS6\ShopBundle\Model\Image\Config\Exception;

use Exception;

class DuplicateSizeNameException extends Exception implements ImageConfigException {

	/**
	 * @var string|null
	 */
	private $sizeName;

	/**
	 * @param string|null$sizeName
	 * @param \Exception $previous
	 */
	public function __construct($sizeName, Exception $previous = null) {
		$this->sizeName = $sizeName;

		if ($this->sizeName === null) {
			$message = 'Image size NULL is not unique.';
		} else {
			$message = sprintf('Image size "%s" is not unique.', $this->sizeName);
		}
		parent::__construct($message, 0, $previous);
	}

	/**
	 * @return string|null
	 */
	public function getSizeName() {
		return $this->sizeName;
	}
}