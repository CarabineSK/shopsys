<?php

namespace SS6\ShopBundle\Component\Transformers;

use Symfony\Component\Form\DataTransformerInterface;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductRepository;

class ProductIdToProductTransformer implements DataTransformerInterface {

	/**
	 * @var SS6\ShopBundle\Model\Product\ProductRepository
	 */
	private $productRepository;

	public function __construct(ProductRepository $productRepository) {
		$this->productRepository = $productRepository;
	}

	/**
	 * @param SS6\ShopBundle\Model\Product\Product|null $product
	 * @return int|null
	 */
	public function transform($product) {
		if ($product instanceof Product) {
			return $product->getId();
		}
		return null;
	}

	/**
	 * @param int $productId
	 * @return SS6\ShopBundle\Model\Product\Product|null
	 */
	public function reverseTransform($productId) {
		if (empty($productId)) {
			return null;
		}
		return $this->productRepository->getById($productId);
	}
}
