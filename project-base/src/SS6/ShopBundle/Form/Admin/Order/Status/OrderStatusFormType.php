<?php

namespace SS6\ShopBundle\Form\Admin\Order\Status;

use SS6\ShopBundle\Model\Order\Status\OrderStatusData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class OrderStatusFormType extends AbstractType {

	/**
	 * @return string
	 */
	public function getName() {
		return 'order_status';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', 'localized', [
				'options' => [
					'constraints' => [
						new Constraints\NotBlank(['message' => 'Vyplňte prosím všechny názvy stavu']),
						new Constraints\Length(['max' => 100, 'maxMessage' => 'Název stavu nesmí být delší než {{ limit }} znaků']),
					],
				],
			]);
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => OrderStatusData::class,
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
