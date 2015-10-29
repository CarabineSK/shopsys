<?php

namespace SS6\ShopBundle\Component\UploadedFile\Config\Exception;

use Exception;
use SS6\ShopBundle\Component\UploadedFile\Config\Exception\UploadedFileConfigException;

class UploadedFileEntityConfigNotFoundException extends Exception implements UploadedFileConfigException {

	/**
	 * @var string
	 */
	private $entityClassOrName;

	/**
	 * @param string $entityClassOrName
	 * @param \Exception $previous
	 */
	public function __construct($entityClassOrName, Exception $previous = null) {
		$this->entityClassOrName = $entityClassOrName;

		parent::__construct('Not found upladed file config for entity "' . $entityClassOrName . '".', 0, $previous);
	}

	/**
	 * @return string
	 */
	public function getEntityClassOrName() {
		return $this->entityClassOrName;
	}

}
