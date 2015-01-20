<?php

namespace SS6\ShopBundle\Form\Front\Customer;

use SS6\ShopBundle\Form\Front\Customer\UserFormType;
use SS6\ShopBundle\Model\Customer\CustomerData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomerFormType extends AbstractType {

	/**
	 * @return string
	 */
	public function getName() {
		return 'customer';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('userData', new UserFormType())
			->add('billingAddressData', new BillingAddressFormType())
			->add('deliveryAddressData', new DeliveryAddressFormType())
			->add('save', 'submit');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => CustomerData::class,
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
