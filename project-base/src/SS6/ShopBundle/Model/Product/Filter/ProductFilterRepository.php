<?php

namespace SS6\ShopBundle\Model\Product\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use SS6\ShopBundle\Component\Doctrine\QueryBuilderService;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroup;
use SS6\ShopBundle\Model\Product\Availability\Availability;
use SS6\ShopBundle\Model\Product\Filter\ParameterFilterRepository;
use SS6\ShopBundle\Model\Product\Filter\ProductFilterData;
use SS6\ShopBundle\Model\Product\Pricing\ProductCalculatedPrice;
use SS6\ShopBundle\Model\Product\Product;

class ProductFilterRepository {

	const DAYS_FOR_STOCK_FILTER = 0;

	/**
	 * @var \SS6\ShopBundle\Component\Doctrine\QueryBuilderService
	 */
	private $queryBuilderService;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Filter\ParameterFilterRepository
	 */
	private $parameterFilterRepository;

	public function __construct(
		QueryBuilderService $queryBuilderService,
		ParameterFilterRepository $parameterFilterRepository
	) {
		$this->queryBuilderService = $queryBuilderService;
		$this->parameterFilterRepository = $parameterFilterRepository;
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param \SS6\ShopBundle\Model\Product\Filter\ProductFilterData $productFilterData
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 */
	public function applyFiltering(
		QueryBuilder $productsQueryBuilder,
		ProductFilterData $productFilterData,
		PricingGroup $pricingGroup
	) {
		$this->filterByPrice(
			$productsQueryBuilder,
			$productFilterData->minimalPrice,
			$productFilterData->maximalPrice,
			$pricingGroup
		);
		$this->filterByStock(
			$productsQueryBuilder,
			$productFilterData->inStock
		);
		$this->filterByFlags(
			$productsQueryBuilder,
			$productFilterData->flags
		);
		$this->parameterFilterRepository->filterByParameters(
			$productsQueryBuilder,
			$productFilterData->parameters
		);
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param string $minimalPrice
	 * @param string $maximalPrice
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $pricingGroup
	 */
	private function filterByPrice(
		QueryBuilder $productsQueryBuilder,
		$minimalPrice,
		$maximalPrice,
		PricingGroup $pricingGroup
	) {
		$priceLimits = 'pcp.product = p AND pcp.pricingGroup = :pricingGroup';
		if ($minimalPrice !== null) {
			$priceLimits .= ' AND pcp.priceWithVat >= :minimalPrice';
			$productsQueryBuilder->setParameter('minimalPrice', $minimalPrice);
		}
		if ($maximalPrice !== null) {
			$priceLimits .= ' AND pcp.priceWithVat <= :maximalPrice';
			$productsQueryBuilder->setParameter('maximalPrice', $maximalPrice);
		}
		$this->queryBuilderService->addOrExtendJoin(
			$productsQueryBuilder,
			ProductCalculatedPrice::class,
			'pcp',
			$priceLimits
		);
		$productsQueryBuilder->setParameter('pricingGroup', $pricingGroup);
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param bool $filterByStock
	 */
	public function filterByStock(QueryBuilder $productsQueryBuilder, $filterByStock) {
		if ($filterByStock) {
			$this->queryBuilderService->addOrExtendJoin(
				$productsQueryBuilder,
				Availability::class,
				'a',
				'p.calculatedAvailability = a'
			);
			$productsQueryBuilder->andWhere('a.dispatchTime = :dispatchTime');
			$productsQueryBuilder->setParameter('dispatchTime', self::DAYS_FOR_STOCK_FILTER);
		}

	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
	 * @param \SS6\ShopBundle\Model\Product\Flag\Flag[] $flags
	 */
	private function filterByFlags(QueryBuilder $productsQueryBuilder, array $flags) {
		$flagsCount = count($flags);
		if ($flagsCount !== 0) {
			$flagsQueryBuilder = $this->getFlagsQueryBuilder($flags, $productsQueryBuilder->getEntityManager());

			$productsQueryBuilder->andWhere($productsQueryBuilder->expr()->exists($flagsQueryBuilder));
			foreach ($flagsQueryBuilder->getParameters() as $parameter) {
				$productsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
			}
		}
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Flag\Flag[] $flags
	 * @param \Doctrine\ORM\EntityManager $em
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function getFlagsQueryBuilder(array $flags, EntityManager $em) {
		$flagsQueryBuilder = $em->createQueryBuilder();

		$flagsQueryBuilder
			->select('1')
			->from(Product::class, 'pf')
			->join('pf.flags', 'f', Join::ON)
			->where('pf = p')
			->andWhere('f IN (:flags)')->setParameter('flags', $flags);

		return $flagsQueryBuilder;
	}

}
