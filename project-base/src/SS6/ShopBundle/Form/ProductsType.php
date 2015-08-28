<?php

namespace SS6\ShopBundle\Form;

use SS6\ShopBundle\Component\Transformers\ProductsIdsToProductsTransformer;
use SS6\ShopBundle\Form\FormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductsType extends AbstractType {

	/**
	 * @var \SS6\ShopBundle\Component\Transformers\ProductsIdsToProductsTransformer
	 */
	private $productsIdsToProductsTransformer;

	/**
	 * @param \SS6\ShopBundle\Component\Transformers\ProductsIdsToProductsTransformer $productsIdsToProductsTransformer
	 */
	public function __construct(ProductsIdsToProductsTransformer $productsIdsToProductsTransformer) {
		$this->productsIdsToProductsTransformer = $productsIdsToProductsTransformer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->addModelTransformer($this->productsIdsToProductsTransformer);
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['products'] = $form->getData();
		$view->vars['main_product'] = $options['main_product'];
		$view->vars['sortable'] = $options['sortable'];
		$view->vars['allow_variants'] = var_export($options['allow_variants'], true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'type' => FormType::HIDDEN,
			'allow_add' => true,
			'allow_delete' => true,
			'delete_empty' => true,
			'main_product' => null,
			'error_bubbling' => false,
			'sortable' => false,
			'allow_variants' => true,
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent() {
		return 'collection';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'products';
	}

}
