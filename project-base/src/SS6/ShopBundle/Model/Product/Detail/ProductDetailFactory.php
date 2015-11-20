<?php

namespace SS6\ShopBundle\Model\Product\Detail;

use SS6\ShopBundle\Component\Image\ImageFacade;
use SS6\ShopBundle\Model\Localization\Localization;
use SS6\ShopBundle\Model\Product\Parameter\ParameterRepository;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculation;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductRepository;

class ProductDetailFactory {

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculationForUser
	 */
	private $productPriceCalculationForUser;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculation
	 */
	private $productPriceCalculation;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductRepository
	 */
	private $productRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Parameter\ParameterRepository
	 */
	private $parameterRepository;

	/**
	 * @var \SS6\ShopBundle\Component\Image\ImageFacade
	 */
	private $imageFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Localization\Localization
	 */
	private $localization;

	public function __construct(
		ProductPriceCalculationForUser $productPriceCalculationForUser,
		ProductPriceCalculation $productPriceCalculation,
		ProductRepository $productRepository,
		ParameterRepository $parameterRepository,
		ImageFacade $imageFacade,
		Localization $localization
	) {
		$this->productPriceCalculationForUser = $productPriceCalculationForUser;
		$this->productPriceCalculation = $productPriceCalculation;
		$this->productRepository = $productRepository;
		$this->parameterRepository = $parameterRepository;
		$this->imageFacade = $imageFacade;
		$this->localization = $localization;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Model\Product\Detail\ProductDetail
	 */
	public function getDetailForProduct(Product $product) {
		return new ProductDetail($product, $this);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product[] $products
	 * @return \SS6\ShopBundle\Model\Product\Detail\ProductDetail[]
	 */
	public function getDetailsForProducts(array $products) {
		$details = [];

		foreach ($products as $product) {
			$details[] = $this->getDetailForProduct($product);
		}

		return $details;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Model\Pricing\Price
	 */
	public function getBasePrice(Product $product) {
		return $this->productPriceCalculation->calculateBasePrice($product);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Model\Product\Pricing\ProductPrice|null
	 */
	public function getSellingPrice(Product $product) {
		try {
			$productPrice = $this->productPriceCalculationForUser->calculatePriceForCurrentUser($product);
		} catch (\SS6\ShopBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException $ex) {
			$productPrice = null;
		}
		return $productPrice;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Model\Product\Parameter\ProductParameterValue[]
	 */
	public function getParameters(Product $product) {
		$productParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);
		foreach ($productParameterValues as $index => $productParameterValue) {
			$parameter = $productParameterValue->getParameter();
			if ($parameter->getName() === null
				|| $productParameterValue->getValue()->getLocale() !== $this->localization->getLocale()
			) {
				unset($productParameterValues[$index]);
			}
		}

		return $productParameterValues;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Component\Image\Image[imageId]
	 */
	public function getImagesIndexedById(Product $product) {
		return $this->imageFacade->getImagesByEntityIndexedById($product, null);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Model\Product\ProductDomain[]
	 */
	public function getProductDomainsIndexedByDomainId(Product $product) {
		return $this->productRepository->getProductDomainsByProductIndexedByDomainId($product);
	}

}
