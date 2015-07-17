<?php

namespace SS6\ShopBundle\Form\Admin\Order;

use SS6\ShopBundle\Form\FormType;
use SS6\ShopBundle\Model\Order\Item\OrderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class OrderItemFormType extends AbstractType {

	/**
	 * @return string
	 */
	public function getName() {
		return 'order_item_form';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', FormType::TEXT, [
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Vyplňte prosím název']),
				],
				'error_bubbling' => true,
			])
			->add('catnum', FormType::TEXT, [
				'constraints' => [
					new Constraints\Length(['max' => '255']),
				],
				'error_bubbling' => true,
			])
			->add('priceWithVat', FormType::MONEY, [
				'currency' => false,
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Vyplňte prosím jednotkovou cenu s DPH']),
				],
				'error_bubbling' => true,
			])
			->add('vatPercent', FormType::MONEY, [
				'currency' => false,
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Vyplňte prosím sazbu DPH']),
				],
				'error_bubbling' => true,
			])
			->add('quantity', FormType::INTEGER, [
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Vyplňte prosím množství']),
					new Constraints\GreaterThan(['value' => 0, 'message' => 'Množství musí být větší než 0']),
				],
				'error_bubbling' => true,
			]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => OrderItemData::class,
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
