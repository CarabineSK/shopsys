<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductDetailViewElasticsearchFacade implements ProductDetailViewFacadeInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository
     */
    protected $productElasticsearchRepository;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewFactory
     */
    protected $productDetailViewFactory;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory
     */
    protected $productDetailViewElasticsearchFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory
     */
    public function __construct(
        ProductElasticsearchRepository $productElasticsearchRepository,
        ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory
    ) {
        $this->productElasticsearchRepository = $productElasticsearchRepository;
        $this->productDetailViewElasticsearchFactory = $productDetailViewElasticsearchFactory;
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function getVisibleProductDetail(int $productId): ProductDetailView
    {
        return $this->productDetailViewElasticsearchFactory->createFromProductArray(
            $this->productElasticsearchRepository->getProductById($productId)
        );
    }
}
