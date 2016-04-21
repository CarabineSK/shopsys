<?php

namespace SS6\ShopBundle\Model\Product\BestsellingProduct;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Category\Category;
use SS6\ShopBundle\Model\Product\BestsellingProduct\BestsellingProductRepository;

class BestsellingProductEditFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\BestsellingProduct\BestsellingProductRepository
	 */
	private $bestsellingProductRepository;

	public function __construct(
		EntityManager $em,
		BestsellingProductRepository $bestsellingProductRepository
	) {
		$this->em = $em;
		$this->bestsellingProductRepository = $bestsellingProductRepository;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param int $domainId
	 * @param \SS6\ShopBundle\Model\Product\Product[] $bestsellingProducts
	 */
	public function edit(Category $category, $domainId, array $bestsellingProducts) {
		$toDelete = $this->bestsellingProductRepository->getManualBestsellingProductsByCategoryAndDomainId($category, $domainId);
		foreach ($toDelete as $item) {
			$this->em->remove($item);
		}
		$this->em->flush();

		foreach ($bestsellingProducts as $position => $product) {
			if ($product !== null) {
				$manualBestsellingProduct = new ManualBestsellingProduct($domainId, $category, $product, $position);
				$this->em->persist($manualBestsellingProduct);
			}
		}
		$this->em->flush();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param int $domainId
	 * @return \SS6\ShopBundle\Model\Product\Product[]
	 */
	public function getBestsellingProductsIndexedByPosition($category, $domainId) {
		$bestsellingProducts = $this->bestsellingProductRepository->getManualBestsellingProductsByCategoryAndDomainId(
			$category,
			$domainId
		);

		$products = [];
		foreach ($bestsellingProducts as $key => $bestsellingProduct) {
			$products[$key] = $bestsellingProduct->getProduct();

		}

		return $products;
	}

	/**
	 * @param int $domainId
	 * @return int[categoryId]
	 */
	public function getManualBestsellingProductCountsInCategories($domainId) {
		return $this->bestsellingProductRepository->getManualBestsellingProductCountsInCategories($domainId);
	}

}
