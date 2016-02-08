<?php

namespace SS6\ShopBundle\Form\Admin\Slider;

use SS6\ShopBundle\Form\FormType;
use SS6\ShopBundle\Model\Slider\SliderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class SliderItemFormType extends AbstractType {

	/**
	 * @var bool
	 */
	private $scenarioCreate;

	/**
	 * @param bool $scenarioCreate
	 */
	public function __construct($scenarioCreate = false) {
		$this->scenarioCreate = $scenarioCreate;
	}

	public function getName() {
		return 'slider_item_form';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', FormType::TEXT, [
				'required' => true,
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Prosím vyplňte název']),
				],
			])
			->add('image', FormType::FILE_UPLOAD, [
				'required' => $this->scenarioCreate,
				'file_constraints' => [
					new Constraints\Image([
						'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
						'mimeTypesMessage' => 'Obrázek může být pouze ve formátech jpg nebo png',
						'maxSize' => '2M',
						'maxSizeMessage' => 'Nahraný obrázek ({{ size }} {{ suffix }}) může mít velikost maximálně {{ limit }} {{ suffix }}',
					]),
				],
			])
			->add('link', FormType::URL, [
				'required' => true,
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Prosím vyplňte odkaz']),
					new Constraints\Url(['message' => 'Odkaz musí být validní URL adresa']),
				],
			])
			->add('save', FormType::SUBMIT);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => SliderItemData::class,
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
