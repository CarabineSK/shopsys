<?php

namespace SS6\ShopBundle\Twig;

use SS6\ShopBundle\Component\Condition;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Component\Image\Config\ImageConfig;
use SS6\ShopBundle\Component\Image\ImageFacade;
use SS6\ShopBundle\Component\Image\ImageLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class ImageExtension extends Twig_Extension {

	const NOIMAGE_FILENAME = 'noimage.gif';

	/**
	 * @var string
	 */
	private $frontDesignImageUrlPrefix;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $container;

	/**
	 * @var \SS6\ShopBundle\Component\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Component\Image\ImageLocator
	 */
	private $imageLocator;

	/**
	 * @var \SS6\ShopBundle\Component\Image\Config\ImageConfig
	 */
	private $imageConfig;

	/**
	 * @var \SS6\ShopBundle\Component\Image\ImageFacade
	 */
	private $imageFacade;

	/**
	 * @param string $frontDesignImageUrlPrefix
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param \SS6\ShopBundle\Component\Domain\Domain $domain
	 * @param \SS6\ShopBundle\Component\Image\ImageLocator $imageLocator
	 * @param \SS6\ShopBundle\Component\Image\Config\ImageConfig $imageConfig
	 * @param \SS6\ShopBundle\Component\Image\ImageFacade $imageFacade
	 */
	public function __construct(
		$frontDesignImageUrlPrefix,
		ContainerInterface $container,
		Domain $domain,
		ImageLocator $imageLocator,
		ImageConfig $imageConfig,
		ImageFacade $imageFacade
	) {
		$this->frontDesignImageUrlPrefix = $frontDesignImageUrlPrefix;
		$this->container = $container;
		$this->domain = $domain;
		$this->imageLocator = $imageLocator;
		$this->imageConfig = $imageConfig;
		$this->imageFacade = $imageFacade;
	}

	/**
	 * Get service "templating" cannot be called in constructor - https://github.com/symfony/symfony/issues/2347
	 * because it causes circular dependency
	 *
	 * @return \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
	 */
	private function getTemplatingService() {
		return $this->container->get('templating');
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction('imageExists', [$this, 'imageExists']),
			new Twig_SimpleFunction('imageUrl', [$this, 'getImageUrl']),
			new Twig_SimpleFunction('image', [$this, 'getImageHtml'], ['is_safe' => ['html']]),
			new Twig_SimpleFunction('getImages', [$this, 'getImages']),
		];
	}

	/**
	 * @param \SS6\ShopBundle\Component\Image\Image|Object $imageOrEntity
	 * @param string|null $type
	 * @return bool
	 */
	public function imageExists($imageOrEntity, $type = null) {
		try {
			$image = $this->imageFacade->getImageByObject($imageOrEntity, $type);
		} catch (\SS6\ShopBundle\Component\Image\Exception\ImageNotFoundException $e) {
			return false;
		}

		return $this->imageLocator->imageExists($image);
	}

	/**
	 * @param \SS6\ShopBundle\Component\Image\Image|Object $imageOrEntity
	 * @param string|null $sizeName
	 * @param string|null $type
	 * @return string
	 */
	public function getImageUrl($imageOrEntity, $sizeName = null, $type = null) {
		try {
			return $this->imageFacade->getImageUrl($this->domain->getCurrentDomainConfig(), $imageOrEntity, $sizeName, $type);
		} catch (\SS6\ShopBundle\Component\Image\Exception\ImageNotFoundException $e) {
			return $this->getEmptyImageUrl();
		}
	}

	/**
	 * @param Object $entity
	 * @param string|null $type
	 * @return \SS6\ShopBundle\Component\Image\Image[]
	 */
	public function getImages($entity, $type = null) {
		return $this->imageFacade->getImagesByEntityIndexedById($entity, $type);
	}

	/**
	 * @param \SS6\ShopBundle\Component\Image\Image|Object $imageOrEntity
	 * @param array $attributtes
	 * @return string
	 */
	public function getImageHtml($imageOrEntity, array $attributtes = []) {
		Condition::setArrayDefaultValue($attributtes, 'type');
		Condition::setArrayDefaultValue($attributtes, 'size');
		Condition::setArrayDefaultValue($attributtes, 'alt', '');
		Condition::setArrayDefaultValue($attributtes, 'title', $attributtes['alt']);

		try {
			$image = $this->imageFacade->getImageByObject($imageOrEntity, $attributtes['type']);
			$entityName = $image->getEntityName();
			$attributtes['src'] = $this->getImageUrl($image, $attributtes['size'], $attributtes['type']);
		} catch (\SS6\ShopBundle\Component\Image\Exception\ImageNotFoundException $e) {
			$entityName = 'noimage';
			$attributtes['src'] = $this->getEmptyImageUrl();
		}

		$htmlAttributes = $attributtes;
		unset($htmlAttributes['type'], $htmlAttributes['size']);

		return $this->getTemplatingService()->render('@SS6Shop/Common/image.html.twig', [
			'attr' => $htmlAttributes,
			'imageCssClass' => $this->getImageCssClass($entityName, $attributtes['type'], $attributtes['size']),
		]);
	}

	/**
	 * @return string
	 */
	private function getEmptyImageUrl() {
		return $this->domain->getUrl() . $this->frontDesignImageUrlPrefix . self::NOIMAGE_FILENAME;
	}

	/**
	 * @param \SS6\ShopBundle\Component\Image\Image $image
	 * @param string|null $sizeName
	 * @return string
	 */
	private function getImageCssClass($entityName, $type, $sizeName) {
		$allClassParts = [
			'image',
			$entityName,
			$type,
			$sizeName,
		];
		$classParts = array_filter($allClassParts);

		return implode('-', $classParts);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'image_extension';
	}
}
