<?php

namespace SS6\ShopBundle\Form\Admin\Product;

use SS6\ShopBundle\Component\Transformers\RemoveDuplicatesFromArrayTransformer;
use SS6\ShopBundle\Form\Admin\Product\Parameter\ProductParameterValueFormTypeFactory;
use SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory;
use SS6\ShopBundle\Model\Domain\Domain;
use SS6\ShopBundle\Model\Image\ImageFacade;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Seo\SeoSettingFacade;

class ProductEditFormTypeFactory {

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Product\Parameter\ProductParameterValueFormTypeFactory
	 */
	private $productParameterValueFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Image\ImageFacade
	 */
	private $imageFacade;

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory
	 */
	private $productFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade
	 */
	private $pricingGroupFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Model\Seo\SeoSettingFacade
	 */
	private $seoSettingFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Transformers\RemoveDuplicatesFromArrayTransformer
	 */
	private $removeDuplicatesFromArrayTransformer;

	public function __construct(
		ProductParameterValueFormTypeFactory $productParameterValueFormTypeFactory,
		ImageFacade $imageFacade,
		ProductFormTypeFactory $productFormTypeFactory,
		PricingGroupFacade $pricingGroupFacade,
		Domain $domain,
		SeoSettingFacade $seoSettingFacade,
		RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer
	) {
		$this->productParameterValueFormTypeFactory = $productParameterValueFormTypeFactory;
		$this->imageFacade = $imageFacade;
		$this->productFormTypeFactory = $productFormTypeFactory;
		$this->pricingGroupFacade = $pricingGroupFacade;
		$this->domain = $domain;
		$this->seoSettingFacade = $seoSettingFacade;
		$this->removeDuplicatesFromArrayTransformer = $removeDuplicatesFromArrayTransformer;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Product|null $product
	 * @return \SS6\ShopBundle\Form\Admin\Product\ProductFormType
	 */
	public function create(Product $product = null) {
		if ($product !== null) {
			$images = $this->imageFacade->getImagesByEntity($product, null);
		} else {
			$images = [];
		}

		$pricingGroups = $this->pricingGroupFacade->getAll();
		$domains = $this->domain->getAll();
		$metaDescriptionsIndexedByDomainId = [];
		foreach ($domains as $domain) {
			$domainId = $domain->getId();
			$metaDescriptionsIndexedByDomainId[$domainId] = $this->seoSettingFacade->getDescriptionMainPage($domainId);
		}

		return new ProductEditFormType(
			$images,
			$this->productParameterValueFormTypeFactory,
			$this->productFormTypeFactory,
			$pricingGroups,
			$domains,
			$metaDescriptionsIndexedByDomainId,
			$this->removeDuplicatesFromArrayTransformer,
			$product
		);
	}

}
