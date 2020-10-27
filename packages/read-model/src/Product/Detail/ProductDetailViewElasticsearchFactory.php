<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFactory;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Parameter\ParameterViewFactory;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;
use Shopsys\ReadModelBundle\Product\PriceFactory;

class ProductDetailViewElasticsearchFactory
{
    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory
     */
    protected $productActionViewFactory;

    /**
     * @var \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory
     */
    protected $parameterViewFactory;

    /**
     * @var \Shopsys\ReadModelBundle\Brand\BrandViewFactory
     */
    protected $brandViewFactory;

    /**
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory $parameterViewFactory
     * @param \Shopsys\ReadModelBundle\Brand\BrandViewFactory $brandViewFactory
     */
    public function __construct(
        ImageViewFacadeInterface $imageViewFacade,
        CurrentCustomerUser $currentCustomerUser,
        ProductActionViewFactory $productActionViewFactory,
        ParameterViewFactory $parameterViewFactory,
        BrandViewFactory $brandViewFactory
    ) {
        $this->imageViewFacade = $imageViewFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productActionViewFactory = $productActionViewFactory;
        $this->parameterViewFactory = $parameterViewFactory;
        $this->brandViewFactory = $brandViewFactory;
    }

    /**
     * @param array $product
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function createFromElasticsearchProduct(array $product): ProductDetailView
    {
        $parameterViews = [];
        foreach ($product['parameters'] as $parameter) {
            $parameterViews[] = $this->parameterViewFactory->createFromElasticsearchParameter($parameter);
        }

        return $this->createInstanceFromElasticsearch(
            $product,
            $this->imageViewFacade->getAllImagesByEntityId(Product::class, $product['id']),
            $parameterViews,
            $this->brandViewFactory->createFromElasticsearchProduct($product)
        );
    }

    /**
     * @param array $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $imageViews
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterView[] $parameterViews
     * @param \Shopsys\ReadModelBundle\Brand\BrandView $brandView
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    protected function createInstanceFromElasticsearch(
        array $product,
        array $imageViews,
        array $parameterViews,
        BrandView $brandView
    ): ProductDetailView {
        return new ProductDetailView(
            $product['id'],
            $product['seo_h1'] ?: $product['name'],
            $product['description'],
            $product['availability'],
            PriceFactory::createProductPriceFromArrayByPricingGroup(
                $product['prices'],
                $this->currentCustomerUser->getPricingGroup()
            ),
            $product['catnum'],
            $product['partno'],
            $product['ean'],
            $product['main_category_id'],
            $product['calculated_selling_denied'],
            $product['in_stock'],
            $product['is_main_variant'],
            $product['main_variant_id'],
            $product['flags'],
            $product['seo_title'] ?: $product['name'],
            $product['seo_meta_description'],
            $this->productActionViewFactory->createFromArray($product),
            $brandView,
            $this->getMainImageView($imageViews),
            $imageViews,
            $parameterViews
        );
    }

    /**
     * @param array $imageViews
     * @return \Shopsys\ReadModelBundle\Image\ImageView|null
     */
    protected function getMainImageView(array $imageViews): ?ImageView
    {
        return Utils::getArrayValue($imageViews, 0, null);
    }
}
