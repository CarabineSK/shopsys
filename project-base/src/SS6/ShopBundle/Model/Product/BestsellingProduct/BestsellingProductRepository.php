<?php

namespace SS6\ShopBundle\Model\Product\BestsellingProduct;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use SS6\ShopBundle\Model\Category\Category;
use SS6\ShopBundle\Model\Order\Item\OrderProduct;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroup;
use SS6\ShopBundle\Model\Product\BestsellingProduct\ManualBestsellingProduct;
use SS6\ShopBundle\Model\Product\Pricing\ProductCalculatedPrice;
use SS6\ShopBundle\Model\Product\ProductRepository;

class BestsellingProductRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductRepository
	 */
	private $productRepository;

	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager, ProductRepository $productRepository) {
		$this->em = $entityManager;
		$this->productRepository = $productRepository;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param int $domainId
	 * @return \SS6\ShopBundle\Model\Product\BestsellingProduct\ManualBestsellingProduct|null
	 */
	public function getManualBestsellingProductsByCategoryAndDomainId(Category $category, $domainId) {
		$queryBuilder = $this->em->createQueryBuilder()
			->select('bp')
			->from(ManualBestsellingProduct::class, 'bp', 'bp.position')
			->where('bp.category = :category')
			->andWhere('bp.domainId = :domainId')
			->setParameter('category', $category)
			->setParameter('domainId', $domainId);

		return $queryBuilder->getQuery()->execute();
	}

	/**
	 * @param int $domainId
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @return \SS6\ShopBundle\Model\Product\BestsellingProduct\ManualBestsellingProduct[]
	 */
	public function getListableManualBestsellingProducts($domainId, Category $category, PricingGroup $pricingGroup) {
		$queryBuilder = $this->productRepository->getAllSellableQueryBuilder($domainId, $pricingGroup);

		$queryBuilder
			->select('bp')
			->join(ManualBestsellingProduct::class, 'bp', Join::WITH, 'bp.product = p')
			->andWhere('bp.category = :category')
			->andWhere('bp.domainId = prv.domainId')
			->setParameter('category', $category);

		return $queryBuilder->getQuery()->execute();
	}

	/**
	 * @param int $domainId
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @param int $maxResults
	 * @return \SS6\ShopBundle\Model\Product\Product[]
	 */
	public function getListableAutomaticBestsellingProducts(
		$domainId,
		Category $category,
		PricingGroup $pricingGroup,
		$maxResults
	) {
		$queryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder($domainId, $pricingGroup, $category);

		$queryBuilder
			->addSelect('COUNT(op) AS HIDDEN orderCount')
			->join(ProductCalculatedPrice::class, 'pcp', Join::WITH, 'pcp.product = p')
			->leftJoin(OrderProduct::class, 'op', Join::WITH, 'op.product = p')
			->andWhere('pcp.pricingGroup = prv.pricingGroup')
			->orderBy('orderCount', 'DESC')
			->addOrderBy('pcp.priceWithVat', 'DESC')
			->groupBy('p, pcp')
			->setMaxResults($maxResults);

		return $queryBuilder->getQuery()->execute();
	}

	/**
	 * @param int $domainId
	 * @return int[categoryId]
	 */
	public function getManualBestsellingProductCountsInCategories($domainId) {
		$queryBuilder = $this->em->createQueryBuilder();

		$queryBuilder
			->select('c.id, COUNT(mbp) AS cnt')
			->from(Category::class, 'c')
			->leftJoin(ManualBestsellingProduct::class, 'mbp', Join::WITH, 'mbp.category = c AND mbp.domainId = :domainId')
			->setParameter('domainId', $domainId)
			->groupBy('c.id');

		$rows = $queryBuilder->getQuery()->execute();
		$manualBestsellingProductCountsIndexedByCategoryId = [];
		foreach ($rows as $row) {
			$manualBestsellingProductCountsIndexedByCategoryId[$row['id']] = $row['cnt'];
		}

		return $manualBestsellingProductCountsIndexedByCategoryId;
	}

}
