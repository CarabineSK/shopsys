<?php

namespace SS6\ShopBundle\Form\Admin\PromoCode;

use SS6\ShopBundle\Component\Constraints\NotInArray;
use SS6\ShopBundle\Form\FormType;
use SS6\ShopBundle\Model\Order\PromoCode\PromoCodeData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class PromoCodeFormType extends AbstractType {

	/**
	 * @var string[]
	 */
	private $prohibitedCodes;

	/**
	 * @param string[] $prohibitedCodes
	 */
	public function __construct(array $prohibitedCodes) {
		$this->prohibitedCodes = $prohibitedCodes;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'promo_code_form';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('code', FormType::TEXT, [
				'required' => true,
				'constraints' => [
					new Constraints\NotBlank([
						'message' => 'Vyplňte prosím kód.',
					]),
					new NotInArray([
						'array' => $this->prohibitedCodes,
						'message' => 'Slevový kupón s tímto kódem již existuje.',
					]),
				],
			])
			->add('percent', FormType::INTEGER, [
				'required' => true,
				'constraints' => [
					new Constraints\NotBlank([
						'message' => 'Vyplňte prosím procento slevy.',
					]),
					new Constraints\Range([
						'min' => 0,
						'max' => 100,
					]),
				],
				'invalid_message' => 'Zadejte prosím celé číslo.',
			]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => PromoCodeData::class,
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
