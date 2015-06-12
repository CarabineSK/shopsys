<?php

namespace SS6\ShopBundle\Model\Payment;

use SS6\ShopBundle\Model\Domain\Domain;
use SS6\ShopBundle\Model\Payment\PaymentRepository;

class IndependentPaymentVisibilityCalculation {

	/**
	 * @var \SS6\ShopBundle\Model\Payment\PaymentRepository
	 */
	private $paymentRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	public function __construct(
		PaymentRepository $paymentRepository,
		Domain $domain
	) {
		$this->paymentRepository = $paymentRepository;
		$this->domain = $domain;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Payment\Payment $payment
	 * @param int $domainId
	 * @return bool
	 */
	public function isIndependentlyVisible(Payment $payment, $domainId) {
		$locale = $this->domain->getDomainConfigById($domainId)->getLocale();

		if (strlen($payment->getName($locale)) === 0) {
			return false;
		}

		if ($payment->isHidden()) {
			return false;
		}

		if (!$this->isOnDomain($payment, $domainId)) {
			return false;
		}

		return true;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Payment\Payment $payment
	 * @param int $domainId
	 * @return bool
	 */
	private function isOnDomain(Payment $payment, $domainId) {
		$paymentDomains = $this->paymentRepository->getPaymentDomainsByPayment($payment);
		foreach ($paymentDomains as $paymentDomain) {
			if ($paymentDomain->getDomainId() === $domainId) {
				return true;
			}
		}

		return false;
	}

}
