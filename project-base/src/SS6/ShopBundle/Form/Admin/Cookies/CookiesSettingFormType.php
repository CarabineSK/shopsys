<?php

namespace SS6\ShopBundle\Form\Admin\Cookies;

use SS6\ShopBundle\Form\FormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CookiesSettingFormType extends AbstractType {

	/**
	 * @var \SS6\ShopBundle\Model\Article\Article[]
	 */
	private $articles;

	/**
	 * @param \SS6\ShopBundle\Model\Article\Article[] $articles
	 */
	public function __construct(array $articles) {
		$this->articles = $articles;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'cookies_setting_form';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
			$builder
				->add('cookiesArticle', FormType::CHOICE, [
					'required' => false,
					'choice_list' => new ObjectChoiceList($this->articles, 'name', [], null, 'id'),
					'placeholder' => t('-- Vyberte článek --'),
				])
				->add('save', FormType::SUBMIT);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults([
			'attr' => ['novalidate' => 'novalidate'],
		]);
	}

}
