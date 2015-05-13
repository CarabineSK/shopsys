<?php
/**
 * Author: Jakub Dolba
 * Date: 25. 4. 2015
 * Description:
 */

namespace SS6\ShopBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use SS6\ShopBundle\Component\String\DatabaseSearching;
use SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFilterInterface;

class OrderNumberFilter implements AdvancedSearchOrderFilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'orderNumber';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedOperators() {
		return [
			self::OPERATOR_CONTAINS,
			self::OPERATOR_NOT_CONTAINS,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFormType() {
		return 'text';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFormOptions() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData) {
		foreach ($rulesData as $index => $ruleData) {
			if ($ruleData->operator === self::OPERATOR_CONTAINS || $ruleData->operator === self::OPERATOR_NOT_CONTAINS) {
				if ($ruleData->value === null || $ruleData->value == '') {
					$searchValue = '%';
				} else {
					$searchValue = '%' . DatabaseSearching::getLikeSearchString($ruleData->value) . '%';
				}

				$dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
				$parameterName = 'orderNumber_' . $index;
				$queryBuilder->andWhere('o.number ' . $dqlOperator . ' :' . $parameterName);
				$queryBuilder->setParameter($parameterName, $searchValue);
			}
		}
	}

	/**
	 * @param string $operator
	 * @return string
	 */
	private function getContainsDqlOperator($operator) {
		switch ($operator) {
			case self::OPERATOR_CONTAINS:
				return 'LIKE';
			case self::OPERATOR_NOT_CONTAINS:
				return 'NOT LIKE';
		}
	}

}