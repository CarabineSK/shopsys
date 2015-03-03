<?php

namespace SS6\ShopBundle\Model\Category\Detail;

use SS6\ShopBundle\Model\Category\Category;

class CategoryDetail {

	/**
	 * @var \SS6\ShopBundle\Model\Category\Category
	 */
	private $category;

	/**
	 * @var \SS6\ShopBundle\Model\Category\Detail\CategoryDetail[]
	 */
	private $children;

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param \SS6\ShopBundle\Model\Category\Detail\CategoryDetail[] $children
	 */
	public function __construct(
		Category $category,
		array $children
	) {
		$this->category = $category;
		$this->children = $children;
	}

	public function getCategory() {
		return $this->category;
	}

	public function getChildren() {
		return $this->children;
	}

}
