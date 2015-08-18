<?php

namespace SS6\ShopBundle\Model\Product;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use SS6\ShopBundle\Model\Domain\Domain;
use SS6\ShopBundle\Model\Image\ImageFacade;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupRepository;
use SS6\ShopBundle\Model\Product\Accessory\ProductAccessory;
use SS6\ShopBundle\Model\Product\Accessory\ProductAccessoryRepository;
use SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use SS6\ShopBundle\Model\Product\Parameter\ParameterRepository;
use SS6\ShopBundle\Model\Product\Parameter\ProductParameterValue;
use SS6\ShopBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductDomain;
use SS6\ShopBundle\Model\Product\ProductEditData;
use SS6\ShopBundle\Model\Product\ProductHiddenRecalculator;
use SS6\ShopBundle\Model\Product\ProductRepository;
use SS6\ShopBundle\Model\Product\ProductSellingDeniedRecalculator;
use SS6\ShopBundle\Model\Product\ProductService;
use SS6\ShopBundle\Model\Product\ProductVisibility;
use SS6\ShopBundle\Model\Product\ProductVisibilityFacade;

class ProductEditFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductRepository
	 */
	private $productRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductVisibilityFacade
	 */
	private $productVisibilityFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Parameter\ParameterRepository
	 */
	private $parameterRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductService
	 */
	private $productService;

	/**
	 * @var \SS6\ShopBundle\Model\Image\ImageFacade
	 */
	private $imageFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
	 */
	private $productPriceRecalculationScheduler;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroupRepository
	 */
	private $pricingGroupRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductManualInputPriceFacade
	 */
	private $productManualInputPriceFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler
	 */
	private $productAvailabilityRecalculationScheduler;

	/**
	 * @var \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
	 */
	private $friendlyUrlFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductHiddenRecalculator
	 */
	private $productHiddenRecalculator;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductSellingDeniedRecalculator
	 */
	private $productSellingDeniedRecalculator;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Accessory\ProductAccessoryRepository
	 */
	private $productAccessoryRepository;

	public function __construct(
		EntityManager $em,
		ProductRepository $productRepository,
		ProductVisibilityFacade $productVisibilityFacade,
		ParameterRepository $parameterRepository,
		Domain $domain,
		ProductService $productService,
		ImageFacade	$imageFacade,
		ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
		PricingGroupRepository $pricingGroupRepository,
		ProductManualInputPriceFacade $productManualInputPriceFacade,
		ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
		FriendlyUrlFacade $friendlyUrlFacade,
		ProductHiddenRecalculator $productHiddenRecalculator,
		ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
		ProductAccessoryRepository $productAccessoryRepository
	) {
		$this->em = $em;
		$this->productRepository = $productRepository;
		$this->productVisibilityFacade = $productVisibilityFacade;
		$this->parameterRepository = $parameterRepository;
		$this->domain = $domain;
		$this->productService = $productService;
		$this->imageFacade = $imageFacade;
		$this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
		$this->pricingGroupRepository = $pricingGroupRepository;
		$this->productManualInputPriceFacade = $productManualInputPriceFacade;
		$this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
		$this->friendlyUrlFacade = $friendlyUrlFacade;
		$this->productHiddenRecalculator = $productHiddenRecalculator;
		$this->productSellingDeniedRecalculator = $productSellingDeniedRecalculator;
		$this->productAccessoryRepository = $productAccessoryRepository;
	}

	/**
	 * @param int $productId
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function getById($productId) {
		return $this->productRepository->getById($productId);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function create(ProductEditData $productEditData) {
		$product = new Product($productEditData->productData);

		$this->em->persist($product);
		$this->em->beginTransaction();
		$this->em->flush($product);
		$this->saveParameters($product, $productEditData->parameters);
		$this->createProductDomains($product, $this->domain->getAll());
		$this->createProductVisibilities($product);
		$this->refreshProductDomains($product, $productEditData);
		$this->refreshProductManualInputPrices($product, $productEditData->manualInputPrices);
		$this->refreshProductAccessories($product, $productEditData->accessories);
		$this->productHiddenRecalculator->calculateHiddenForProduct($product);
		$this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

		$this->imageFacade->uploadImages($product, $productEditData->imagesToUpload, null);
		$this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());
		$this->em->commit();

		$this->productAvailabilityRecalculationScheduler->scheduleRecalculateAvailabilityForProduct($product);
		$this->productVisibilityFacade->refreshProductsVisibilityDelayed();
		$this->productPriceRecalculationScheduler->scheduleRecalculatePriceForProduct($product);

		return $product;
	}

	/**
	 * @param int $productId
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function edit($productId, ProductEditData $productEditData) {
		$product = $this->productRepository->getById($productId);

		$this->productService->edit($product, $productEditData->productData);

		$this->em->beginTransaction();
		try {
			$this->saveParameters($product, $productEditData->parameters);
			$this->refreshProductDomains($product, $productEditData);
			$this->refreshProductManualInputPrices($product, $productEditData->manualInputPrices);
			$this->refreshProductAccessories($product, $productEditData->accessories);
			$this->em->flush();
			$this->productHiddenRecalculator->calculateHiddenForProduct($product);
			$this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);
			$this->imageFacade->saveImagePositions($productEditData->imagePositions);
			$this->imageFacade->uploadImages($product, $productEditData->imagesToUpload, null);
			$this->imageFacade->deleteImages($product, $productEditData->imagesToDelete);
			$this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productEditData->urls);
			$this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());
			$this->em->commit();
		} catch (\Exception $exception) {
			$this->em->rollback();
			throw $exception;
		}

		$this->productAvailabilityRecalculationScheduler->scheduleRecalculateAvailabilityForProduct($product);
		$this->productVisibilityFacade->refreshProductsVisibilityDelayed();

		return $product;
	}

	/**
	 * @param int $productId
	 */
	public function delete($productId) {
		$product = $this->productRepository->getById($productId);
		$this->em->remove($product);
		$this->em->flush();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param \SS6\ShopBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData
	 */
	private function saveParameters(Product $product, array $productParameterValuesData) {
		// Doctrine runs INSERTs before DELETEs in UnitOfWork. In case of UNIQUE constraint
		// in database, this leads in trying to insert duplicate entry.
		// That's why it's necessary to do remove and flush first.

		$oldProductParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);
		foreach ($oldProductParameterValues as $oldProductParameterValue) {
			$this->em->remove($oldProductParameterValue);
		}
		$this->em->flush($oldProductParameterValues);

		$toFlush = [];
		foreach ($productParameterValuesData as $productParameterValueData) {
			$productParameterValueData->product = $product;
			$productParameterValue = new ProductParameterValue(
				$productParameterValueData->product,
				$productParameterValueData->parameter,
				$productParameterValueData->locale,
				$this->parameterRepository->findOrCreateParameterValueByValueText($productParameterValueData->valueText)
			);
			$this->em->persist($productParameterValue);
			$toFlush[] = $productParameterValue;
		}
		$this->em->flush($toFlush);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param \SS6\ShopBundle\Model\Domain\Config\DomainConfig[] $domains
	 */
	private function createProductDomains(Product $product, array $domains) {
		$toFlush = [];
		foreach ($domains as $domain) {
			$productDomain = new ProductDomain($product, $domain->getId());
			$this->em->persist($productDomain);
			$toFlush[] = $productDomain;
		}
		$this->em->flush($toFlush);

	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 */
	private function refreshProductDomains(Product $product, ProductEditData $productEditData) {
		$hiddenOnDomainData = $productEditData->productData->hiddenOnDomains;
		$productDomains = $this->productRepository->getProductDomainsByProductIndexedByDomainId($product);
		$seoTitles = $productEditData->seoTitles;
		$seoMetaDescriptions = $productEditData->seoMetaDescriptions;
		$descriptions = $productEditData->descriptions;
		$heurekaCpcValues = $productEditData->heurekaCpcValues;
		foreach ($productDomains as $domainId => $productDomain) {
			if (in_array($productDomain->getDomainId(), $hiddenOnDomainData)) {
				$productDomain->setHidden(true);
			} else {
				$productDomain->setHidden(false);
			}
			if (!empty($seoTitles)) {
				$productDomain->setSeoTitle($seoTitles[$domainId]);
			}
			if (!empty($seoMetaDescriptions)) {
				$productDomain->setSeoMetaDescription($seoMetaDescriptions[$domainId]);
			}
			if (!empty($descriptions)) {
				$productDomain->setDescription($descriptions[$domainId]);
			}
			if (!empty($heurekaCpcValues)) {
				$productDomain->setHeurekaCpc($heurekaCpcValues[$domainId]);
			}
		}

		$this->em->flush($productDomains);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @return \SS6\ShopBundle\Model\Product\Pricing\ProductSellingPrice[]
	 */
	public function getAllProductSellingPricesIndexedByDomainId(Product $product) {
		return $this->productService->getProductSellingPricesIndexedByDomainIdAndPricingGroupId(
			$product,
			$this->pricingGroupRepository->getAll()
		);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param string[] $manualInputPrices
	 */
	private function refreshProductManualInputPrices(Product $product, array $manualInputPrices) {
		if ($product->getPriceCalculationType() === Product::PRICE_CALCULATION_TYPE_AUTO) {
			$this->productManualInputPriceFacade->deleteByProduct($product);
		} else {
			foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
				$this->productManualInputPriceFacade->refresh($product, $pricingGroup, $manualInputPrices[$pricingGroup->getId()]);
			}
		}
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 */
	private function createProductVisibilities(Product $product) {
		foreach ($this->domain->getAll() as $domainConfig) {
			$domainId = $domainConfig->getId();
			$toFlush = [];
			foreach ($this->pricingGroupRepository->getPricingGroupsByDomainId($domainId) as $pricingGroup) {
				$productVisibility = new ProductVisibility($product, $pricingGroup, $domainId);
				$this->em->persist($productVisibility);
				$toFlush[] = $productVisibility;
			}
		}
		$this->em->flush($toFlush);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product[] $accessories
	 */
	private function refreshProductAccessories(Product $product, array $accessories) {
		$oldProductAccessories = $this->productAccessoryRepository->getAllByProduct($product);
		foreach ($oldProductAccessories as $oldProductAccessory) {
			$this->em->remove($oldProductAccessory);
		}
		$this->em->flush($oldProductAccessories);

		$toFlush = [];
		foreach ($accessories as $position => $accessory) {
			$newProductAccessory = new ProductAccessory($product, $accessory, $position);
			$this->em->persist($newProductAccessory);
			$toFlush[] = $newProductAccessory;
		}
		$this->em->flush($toFlush);
	}

}
