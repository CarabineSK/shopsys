<?php

namespace SS6\ShopBundle\Model\Customer;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Customer\CustomerEditService;
use SS6\ShopBundle\Model\Customer\Mail\CustomerMailFacade;
use SS6\ShopBundle\Model\Customer\RegistrationService;
use SS6\ShopBundle\Model\Order\Order;
use SS6\ShopBundle\Model\Order\OrderRepository;
use SS6\ShopBundle\Model\Order\OrderService;

class CustomerEditFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Order\OrderRepository
	 */
	private $orderRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Customer\UserRepository
	 */
	private $userRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Order\OrderService
	 */
	private $orderService;

	/**
	 * @var \SS6\ShopBundle\Model\Customer\RegistrationService
	 */
	private $registrationService;

	/**
	 * @var \SS6\ShopBundle\Model\Customer\CustomerEditService
	 */
	private $customerEditService;

	/**
	 * @var \SS6\ShopBundle\Model\Customer\Mail\CustomerMailFacade
	 */
	private $customerMailFacade;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Model\Order\OrderRepository $orderRepository
	 * @param \SS6\ShopBundle\Model\Customer\UserRepository $userRepository
	 * @param \SS6\ShopBundle\Model\Order\OrderService $orderService
	 * @param \SS6\ShopBundle\Model\Customer\RegistrationService $registrationService
	 * @param \SS6\ShopBundle\Model\Customer\CustomerEditService $customerEditService
	 */
	public function __construct(
		EntityManager $em,
		OrderRepository $orderRepository,
		UserRepository $userRepository,
		OrderService $orderService,
		RegistrationService $registrationService,
		CustomerEditService $customerEditService,
		CustomerMailFacade $customerMailFacade
	) {
		$this->em = $em;
		$this->orderRepository = $orderRepository;
		$this->userRepository = $userRepository;
		$this->orderService = $orderService;
		$this->registrationService = $registrationService;
		$this->customerEditService = $customerEditService;
		$this->customerMailFacade = $customerMailFacade;
	}

	/**
	 * @param int $userId
	 * @return \SS6\ShopBundle\Model\Customer\User
	 */
	public function getUserById($userId) {
		return $this->userRepository->getUserById($userId);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Customer\UserData $userData
	 * @return \SS6\ShopBundle\Model\Customer\User
	 */
	public function register(UserData $userData) {
		$userByEmailAndDomain = $this->userRepository->findUserByEmailAndDomain($userData->email, $userData->domainId);

		$billingAddress = new BillingAddress(new BillingAddressData());

		$user = $this->registrationService->create(
			$userData,
			$billingAddress,
			null,
			$userByEmailAndDomain
		);

		$this->em->persist($billingAddress);
		$this->em->persist($user);
		$this->em->flush();

		$this->customerMailFacade->sendRegistrationMail($user);

		return $user;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Customer\CustomerData $customerData
	 * @return \SS6\ShopBundle\Model\Customer\User
	 */
	public function create(CustomerData $customerData) {
		$billingAddress = new BillingAddress($customerData->billingAddressData);
		$this->em->persist($billingAddress);

		$deliveryAddress = $this->registrationService->createDeliveryAddress($customerData->deliveryAddressData);
		if ($deliveryAddress !== null) {
			$this->em->persist($deliveryAddress);
		}

		$userByEmailAndDomain = $this->userRepository->findUserByEmailAndDomain(
			$customerData->userData->email,
			$customerData->userData->domainId
		);

		$user = $this->registrationService->create(
			$customerData->userData,
			$billingAddress,
			$deliveryAddress,
			$userByEmailAndDomain
		);
		$this->em->persist($user);

		$this->em->flush([
			$billingAddress,
			$deliveryAddress,
			$user,
		]);

		if ($customerData->sendRegistrationMail) {
			$this->customerMailFacade->sendRegistrationMail($user);
		}

		return $user;
	}

	/**
	 * @param int $userId
	 * @param \SS6\ShopBundle\Model\Customer\CustomerData $customerData
	 * @return \SS6\ShopBundle\Model\Customer\User
	 */
	private function edit($userId, CustomerData $customerData) {
		$user = $this->userRepository->getUserById($userId);

		$this->registrationService->edit($user, $customerData->userData);

		$user->getBillingAddress()->edit($customerData->billingAddressData);

		$oldDeliveryAddress = $user->getDeliveryAddress();
		$deliveryAddress = $this->registrationService->editDeliveryAddress(
			$user,
			$customerData->deliveryAddressData,
			$oldDeliveryAddress
		);

		if ($deliveryAddress !== null) {
			$this->em->persist($deliveryAddress);
		} else {
			if ($oldDeliveryAddress !== null) {
				$this->em->remove($oldDeliveryAddress);
			}
		}

		return $user;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Customer\CustomerData $customerData
	 * @return \SS6\ShopBundle\Model\Customer\User
	 */
	public function editByAdmin($userId, CustomerData $customerData) {
		$user = $this->edit($userId, $customerData);

		$userByEmailAndDomain = $this->userRepository->findUserByEmailAndDomain(
			$customerData->userData->email,
			$customerData->userData->domainId
		);
		$this->registrationService->changeEmail($user, $customerData->userData->email, $userByEmailAndDomain);

		$this->em->flush();

		return $user;
	}

	/**
	 * @param int $userId
	 * @param \SS6\ShopBundle\Model\Customer\CustomerData $customerData
	 * @return \SS6\ShopBundle\Model\Customer\User
	 */
	public function editByCustomer($userId, CustomerData $customerData) {
		$user = $this->edit($userId, $customerData);

		$this->em->flush();

		return $user;
	}

	/**
	 * @param int $userId
	 */
	public function delete($userId) {
		$user = $this->userRepository->getUserById($userId);

		$this->em->remove($user);
		$this->em->flush();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Customer\User $user
	 * @param \SS6\ShopBundle\Model\Order\Order $order
	 */
	public function amendCustomerDataFromOrder(User $user, Order $order) {
		$this->edit(
			$user->getId(),
			$this->customerEditService->getAmendedCustomerDataByOrder($user, $order)
		);

		$this->em->flush();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $oldPricingGroup
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroup $newPricingGroup
	 */
	public function replaceOldPricingGroupWithNewPricingGroup($oldPricingGroup, $newPricingGroup) {
		$users = $this->userRepository->getAllByPricingGroup($oldPricingGroup);
		foreach ($users as $user) {
			$user->setPricingGroup($newPricingGroup);
		}
		$this->em->flush();
	}

}
