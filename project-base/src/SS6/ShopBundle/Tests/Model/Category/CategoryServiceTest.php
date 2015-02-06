<?php

namespace SS6\ShopBundle\Tests\Model\Category;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Category\Category;
use SS6\ShopBundle\Model\Category\CategoryData;
use SS6\ShopBundle\Model\Category\CategoryService;

class CategoryServiceTest extends PHPUnit_Framework_TestCase {

	public function testCreateSetRoot() {
		$categoryData = new CategoryData();
		$rootCategory = new Category($categoryData);

		$categoryService = new CategoryService();
		$category = $categoryService->create($categoryData, $rootCategory);

		$this->assertEquals($rootCategory, $category->getParent());
	}

	public function testCreate() {
		$rootCategory = new Category(new CategoryData());
		$parentCategory = new Category(new CategoryData());
		$categoryData = new CategoryData();
		$categoryData->parent = $parentCategory;

		$categoryService = new CategoryService();
		$category = $categoryService->create($categoryData, $rootCategory);

		$this->assertEquals($parentCategory, $category->getParent());
	}

	public function testEditSetRoot() {
		$categoryData = new CategoryData();
		$rootCategory = new Category($categoryData);
		$category = new Category(new CategoryData());

		$categoryService = new CategoryService();
		$categoryService->edit($category, $categoryData, $rootCategory);

		$this->assertEquals($rootCategory, $category->getParent());
	}

	public function testEdit() {
		$rootCategory = new Category(new CategoryData());
		$parentCategory = new Category(new CategoryData());
		$categoryData = new CategoryData();
		$categoryData->parent = $parentCategory;
		$category = new Category(new CategoryData());

		$categoryService = new CategoryService();
		$categoryService->edit($category, $categoryData, $rootCategory);

		$this->assertEquals($parentCategory, $category->getParent());
	}

}
