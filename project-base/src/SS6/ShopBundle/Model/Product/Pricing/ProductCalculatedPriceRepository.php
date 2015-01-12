<?php

namespace SS6\ShopBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroup;
use SS6\ShopBundle\Model\Product\Pricing\ProductCalculatedPrice;
use SS6\ShopBundle\Model\Product\Product;

class ProductCalculatedPriceRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getProductCalculatedPriceRepository() {
		return $this->em->getRepository(ProductCalculatedPrice::class);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @param string $priceWithVat
	 */
	public function saveCalculatedPrice(Product $product, PricingGroup $pricingGroup, $priceWithVat) {
		$productCalculatedPrice = $this->getProductCalculatedPriceRepository()->find([
			'product' => $product->getId(),
			'pricingGroup' => $pricingGroup->getId(),
		]);

		if ($productCalculatedPrice === null) {
			$productCalculatedPrice = new ProductCalculatedPrice($product, $pricingGroup, $priceWithVat);
			$this->em->persist($productCalculatedPrice);
		} else {
			$productCalculatedPrice->setPriceWithVat($priceWithVat);
		}
	}

}
