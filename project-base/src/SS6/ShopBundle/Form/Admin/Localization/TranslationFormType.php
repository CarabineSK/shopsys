<?php

namespace SS6\ShopBundle\Form\Admin\Localization;

use SS6\ShopBundle\Component\Translation\Translator;
use SS6\ShopBundle\Form\FormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class TranslationFormType extends AbstractType implements DataTransformerInterface {

	/**
	 * @var string[]
	 */
	private $locales;

	/**
	 * @param string[] $locales
	 */
	public function __construct(array $locales) {
		$this->locales = $locales;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function transform($value) {
		if (preg_match('/^' . preg_quote(Translator::NOT_TRANSLATED_PREFIX) . '/u', $value)) {
			return '';
		}

		return $value;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function reverseTransform($value) {
		return $value;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'translation';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		foreach ($this->locales as $locale) {
			$builder->add(
				$builder
					->create($locale, FormType::TEXTAREA, [
						'required' => true,
						'constraints' => new Constraints\NotBlank(['message' => 'Vyplňte prosím překlad']),
					])
					->addModelTransformer($this)
			);
		}
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
