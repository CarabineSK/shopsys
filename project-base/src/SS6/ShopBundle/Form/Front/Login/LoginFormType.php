<?php

namespace SS6\ShopBundle\Form\Front\Login;

use SS6\ShopBundle\Form\FormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class LoginFormType extends AbstractType {

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('email', FormType::TEXT, [
					'constraints' => [
						new Constraints\NotBlank(['message' => 'Vyplňte prosím email']),
						new Constraints\Email(),
					],
				]
			)
			->add('password', FormType::PASSWORD, [
					'constraints' => [
						new Constraints\NotBlank(['message' => 'Vyplňte prosím heslo']),
					],
				]
			)
			->add('login', FormType::SUBMIT);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'front_login';
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
