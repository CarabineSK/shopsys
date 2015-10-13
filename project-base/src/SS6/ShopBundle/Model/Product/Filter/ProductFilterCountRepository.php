<?php

namespace SS6\ShopBundle\Model\Product\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use SS6\ShopBundle\Model\Category\Category;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroup;
use SS6\ShopBundle\Model\Product\Filter\ProductFilterCountData;
use SS6\ShopBundle\Model\Product\Filter\ProductFilterData;
use SS6\ShopBundle\Model\Product\Filter\ProductFilterRepository;
use SS6\ShopBundle\Model\Product\Parameter\ProductParameterValue;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductRepository;

class ProductFilterCountRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductRepository
	 */
	private $productRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Filter\ProductFilterRepository
	 */
	private $productFilterRepository;

	public function __construct(
		EntityManager $em,
		ProductRepository $productRepository,
		ProductFilterRepository $productFilterRepository
	) {
		$this->em = $em;
		$this->productRepository = $productRepository;
		$this->productFilterRepository = $productFilterRepository;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param int $domainId
	 * @param string $locale
	 * @param \SS6\ShopBundle\Model\Product\Brand\Brand[] $brandFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Flag\Flag[] $flagFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @return \SS6\ShopBundle\Model\Product\Filter\ProductFilterCountData
	 */
	public function getProductFilterCountDataInCategory(
		Category $category,
		$domainId,
		$locale,
		array $brandFilterChoices,
		array $flagFilterChoices,
		array $parameterFilterChoices,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup
	) {
		$productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
			$domainId,
			$pricingGroup,
			$category
		);

		$productFilterCountData = new ProductFilterCountData();
		$productFilterCountData->countInStock = $this->getCountInStock(
			$productsQueryBuilder,
			$productFilterData,
			$pricingGroup
		);
		$productFilterCountData->countByBrandId = $this->getCountByBrandId(
			$productsQueryBuilder,
			$brandFilterChoices,
			$productFilterData,
			$pricingGroup
		);
		$productFilterCountData->countByFlagId = $this->getCountByFlagId(
			$productsQueryBuilder,
			$flagFilterChoices,
			$productFilterData,
			$pricingGroup
		);
		$productFilterCountData->countByParameterIdAndValueId = $this->getCountByParameterIdAndValueId(
			$productsQueryBuilder,
			$parameterFilterChoices,
			$productFilterData,
			$pricingGroup,
			$locale
		);

		return $productFilterCountData;
	}

	/**
	 * @param string|null $searchText
	 * @param int $domainId
	 * @param string $locale
	 * @param \SS6\ShopBundle\Model\Product\Brand\Brand[] $brandFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Flag\Flag[] $flagFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @return \SS6\ShopBundle\Model\Product\Filter\ProductFilterCountData
	 */
	public function getProductFilterCountDataForSearch(
		$searchText,
		$domainId,
		$locale,
		array $brandFilterChoices,
		array $flagFilterChoices,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup
	) {
		$productsQueryBuilder = $this->productRepository->getListableBySearchTextQueryBuilder(
			$domainId,
			$pricingGroup,
			$locale,
			$searchText
		);

		$productFilterCountData = new ProductFilterCountData();
		$productFilterCountData->countInStock = $this->getCountInStock(
			$productsQueryBuilder,
			$productFilterData,
			$pricingGroup
		);
		$productFilterCountData->countByBrandId = $this->getCountByBrandId(
			$productsQueryBuilder,
			$brandFilterChoices,
			$productFilterData,
			$pricingGroup
		);
		$productFilterCountData->countByFlagId = $this->getCountByFlagId(
			$productsQueryBuilder,
			$flagFilterChoices,
			$productFilterData,
			$pricingGroup
		);

		return $productFilterCountData;
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @return int
	 */
	private function getCountInStock(
		QueryBuilder $productsQueryBuilder,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup
	) {
		$productsInStockQueryBuilder = clone $productsQueryBuilder;
		$productInStockFilterData = clone $productFilterData;

		$productInStockFilterData->inStock = true;

		$this->productFilterRepository->applyFiltering(
			$productsInStockQueryBuilder,
			$productInStockFilterData,
			$pricingGroup
		);

		$productsInStockQueryBuilder
			->select('COUNT(p)')
			->resetDQLPart('orderBy');

		return $productsInStockQueryBuilder->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param \SS6\ShopBundle\Model\Product\Brand\Brand[] $brandFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @return int[brandId]
	 */
	private function getCountByBrandId(
		QueryBuilder $productsQueryBuilder,
		array $brandFilterChoices,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup
		) {
		if (count($brandFilterChoices) === 0) {
			return [];
		}

		$productFilterDataExceptBrands = clone $productFilterData;
		$productFilterDataExceptBrands->brands = [];

		$productsFilteredExceptBrandsQueryBuilder = clone $productsQueryBuilder;

		$this->productFilterRepository->applyFiltering(
			$productsFilteredExceptBrandsQueryBuilder,
			$productFilterDataExceptBrands,
			$pricingGroup
		);

		$productsFilteredExceptBrandsQueryBuilder
			->select('b.id, COUNT(p) AS cnt')
			->join('p.brand', 'b')
			->andWhere('b IN (:filterBrands)')->setParameter('filterBrands', $brandFilterChoices);

		if (count($productFilterData->brands) > 0) {
			$productsFilteredExceptBrandsQueryBuilder
				->andWhere('p.brand NOT IN (:activeBrands)')
				->setParameter('activeBrands', $productFilterData->brands);
		}

		$productsFilteredExceptBrandsQueryBuilder
			->resetDQLPart('orderBy')
			->groupBy('b.id');

		$rows = $productsFilteredExceptBrandsQueryBuilder->getQuery()->execute();

		$countByBrandId = [];
		foreach ($rows as $row) {
			$countByBrandId[$row['id']] = $row['cnt'];
		}

		return $countByBrandId;
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param \SS6\ShopBundle\Model\Product\Flag\Flag[] $flagFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @return int[flagId]
	 */
	private function getCountByFlagId(
		QueryBuilder $productsQueryBuilder,
		array $flagFilterChoices,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup
	) {
		if (count($flagFilterChoices) === 0) {
			return [];
		}

		$productFilterDataExceptFlags = clone $productFilterData;
		$productFilterDataExceptFlags->flags = [];

		$productsFilteredExceptFlagsQueryBuilder = clone $productsQueryBuilder;

		$this->productFilterRepository->applyFiltering(
			$productsFilteredExceptFlagsQueryBuilder,
			$productFilterDataExceptFlags,
			$pricingGroup
		);

		$productsFilteredExceptFlagsQueryBuilder
			->select('f.id, COUNT(p) AS cnt')
			->join('p.flags', 'f')
			->andWhere('f IN (:filterFlags)')->setParameter('filterFlags', $flagFilterChoices);

		if (count($productFilterData->flags) > 0) {
			$productsFilteredExceptFlagsQueryBuilder
				->andWhere(
					$productsFilteredExceptFlagsQueryBuilder->expr()->notIn(
						'p.id',
						$this->em->createQueryBuilder()
							->select('_p.id')
							->from(Product::class, '_p')
							->join('_p.flags', '_f')
							->where('_f IN (:activeFlags)')
							->getDQL()
					)
				)
				->setParameter('activeFlags', $productFilterData->flags);
		}

		$productsFilteredExceptFlagsQueryBuilder
			->resetDQLPart('orderBy')
			->groupBy('f.id');

		$rows = $productsFilteredExceptFlagsQueryBuilder->getQuery()->execute();

		$countByFlagId = [];
		foreach ($rows as $row) {
			$countByFlagId[$row['id']] = $row['cnt'];
		}

		return $countByFlagId;
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param \SS6\ShopBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterFilterChoices
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 * @param string $locale
	 * @return int[parameterId][valueId]
	 */
	private function getCountByParameterIdAndValueId(
		QueryBuilder $productsQueryBuilder,
		array $parameterFilterChoices,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup,
		$locale
	) {
		$countByParameterIdAndValueId = [];

		foreach ($parameterFilterChoices as $parameterFilterChoice) {
			$currentParameter = $parameterFilterChoice->getParameter();

			$productFilterDataExceptCurrentParameter = clone $productFilterData;
			foreach ($productFilterDataExceptCurrentParameter->parameters as $index => $parameterFilterData) {
				if ($parameterFilterData->parameter->getId() === $currentParameter->getId()) {
					unset($productFilterDataExceptCurrentParameter->parameters[$index]);
				}
			}

			$productsFilteredExceptCurrentParameterQueryBuilder = clone $productsQueryBuilder;

			$this->productFilterRepository->applyFiltering(
				$productsFilteredExceptCurrentParameterQueryBuilder,
				$productFilterDataExceptCurrentParameter,
				$pricingGroup
			);

			$productsFilteredExceptCurrentParameterQueryBuilder
				->select('pv.id, COUNT(p) AS cnt')
				->join(ProductParameterValue::class, 'ppv', Join::WITH, 'ppv.product = p')
				->join('ppv.value', 'pv', Join::WITH, 'pv.locale = :locale')
				->andWhere('ppv.parameter = :parameter')
				->resetDQLPart('orderBy')
				->groupBy('pv.id')
				->setParameter('locale', $locale)
				->setParameter('parameter', $currentParameter);

			$rows = $productsFilteredExceptCurrentParameterQueryBuilder->getQuery()->execute();

			$countByParameterIdAndValueId[$currentParameter->getId()] = [];
			foreach ($rows as $row) {
				$countByParameterIdAndValueId[$currentParameter->getId()][$row['id']] = $row['cnt'];
			}
		}

		return $countByParameterIdAndValueId;
	}

}
