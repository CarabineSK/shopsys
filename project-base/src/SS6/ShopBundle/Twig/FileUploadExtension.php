<?php

namespace SS6\ShopBundle\Twig;

use SS6\ShopBundle\Component\FileUpload\FileUpload;
use Twig_Extension;
use Twig_SimpleFunction;

class FileUploadExtension extends Twig_Extension {

	/**
	 * @var \SS6\ShopBundle\Component\FileUpload\FileUpload
	 */
	private $fileUpload;

	/**
	 * @param \SS6\ShopBundle\Component\FileUpload\FileUpload $fileUpload
	 */
	public function __construct(FileUpload $fileUpload) {
		$this->fileUpload = $fileUpload;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction('getLabelByTemporaryFilename', [$this, 'getLabelByTemporaryFilename']),
		];
	}

	/**
	 * @param string $temporaryFilename
	 * @return string
	 */
	public function getLabelByTemporaryFilename($temporaryFilename) {
		$filename = $this->fileUpload->getOriginalFilenameByTemporary($temporaryFilename);
		$filepath = ($this->fileUpload->getTemporaryDirectory() . DIRECTORY_SEPARATOR . $temporaryFilename);
		if (file_exists($filepath) && is_file($filepath) && is_writable($filepath)) {
			$fileSize = round((int)filesize($filepath) / 1024 / 1024, 2);
			return $filename . ' (' . $fileSize . ' MB)';
		}
		return '';
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'fileupload_extension';
	}
}
