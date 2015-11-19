<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Product;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductData;

class ProductTest extends PHPUnit_Framework_TestCase {

	public function testNoVariant() {
		$productData = new ProductData();
		$product = new Product($productData);

		$this->assertFalse($product->isVariant());
		$this->assertFalse($product->isMainVariant());
	}

	public function testIsVariant() {
		$productData = new ProductData();
		$variant = new Product($productData);
		new Product($productData, [$variant]);

		$this->assertTrue($variant->isVariant());
		$this->assertFalse($variant->isMainVariant());
	}

	public function testIsMainVariant() {
		$productData = new ProductData();
		$variant = new Product($productData);
		$mainVariant = new Product($productData, [$variant]);

		$this->assertFalse($mainVariant->isVariant());
		$this->assertTrue($mainVariant->isMainVariant());
	}

	public function testGetMainVariant() {
		$productData = new ProductData();
		$variant = new Product($productData);
		$mainVariant = new Product($productData, [$variant]);

		$this->assertSame($mainVariant, $variant->getMainVariant());
	}

	public function testGetMainVariantException() {
		$productData = new ProductData();
		$product = new Product($productData);

		$this->setExpectedException(\SS6\ShopBundle\Model\Product\Exception\ProductIsNotVariantException::class);
		$product->getMainVariant();
	}

	public function testCreateVariantFromVariantException() {
		$productData = new ProductData();
		$variant = new Product($productData);
		$variant2 = new Product($productData);
		$mainVariant = new Product($productData, [$variant]);
		new Product($productData, [$variant2]);

		$this->setExpectedException(\SS6\ShopBundle\Model\Product\Exception\ProductIsAlreadyVariantException::class);
		$mainVariant->addVariant($variant2);
	}

	public function testCreateVariantFromMainVariantException() {
		$productData = new ProductData();
		$variant = new Product($productData);
		$variant2 = new Product($productData);
		$mainVariant = new Product($productData, [$variant]);
		$mainVariant2 = new Product($productData, [$variant2]);

		$this->setExpectedException(\SS6\ShopBundle\Model\Product\Exception\MainVariantCannotBeVariantException::class);
		$mainVariant->addVariant($mainVariant2);
	}

	public function testCreateMainVariantFromVariantException() {
		$productData = new ProductData();
		$variant = new Product($productData);
		$variant2 = new Product($productData);
		$variant3 = new Product($productData);
		new Product($productData, [$variant]);
		new Product($productData, [$variant2]);

		$this->setExpectedException(\SS6\ShopBundle\Model\Product\Exception\VariantCanBeAddedOnlyToMainVariantException::class);
		$variant2->addVariant($variant3);
	}

	public function testAddSelfAsVariantException() {
		$productData = new ProductData();
		$variant = new Product($productData);
		$mainVariant = new Product($productData, [$variant]);

		$this->setExpectedException(\SS6\ShopBundle\Model\Product\Exception\MainVariantCannotBeVariantException::class);
		$mainVariant->addVariant($mainVariant);
	}

}
