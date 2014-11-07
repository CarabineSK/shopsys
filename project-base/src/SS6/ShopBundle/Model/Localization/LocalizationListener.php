<?php

namespace SS6\ShopBundle\Model\Localization;

use SS6\ShopBundle\Model\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocalizationListener implements EventSubscriberInterface {

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
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event) {
		if ($event->isMasterRequest()) {
			$request = $event->getRequest();
			$request->setLocale($this->domain->getLocale());
		}
	}

	public static function getSubscribedEvents() {
		return array(
			// must be registered before the default Locale listener
			// see: http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
			KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
		);
	}

}
