<?php

namespace SS6\ShopBundle\Twig;

use SS6\ShopBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductVisibilityRepository;
use Twig_SimpleFunction;

class ProductVisibilityExtension extends \Twig_Extension {

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductVisibilityRepository
	 */
	private $productVisibilityRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroupSettingFacade
	 */
	private $pricingGroupSettingFacade;

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
	 */
	public function __construct(
		ProductVisibilityRepository $productVisibilityRepository,
		PricingGroupSettingFacade $pricingGroupSettingFacade
	) {
		$this->productVisibilityRepository = $productVisibilityRepository;
		$this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction('isVisibileForDefaultPricingGroup', [$this, 'isVisibileForDefaultPricingGroupOnDomain']),
		];
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'product_visibility';
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param int $domainId
	 * @return bool
	 */
	public function isVisibileForDefaultPricingGroupOnDomain(Product $product, $domainId) {
		$pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
		$productVisibility = $this->productVisibilityRepository->getProductVisibility($product, $pricingGroup, $domainId);

		return $productVisibility->isVisible();
	}

}
