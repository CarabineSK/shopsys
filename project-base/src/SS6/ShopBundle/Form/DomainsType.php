<?php

namespace SS6\ShopBundle\Form;

use SS6\ShopBundle\Form\Extension\IndexedChoiceList;
use SS6\ShopBundle\Model\Domain\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DomainsType extends AbstractType {

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	/**
	 * @param \SS6\ShopBundle\Model\Domain\Domain $domain
	 */
	public function __construct(Domain $domain) {
		$this->domain = $domain;
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$ids = [];
		$labels = [];
		$values = [];
		foreach ($this->domain->getAll() as $domainConfig) {
			$ids[] = $domainConfig->getId();
			$labels[] = $domainConfig->getName();
			$values[] = (string)$domainConfig->getId();
		}

		$resolver->setDefaults([
			'choice_list' => new IndexedChoiceList($ids, $labels, $ids, $values),
			'multiple' => true,
			'expanded' => true,
		]);
	}

	/**
	 * @return string
	 */
	public function getParent() {
		return 'choice';
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'domains';
	}

}
