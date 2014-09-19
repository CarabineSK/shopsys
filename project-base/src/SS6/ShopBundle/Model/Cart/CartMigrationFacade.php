<?php

namespace SS6\ShopBundle\Model\Cart;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Cart\CartFactory;
use SS6\ShopBundle\Model\Customer\CustomerIdentifier;
use SS6\ShopBundle\Model\Customer\CustomerIdentifierFactory;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class CartMigrationFacade {

	const SESSION_PREVIOUS_ID = 'previous_id';
	
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;
	
	/**
	 * @var \SS6\ShopBundle\Model\Cart\CartService
	 */
	private $cartService;
	
	/**
	 * @var \SS6\ShopBundle\Model\Customer\CustomerIdentifier
	 */
	private $customerIdentifier;
	
	/**
	 * 
	 * @var \SS6\ShopBundle\Model\Cart\CartFactory
	 */
	private $cartFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Customer\CustomerIdentifierFactory
	 */
	private $customerIdentifierFactory;
	
	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Model\Cart\CartService $cartService
	 * @param \SS6\ShopBundle\Model\Customer\CustomerIdentifier
	 * @param \SS6\ShopBundle\Model\Cart\CartFactory $cartFactory
	 * @param \SS6\ShopBundle\Model\Customer\CustomerIdentifierFactory
	 */
	public function __construct(
		EntityManager $em,
		CartService $cartService,
		CustomerIdentifier $customerIdentifier,
		CartFactory $cartFactory,
		CustomerIdentifierFactory $customerIdentifierFactory
	) {
		$this->em = $em;
		$this->cartService = $cartService;
		$this->customerIdentifier = $customerIdentifier;
		$this->cartFactory = $cartFactory;
		$this->customerIdentifierFactory = $customerIdentifierFactory;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Cart\Cart $cart
	 */
	private function mergeCurrentCartWithCart(Cart $cart) {
		$currentCart = $this->cartFactory->get($this->customerIdentifier);
		$this->cartService->mergeCarts($currentCart, $cart, $this->customerIdentifier);

		foreach ($cart->getItems() as $itemToRemove) {
			$this->em->remove($itemToRemove);
		}

		foreach ($currentCart->getItems() as $item) {
			$this->em->persist($item);
		}

		$this->em->flush();
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $filterControllerEvent
	 */
	public function onKernelController(FilterControllerEvent $filterControllerEvent) {
		$session = $filterControllerEvent->getRequest()->getSession();

		$previousId = $session->get(self::SESSION_PREVIOUS_ID);
		if (!empty($previousId) && $previousId !== $session->getId()) {
			$previousCustomerIdentifier = $this->customerIdentifierFactory->getOnlyWithSessionId($previousId);
			$cart = $this->cartFactory->get($previousCustomerIdentifier);
			$this->mergeCurrentCartWithCart($cart);
		}
		$session->set(self::SESSION_PREVIOUS_ID, $session->getId());
	}
}
