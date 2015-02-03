<?php

namespace SS6\ShopBundle\Model\AdvancedSearch;

use SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use SS6\ShopBundle\Model\AdvancedSearch\Filter\ProductCatnumFilter;
use SS6\ShopBundle\Model\AdvancedSearch\Filter\ProductNameFilter;
use SS6\ShopBundle\Model\AdvancedSearch\Filter\ProductPartnoFilter;

class AdvancedSearchConfig {

	/**
	 * @var \SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface[]
	 */
	private $filters;

	public function __construct(
		ProductCatnumFilter $productCatnumFilter,
		ProductNameFilter $productNameFilter,
		ProductPartnoFilter $productPartnoFilter
	) {
		$this->filters = [];

		$this->registerFilter($productNameFilter);
		$this->registerFilter($productCatnumFilter);
		$this->registerFilter($productPartnoFilter);
	}

	/**
	 * @param \SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface $filter
	 */
	public function registerFilter(AdvancedSearchFilterInterface $filter) {
		if (array_key_exists($filter->getName(), $this->filters)) {
			$message = 'Filter "' . $filter->getName() . '" already exists.';
			throw new \SS6\ShopBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterAlreadyExistsException($message);
		}

		$this->filters[$filter->getName()] = $filter;
	}

	/**
	 * @return \SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface[]
	 */
	public function getAllFilters() {
		return $this->filters;
	}

	/**
	 * @param string $filterName
	 * @return \SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface
	 */
	public function getFilter($filterName) {
		if (!array_key_exists($filterName, $this->filters)) {
			$message = 'Filter "' . $filterName . '" not found.';
			throw new \SS6\ShopBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterNotFoundException($message);
		}

		return $this->filters[$filterName];
	}
}
