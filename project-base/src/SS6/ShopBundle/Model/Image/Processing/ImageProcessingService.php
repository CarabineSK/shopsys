<?php

namespace SS6\ShopBundle\Model\Image\Processing;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use SS6\ShopBundle\Model\Image\Config\ImageSizeConfig;
use Symfony\Component\Filesystem\Filesystem;

class ImageProcessingService {

	const EXTENSION_JPEG = 'jpeg';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_PNG = 'png';
	const EXTENSION_GIF = 'gif';

	/**
	 * @var string[]
	 */
	private $supportedImageExtensions;

	/**
	 * @var \Intervention\Image\ImageManager
	 */
	private $imageManager;

	/**
	 * @var \Symfony\Component\Filesystem\Filesystem
	 */
	private $filesystem;

	public function __construct(ImageManager $imageManager, Filesystem $filesystem) {
		$this->imageManager = $imageManager;
		$this->filesystem = $filesystem;

		$this->supportedImageExtensions = [
			self::EXTENSION_JPEG,
			self::EXTENSION_JPG,
			self::EXTENSION_GIF,
			self::EXTENSION_PNG,
		];
	}

	/**
	 * @param string $filepath
	 * @return \Intervention\Image\Image
	 */
	public function createInterventionImage($filepath) {
		$extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
		if (!in_array($extension, $this->supportedImageExtensions)) {
			throw new \SS6\ShopBundle\Model\Image\Processing\Exception\FileIsNotSupportedImageException($filepath);
		}
		try {
			return $this->imageManager->make($filepath);
		} catch (\Intervention\Image\Exception\NotReadableException $ex) {
			throw new \SS6\ShopBundle\Model\Image\Processing\Exception\FileIsNotSupportedImageException($filepath, $ex);
		}
	}

	/**
	 * @param string $filepath
	 * @return string
	 */
	public function convertToShopFormatAndGetNewFilename($filepath) {
		$extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
		$newFilepath = pathinfo($filepath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($filepath, PATHINFO_FILENAME) . '.';

		if ($extension === self::EXTENSION_PNG) {
			$newFilepath .= self::EXTENSION_PNG;
		} elseif (in_array($extension, $this->supportedImageExtensions)) {
			$newFilepath .= self::EXTENSION_JPG;
		} else {
			throw new \SS6\ShopBundle\Model\Image\Processing\Exception\FileIsNotSupportedImageException($filepath);
		}

		$image = $this->createInterventionImage($filepath)->save($newFilepath);
		if (realpath($filepath) !== realpath($newFilepath)) {
			$this->filesystem->remove($filepath);
		}

		return $image->filename . '.' . $image->extension;
	}

	/**
	 * @param \Intervention\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param bool $crop
	 * @return \Intervention\Image\Image
	 */
	public function resize(Image $image, $width, $height, $crop = false) {
		if ($crop) {
			$image->fit($width, $height, function (Constraint $constraint) {
				$constraint->aspectRatio();
			});
		} else {
			$image->resize($width, $height, function (Constraint $constraint) {
				$constraint->aspectRatio();
			});
		}

		return $image;
	}

	/**
	 * @param \Intervention\Image\Image $image
	 * @param \SS6\ShopBundle\Model\Image\Config\ImageSizeConfig $sizeConfig
	 */
	public function resizeBySizeConfig(Image $image, ImageSizeConfig $sizeConfig) {
		$this->resize($image, $sizeConfig->getWidth(), $sizeConfig->getHeight(), $sizeConfig->getCrop());
	}

}
