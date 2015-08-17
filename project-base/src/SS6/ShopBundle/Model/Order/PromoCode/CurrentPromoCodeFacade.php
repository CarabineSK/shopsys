<?php

namespace SS6\ShopBundle\Model\Order\PromoCode;

use SS6\ShopBundle\Model\Order\PromoCode\PromoCodeFacade;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentPromoCodeFacade {

	const PROMO_CODE_SESSION_KEY = 'promoCode';

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	private $session;

	/**
	 * @var \SS6\ShopBundle\Model\Order\PromoCode\PromoCodeFacade
	 */
	private $promoCodeFacade;

	public function __construct(PromoCodeFacade $promoCodeFacade, SessionInterface $session) {
		$this->promoCodeFacade = $promoCodeFacade;
		$this->session = $session;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Order\PromoCode\PromoCode|null
	 */
	public function getValidEnteredPromoCode() {
		$enteredCode = $this->session->get(self::PROMO_CODE_SESSION_KEY);

		return $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
	}

	/**
	 * @param string $enteredCode
	 */
	public function setEnteredPromoCode($enteredCode) {
		$promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
		if ($promoCode === null) {
			throw new \SS6\ShopBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException($enteredCode);
		} else {
			$this->session->set(self::PROMO_CODE_SESSION_KEY, $enteredCode);
		}
	}

	public function removeEnteredPromoCode() {
		$this->session->remove(self::PROMO_CODE_SESSION_KEY);
	}
}
