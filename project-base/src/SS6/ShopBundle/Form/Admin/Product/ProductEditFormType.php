<?php

namespace SS6\ShopBundle\Form\Admin\Product;

use SS6\ShopBundle\Component\Constraints\UniqueCollection;
use SS6\ShopBundle\Component\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer;
use SS6\ShopBundle\Form\Admin\Product\Parameter\ProductParameterValueFormTypeFactory;
use SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory;
use SS6\ShopBundle\Form\FormType;
use SS6\ShopBundle\Form\ValidationGroup;
use SS6\ShopBundle\Model\Domain\Config\DomainConfig;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductEditData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class ProductEditFormType extends AbstractType {

	const INTENTION = 'product_edit_type';
	const VALIDATION_GROUP_MANUAL_PRICE_CALCULATION = 'manualPriceCalculation';

	/**
	 * @var \SS6\ShopBundle\Model\Image\Image[]
	 */
	private $images;

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Product\Parameter\ProductParameterValueFormTypeFactory
	 */
	private $productParameterValueFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory
	 */
	private $productFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroup[]
	 */
	private $pricingGroups;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Config\DomainConfig[]
	 */
	private $domains;

	/**
	 * @var string[]
	 */
	private $metaDescriptionsIndexedByDomainId;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Product|null
	 */
	private $product;

	/**
	 * @param \SS6\ShopBundle\Model\Image\Image[] $images
	 * @param \SS6\ShopBundle\Form\Admin\Product\Parameter\ProductParameterValueFormTypeFactory $productParameterValueFormTypeFactory
	 * @param \SS6\ShopBundle\Form\Admin\Product\ProductFormTypeFactory
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup[] $pricingGroups
	 * @param \SS6\ShopBundle\Model\Domain\Config\DomainConfig[] $domains
	 * @param string[] $metaDescriptionsIndexedByDomainId
	 * @param \SS6\ShopBundle\Model\Product\Product|null $product
	 */
	public function __construct(
		array $images,
		ProductParameterValueFormTypeFactory $productParameterValueFormTypeFactory,
		ProductFormTypeFactory $productFormTypeFactory,
		array $pricingGroups,
		array $domains,
		array $metaDescriptionsIndexedByDomainId,
		Product $product = null
	) {
		$this->images = $images;
		$this->productParameterValueFormTypeFactory = $productParameterValueFormTypeFactory;
		$this->productFormTypeFactory = $productFormTypeFactory;
		$this->pricingGroups = $pricingGroups;
		$this->domains = $domains;
		$this->metaDescriptionsIndexedByDomainId = $metaDescriptionsIndexedByDomainId;
		$this->product = $product;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'product_edit_form';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('productData', $this->productFormTypeFactory->create($this->product))
			->add('imagesToUpload', FormType::FILE_UPLOAD, [
				'required' => false,
				'multiple' => true,
				'file_constraints' => [
					new Constraints\Image([
						'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
						'mimeTypesMessage' => 'Obrázek může být pouze ve formátech jpg, png nebo gif',
						'maxSize' => '2M',
						'maxSizeMessage' => 'Nahraný obrázek ({{ size }} {{ suffix }}) může mít velikost maximálně {{ limit }} {{ suffix }}',
					]),
				],
			])
			->add('imagesToDelete', FormType::CHOICE, [
				'required' => false,
				'multiple' => true,
				'expanded' => true,
				'choice_list' => new ObjectChoiceList($this->images, 'filename', [], null, 'id'),
			])
			->add($builder->create('parameters', FormType::COLLECTION, [
					'required' => false,
					'allow_add' => true,
					'allow_delete' => true,
					'type' => $this->productParameterValueFormTypeFactory->create(),
					'constraints' => [
						new UniqueCollection([
							'fields' => ['parameter', 'locale'],
							'message' => 'Každý parametr může být nastaven pouze jednou',
						]),
					],
					'error_bubbling' => false,
				])
				->addViewTransformer(new ProductParameterValueToProductParameterValuesLocalizedTransformer())
			)
			->add('manualInputPrices', FormType::FORM, [
				'compound' => true,
			])
			->add('seoTitles', FormType::FORM, [
				'compound' => true,
			])
			->add('seoMetaDescriptions', FormType::FORM, [
				'compound' => true,
			])
			->add('urls', FormType::URL_LIST, [
				'route_name' => 'front_product_detail',
				'entity_id' => $this->product === null ? null : $this->product->getId(),
			])
			->add('save', FormType::SUBMIT);

		foreach ($this->pricingGroups as $pricingGroup) {
			$builder->get('manualInputPrices')
				->add($pricingGroup->getId(), FormType::MONEY, [
					'currency' => false,
					'precision' => 6,
					'required' => true,
					'invalid_message' => 'Prosím zadejte cenu v platném formátu (kladné číslo s desetinnou čárkou nebo tečkou)',
					'constraints' => [
						new Constraints\NotBlank([
							'message' => 'Prosím vyplňte cenu',
							'groups' => [self::VALIDATION_GROUP_MANUAL_PRICE_CALCULATION],
						]),
						new Constraints\GreaterThanOrEqual([
							'value' => 0,
							'message' => 'Cena musí být větší nebo rovna {{ compared_value }}',
							'groups' => [self::VALIDATION_GROUP_MANUAL_PRICE_CALCULATION],
						]),
					],
				]);
		}

		foreach ($this->domains as $domainConfig) {
			$builder->get('seoTitles')
				->add($domainConfig->getId(), FormType::TEXT, [
					'required' => false,
					'attr' => [
						'placeholder' => $this->getTitlePlaceholder($domainConfig),
					]
				]);
			$builder->get('seoMetaDescriptions')
				->add($domainConfig->getId(), FormType::TEXTAREA, [
					'required' => false,
					'attr' => [
						'placeholder' => $this->getMetaDescriptionPlaceholder($domainConfig),
					]
				]);
		}
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => ProductEditData::class,
			'attr' => ['novalidate' => 'novalidate'],
			'intention' => self::INTENTION,
			'validation_groups' => function (FormInterface $form) {
				$validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];
				$productData = $form->getData()->productData;
				/* @var $productData \SS6\ShopBundle\Model\Product\ProductData */

				if ($productData->priceCalculationType === Product::PRICE_CALCULATION_TYPE_MANUAL) {
					$validationGroups[] = self::VALIDATION_GROUP_MANUAL_PRICE_CALCULATION;
				}

				return $validationGroups;
			},
		]);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Domain\Config\DomainConfig $domainConfig
	 * @return string
	 */
	private function getTitlePlaceholder(DomainConfig $domainConfig) {
		if ($this->product === null) {
			return '';
		} else {
			return $this->product->getName($domainConfig->getLocale());
		}
	}

	/**
	 * @param \SS6\ShopBundle\Model\Domain\Config\DomainConfig $domainConfig
	 * @return string
	 */
	private function getMetaDescriptionPlaceholder(DomainConfig $domainConfig) {
		return $this->metaDescriptionsIndexedByDomainId[$domainConfig->getId()];
	}

}
