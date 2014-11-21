<?php

namespace SS6\ShopBundle\Component\Router;

use SS6\ShopBundle\Component\Router\DomainRouterFactory;
use SS6\ShopBundle\Model\Domain\Domain;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class CurrentDomainRouter implements RouterInterface {

	/**
	 * @var \Symfony\Component\Routing\RequestContext
	 */
	private $context;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	/**
	 *
	 * @var \SS6\ShopBundle\Component\Router\LocalizedRouterFactory
	 */
	private $domainRouterFactory;

	public function __construct(Domain $domain, DomainRouterFactory $domainRouterFactory) {
		$this->domain = $domain;
		$this->domainRouterFactory = $domainRouterFactory;
	}

	/**
	 * @return \Symfony\Component\Routing\RequestContext
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @param \Symfony\Component\Routing\RequestContext $context
	 */
	public function setContext(RequestContext $context) {
		$this->context = $context;
	}

	/**
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	public function getRouteCollection() {
		return $this->getDomainRouter()->getRouteCollection();
	}

	/**
	 * @param string $name
	 * @param array $parameters
	 * @param bool $referenceType
	 * @return string
	 */
	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH) {
		return $this->getDomainRouter()->generate($name, $parameters, $referenceType);
	}

	/**
	 * @param string $pathinfo
	 * @return array
	 */
	public function match($pathinfo) {
		return $this->getDomainRouter()->match($pathinfo);
	}

	/**
	 * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
	 */
	private function getDomainRouter() {
		return $this->domainRouterFactory->getRouter($this->domain->getid());
	}

}
