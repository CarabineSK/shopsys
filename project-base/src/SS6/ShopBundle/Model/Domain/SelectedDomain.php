<?php

namespace SS6\ShopBundle\Model\Domain;

use Symfony\Component\HttpFoundation\Session\Session;
use SS6\ShopBundle\Model\Domain\Domain;

class SelectedDomain {

	const SESSION_SELECTED_DOMAIN = 'selected_domain_id';

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\Session
	 */
	private $session;

	/**
	 * @param \SS6\ShopBundle\Model\Domain\Domain $domain
	 * @param \Symfony\Component\HttpFoundation\Session\Session $session
	 */
	public function __construct(Domain $domain, Session $session) {
		$this->domain = $domain;
		$this->session = $session;
	}

	public function getId() {
		return $this->getCurrentSelectedDomain()->getId();
	}

	/**
	 * @param int $domainId
	 */
	public function setId($domainId) {
		$domainConfig = $this->domain->getDomainConfigById($domainId);
		$this->session->set(self::SESSION_SELECTED_DOMAIN, $domainConfig->getId());
	}

	/**
	 * @return Config\DomainConfig
	 */
	public function getCurrentSelectedDomain() {
		try {
			$domainId = $this->session->get(self::SESSION_SELECTED_DOMAIN);
			return $this->domain->getDomainConfigById($domainId);
		} catch (\SS6\ShopBundle\Model\Domain\Exception\InvalidDomainIdException $e) {
			$allDomains = $this->domain->getAll();
			return reset($allDomains);
		}
	}

}
