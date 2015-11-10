<?php

namespace SS6\ShopBundle\Form;

use SS6\ShopBundle\Component\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DomainType extends AbstractType {

	/**
	 * @var \SS6\ShopBundle\Component\Domain\Domain
	 */
	private $domain;

	/**
	 * @param \SS6\ShopBundle\Component\Domain\Domain $domain
	 */
	public function __construct(Domain $domain) {
		$this->domain = $domain;
	}

	/**
	 * @inheritdoc
	 */
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['domainConfigs'] = $this->domain->getAll();
	}

	/**
	 * @return string
	 */
	public function getParent() {
		return 'integer';
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'domain';
	}

}
