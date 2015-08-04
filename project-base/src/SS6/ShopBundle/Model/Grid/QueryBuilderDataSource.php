<?php

namespace SS6\ShopBundle\Model\Grid;

use Doctrine\ORM\QueryBuilder;
use SS6\ShopBundle\Component\Paginator\QueryPaginator;
use SS6\ShopBundle\Model\Grid\DataSourceInterface;

class QueryBuilderDataSource implements DataSourceInterface {

	const HYDRATION_MODE = 'GroupedScalarHydrator';

	/**
	 * @var \Doctrine\ORM\QueryBuilder
	 */
	private $queryBuilder;

	/**
	 * @var string
	 */
	private $rowIdSourceColumnName;

	/**
	 * @param \Doctrine\ORM\QueryBuilder $queryBuilder
	 * @param string $rowIdSourceColumnName
	 */
	public function __construct(QueryBuilder $queryBuilder, $rowIdSourceColumnName) {
		$this->queryBuilder = $queryBuilder;
		$this->rowIdSourceColumnName = $rowIdSourceColumnName;
	}

	/**
	 * @param int|null $limit
	 * @param int $page
	 * @param string|null $orderSourceColumnName
	 * @param string $orderDirection
	 * @return \SS6\ShopBundle\Component\Paginator\PaginationResult
	 */
	public function getPaginatedRows(
		$limit = null,
		$page = 1,
		$orderSourceColumnName = null,
		$orderDirection = self::ORDER_ASC
	) {
		$queryBuilder = clone $this->queryBuilder;
		if ($orderSourceColumnName !== null) {
			$this->addQueryOrder($queryBuilder, $orderSourceColumnName, $orderDirection);
		}

		$queryPaginator = new QueryPaginator($queryBuilder, self::HYDRATION_MODE);

		$paginationResult = $queryPaginator->getResult($page, $limit);
		/* @var $paginationResult \SS6\ShopBundle\Component\Paginator\PaginationResult */

		return $paginationResult;
	}

	/**
	 * @param int $rowId
	 * @return array
	 */
	public function getOneRow($rowId) {
		$queryBuilder = clone $this->queryBuilder;
		$this->prepareQueryWithOneRow($queryBuilder, $rowId);

		return $queryBuilder->getQuery()->getSingleResult(self::HYDRATION_MODE);
	}

	/**
	 * @return int
	 */
	public function getTotalRowsCount() {
		$queryPaginator = new QueryPaginator($this->queryBuilder, self::HYDRATION_MODE);
		return $queryPaginator->getTotalCount();
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $queryBuilder
	 * @param string $orderSourceColumnName
	 * @param string $orderDirection
	 */
	private function addQueryOrder(QueryBuilder $queryBuilder, $orderSourceColumnName, $orderDirection) {
		$queryBuilder->orderBy($orderSourceColumnName, $orderDirection);
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $queryBuilder
	 * @param int $rowId
	 */
	private function prepareQueryWithOneRow(QueryBuilder $queryBuilder, $rowId) {
		$queryBuilder
			->andWhere($this->rowIdSourceColumnName . ' = :rowId')
			->setParameter('rowId', $rowId)
			->setFirstResult(null)
			->setMaxResults(null)
			->resetDQLPart('orderBy');
	}

	/**
	 * @return string
	 */
	public function getRowIdSourceColumnName() {
		return $this->rowIdSourceColumnName;
	}

}
