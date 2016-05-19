<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Customer;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Customer\BillingAddress;
use SS6\ShopBundle\Model\Customer\BillingAddressData;
use SS6\ShopBundle\Model\Customer\CustomerPasswordService;
use SS6\ShopBundle\Model\Customer\CustomerService;
use SS6\ShopBundle\Model\Customer\DeliveryAddress;
use SS6\ShopBundle\Model\Customer\DeliveryAddressData;
use SS6\ShopBundle\Model\Customer\User;
use SS6\ShopBundle\Model\Customer\UserData;
use SS6\ShopBundle\Model\Order\Order;
use SS6\ShopBundle\Model\Order\OrderData;
use SS6\ShopBundle\Model\Order\Status\OrderStatus;
use SS6\ShopBundle\Model\Order\Status\OrderStatusData;
use SS6\ShopBundle\Model\Payment\Payment;
use SS6\ShopBundle\Model\Payment\PaymentData;
use SS6\ShopBundle\Model\Transport\Transport;
use SS6\ShopBundle\Model\Transport\TransportData;

class CustomerServiceTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$customerService = $this->getCustomerService();

		$billingAddress = new BillingAddress(new BillingAddressData());
		$deliveryAddress = new DeliveryAddress(new DeliveryAddressData());
		$userByEmail = null;
		$userData = new UserData();
		$userData->firstName = 'firstName';
		$userData->lastName = 'lastName';
		$userData->email = 'no-reply@netdevelo.cz';
		$userData->password = 'pa55w0rd';

		$user = $customerService->create(
			$userData,
			$billingAddress,
			$deliveryAddress,
			$userByEmail
		);

		$this->assertInstanceOf(User::class, $user);
	}

	public function testCreateNotDuplicateEmail() {
		$customerService = $this->getCustomerService();

		$billingAddress1 = new BillingAddress(new BillingAddressData());
		$deliveryAddress1 = new DeliveryAddress(new DeliveryAddressData());
		$userByEmail = null;
		$userData1 = new UserData();
		$userData1->firstName = 'firstName1';
		$userData1->lastName = 'lastName1';
		$userData1->email = 'no-reply@netdevelo.cz';
		$userData1->password = 'pa55w0rd';

		$user1 = $customerService->create(
			$userData1,
			$billingAddress1,
			$deliveryAddress1,
			$userByEmail
		);
		$this->assertInstanceOf(User::class, $user1);

		$billingAddress2 = new BillingAddress(new BillingAddressData());
		$deliveryAddress2 = new DeliveryAddress(new DeliveryAddressData());
		$userData2 = new UserData();
		$userData2->firstName = 'firstName2';
		$userData2->lastName = 'lastName2';
		$userData2->email = 'no-reply2@netdevelo.cz';
		$userData2->password = 'pa55w0rd';

		$user2 = $customerService->create(
			$userData2,
			$billingAddress2,
			$deliveryAddress2,
			$user1
		);
		$this->assertInstanceOf(User::class, $user2);
	}

	public function testCreateDuplicateEmail() {
		$customerService = $this->getCustomerService();

		$billingAddress1 = new BillingAddress(new BillingAddressData());
		$deliveryAddress1 = new DeliveryAddress(new DeliveryAddressData());
		$userByEmail = null;
		$userData1 = new UserData();
		$userData1->firstName = 'firstName1';
		$userData1->lastName = 'lastName1';
		$userData1->email = 'no-reply@netdevelo.cz';
		$userData1->password = 'pa55w0rd';

		$user1 = $customerService->create(
			$userData1,
			$billingAddress1,
			$deliveryAddress1,
			$userByEmail
		);

		$billingAddress2 = new BillingAddress(new BillingAddressData());
		$deliveryAddress2 = new DeliveryAddress(new DeliveryAddressData());
		$userData2 = new UserData();
		$userData2->firstName = 'firstName2';
		$userData2->lastName = 'lastName2';
		$userData2->email = 'no-reply@netdevelo.cz';
		$userData2->password = 'pa55w0rd';

		$this->setExpectedException(\SS6\ShopBundle\Model\Customer\Exception\DuplicateEmailException::class);
		$customerService->create(
			$userData2,
			$billingAddress2,
			$deliveryAddress2,
			$user1
		);
	}

	public function testCreateDuplicateEmailCaseInsentitive() {
		$customerService = $this->getCustomerService();

		$billingAddress1 = new BillingAddress(new BillingAddressData());
		$deliveryAddress1 = new DeliveryAddress(new DeliveryAddressData());
		$userByEmail = null;
		$userData1 = new UserData();
		$userData1->firstName = 'firstName1';
		$userData1->lastName = 'lastName1';
		$userData1->email = 'no-reply@netdevelo.cz';
		$userData1->password = 'pa55w0rd';

		$user1 = $customerService->create(
			$userData1,
			$billingAddress1,
			$deliveryAddress1,
			$userByEmail
		);

		$billingAddress2 = new BillingAddress(new BillingAddressData());
		$deliveryAddress2 = new DeliveryAddress(new DeliveryAddressData());
		$userData2 = new UserData();
		$userData2->firstName = 'firstName2';
		$userData2->lastName = 'lastName2';
		$userData2->email = 'NO-reply@netdevelo.cz';
		$userData2->password = 'pa55w0rd';

		$this->setExpectedException(\SS6\ShopBundle\Model\Customer\Exception\DuplicateEmailException::class);
		$customerService->create(
			$userData2,
			$billingAddress2,
			$deliveryAddress2,
			$user1
		);
	}

	/**
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function testGetAmendedCustomerDataByOrderWithoutChanges() {
		$customerService = $this->getCustomerService();

		$userData = new UserData();
		$userData->firstName = 'firstName';
		$userData->lastName = 'lastName';
		$billingAddressData = new BillingAddressData(
			'street',
			'city',
			'postcode',
			true,
			'companyName',
			'companyNumber',
			'companyTaxNumber',
			'telephone'
		);
		$deliveryAddressData = new DeliveryAddressData(
			true,
			'deliveryStreet',
			'deliveryCity',
			'deliveryPostcode',
			'deliveryCompanyName',
			'deliveryContactPerson',
			'deliveryTelephone'
		);

		$billingAddress = new BillingAddress($billingAddressData);
		$deliveryAddress = new DeliveryAddress($deliveryAddressData);
		$user = new User($userData, $billingAddress, $deliveryAddress);

		$transport = new Transport(new TransportData(['cs' => 'transportName']));
		$payment = new Payment(new PaymentData(['cs' => 'paymentName']));
		$orderStatus = new OrderStatus(new OrderStatusData(['en' => 'orderStatusName']), OrderStatus::TYPE_NEW);
		$orderData = new OrderData();
		$orderData->transport = $transport;
		$orderData->payment = $payment;
		$orderData->status = $orderStatus;
		$orderData->firstName = 'orderFirstName';
		$orderData->lastName = 'orderLastName';
		$orderData->email = 'order@email.com';
		$orderData->telephone = 'orderTelephone';
		$orderData->street = 'orderStreet';
		$orderData->city = 'orderCity';
		$orderData->postcode = 'orderPostcode';
		$orderData->deliveryAddressSameAsBillingAddress = false;
		$orderData->deliveryContactPerson = 'orderDeliveryContactPerson';
		$orderData->deliveryCompanyName = 'orderDeliveryCompanyName';
		$orderData->deliveryTelephone = 'orderDeliveryTelephone';
		$orderData->deliveryStreet = 'orderDeliveryStreet';
		$orderData->deliveryCity = 'orderDeliveryCity';
		$orderData->deliveryPostcode = 'orderDeliveryPostcode';
		$order = new Order(
			$orderData,
			'123456',
			'7ebafe9fe'
		);
		$order->setCompanyInfo(
			'companyName',
			'companyNumber',
			'companyTaxNumber'
		);

		$customerData = $customerService->getAmendedCustomerDataByOrder($user, $order);

		$this->assertEquals($userData, $customerData->userData);
		$this->assertEquals($billingAddressData, $customerData->billingAddressData);
		$this->assertEquals($deliveryAddressData, $customerData->deliveryAddressData);
	}

	/**
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function testGetAmendedCustomerDataByOrder() {
		$customerService = $this->getCustomerService();

		$userData = new UserData();
		$userData->firstName = 'firstName';
		$userData->lastName = 'lastName';
		$billingAddressData = new BillingAddressData();

		$billingAddress = new BillingAddress($billingAddressData);
		$user = new User($userData, $billingAddress, null);

		$transport = new Transport(new TransportData(['cs' => 'transportName']));
		$payment = new Payment(new PaymentData(['cs' => 'paymentName']));
		$orderStatus = new OrderStatus(new OrderStatusData(['en' => 'orderStatusName']), OrderStatus::TYPE_NEW);
		$orderData = new OrderData();
		$orderData->transport = $transport;
		$orderData->payment = $payment;
		$orderData->status = $orderStatus;
		$orderData->firstName = 'orderFirstName';
		$orderData->lastName = 'orderLastName';
		$orderData->email = 'order@email.com';
		$orderData->telephone = 'orderTelephone';
		$orderData->street = 'orderStreet';
		$orderData->city = 'orderCity';
		$orderData->postcode = 'orderPostcode';
		$orderData->deliveryAddressSameAsBillingAddress = false;
		$orderData->deliveryContactPerson = 'orderDeliveryContactPerson';
		$orderData->deliveryCompanyName = 'orderDeliveryCompanyName';
		$orderData->deliveryTelephone = 'orderDeliveryTelephone';
		$orderData->deliveryStreet = 'orderDeliveryStreet';
		$orderData->deliveryCity = 'orderDeliveryCity';
		$orderData->deliveryPostcode = 'orderDeliveryPostcode';
		$order = new Order(
			$orderData,
			'123456',
			'7eba123456fe9fe'
		);
		$order->setCompanyInfo(
			'companyName',
			'companyNumber',
			'companyTaxNumber'
		);

		$deliveryAddressData = new DeliveryAddressData(
			true,
			$order->getDeliveryStreet(),
			$order->getDeliveryCity(),
			$order->getDeliveryPostcode(),
			$order->getDeliveryCompanyName(),
			$order->getDeliveryContactPerson(),
			$order->getDeliveryTelephone()
		);

		$customerData = $customerService->getAmendedCustomerDataByOrder($user, $order);

		$this->assertEquals($userData, $customerData->userData);
		$this->assertEquals($deliveryAddressData, $customerData->deliveryAddressData);
		$this->assertTrue($customerData->billingAddressData->companyCustomer);
		$this->assertSame($order->getCompanyName(), $customerData->billingAddressData->companyName);
		$this->assertSame($order->getCompanyNumber(), $customerData->billingAddressData->companyNumber);
		$this->assertSame($order->getCompanyTaxNumber(), $customerData->billingAddressData->companyTaxNumber);
		$this->assertSame($order->getStreet(), $customerData->billingAddressData->street);
		$this->assertSame($order->getCity(), $customerData->billingAddressData->city);
		$this->assertSame($order->getPostcode(), $customerData->billingAddressData->postcode);
		$this->assertSame($order->getTelephone(), $customerData->billingAddressData->telephone);
	}

	/**
	 * @return \SS6\ShopBundle\Model\Customer\CustomerService
	 */
	private function getCustomerService() {
		$customerPasswordServiceMock = $this->getMock(CustomerPasswordService::class, [], [], '', false);

		return new CustomerService($customerPasswordServiceMock);
	}

}
