<?php

namespace SS6\ShopBundle\Tests\Component\Transformers;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Transformers\ProductIdToProductTransformer;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductRepository;

class ProductIdToProductTransformerTest extends PHPUnit_Framework_TestCase {

	public function testTransform() {
		$productId = 1;
		$product = $this->getMockBuilder(Product::class)
			->setMethods(['getId'])
			->disableOriginalConstructor()
			->getMock();
		$product->expects($this->atLeastOnce())->method('getId')->willReturn($productId);

		$productRepository = $this->getMock(ProductRepository::class, [], [], '', false);
		$productIdToProductTransformer = new ProductIdToProductTransformer($productRepository);

		$this->assertEquals($productId, $productIdToProductTransformer->transform($product));
		$this->assertNull($productIdToProductTransformer->transform(null));
	}

	public function testReverseTransform() {
		$productId = 1;
		$product = $this->getMockBuilder(Product::class, [], [], '', false);

		$productsRepositoryGetByIdValues = [
			[$productId, $product],
		];

		$productRepository = $this->getMockBuilder(ProductRepository::class)
			->setMethods(['getById'])
			->disableOriginalConstructor()
			->getMock();
		$productRepository->expects($this->atLeastOnce())->method('getById')->willReturnMap($productsRepositoryGetByIdValues);

		$productIdToProductTransformer = new ProductIdToProductTransformer($productRepository);

		$this->assertEquals($product, $productIdToProductTransformer->reverseTransform($productId));
		$this->assertNull($productIdToProductTransformer->reverseTransform(null));
	}
}