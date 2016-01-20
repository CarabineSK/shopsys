<?php

namespace SS6\ShopBundle\Form\Admin\Script;

use SS6\ShopBundle\Component\Transformers\ScriptPlacementToBooleanTransformer;
use SS6\ShopBundle\Form\FormType;
use SS6\ShopBundle\Model\Script\ScriptData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class ScriptFormType extends AbstractType {

	/**
	 * @return string
	 */
	public function getName() {
		return 'script_form';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', FormType::TEXT, [
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Vyplňte prosím název skriptu']),
				],
			])
			->add('code', FormType::TEXTAREA, [
				'constraints' => [
					new Constraints\NotBlank(['message' => 'Vyplňte prosím kód skriptu']),
				],
			])
			->add($builder
				->create('placement', FormType::CHECKBOX, ['required' => false])
				->addModelTransformer(new ScriptPlacementToBooleanTransformer()))
			->add('save', FormType::SUBMIT);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'data_class' => ScriptData::class,
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
