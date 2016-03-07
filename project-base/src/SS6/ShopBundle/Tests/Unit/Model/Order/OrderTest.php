<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Order;

use DateTime;
use DateTimeInterface;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Order\Item\OrderPayment;
use SS6\ShopBundle\Model\Order\Item\OrderProduct;
use SS6\ShopBundle\Model\Order\Order;
use SS6\ShopBundle\Model\Order\OrderData;
use SS6\ShopBundle\Model\Payment\Payment;
use SS6\ShopBundle\Model\Payment\PaymentData;
use SS6\ShopBundle\Model\Pricing\Price;

class OrderTest extends PHPUnit_Framework_TestCase {

	public function testGetProductItems() {
		$payment = new Payment(new PaymentData());
		$orderData = new OrderData();
		$paymentPrice = new Price(0, 0);

		$order = new Order($orderData, 'orderNumber', 'urlHash', null);
		$orderProduct = new OrderProduct($order, 'productName', $paymentPrice, 0, 1, null, null, null);
		$orderPayment = new OrderPayment($order, 'paymentName', $paymentPrice, 0, 1, $payment);
		$order->addItem($orderProduct);
		$order->addItem($orderPayment);

		$productItems = $order->getProductItems();

		$this->assertCount(1, $productItems);
		$this->assertContainsOnlyInstancesOf(OrderProduct::class, $productItems);
	}

	public function testGetProductItemsCount() {
		$payment = new Payment(new PaymentData());
		$paymentItemPrice = new Price(0, 0);
		$orderData = new OrderData();

		$order = new Order($orderData, 'orderNumber', 'urlHash', null);
		$productItem = new OrderProduct($order, 'productName', $paymentItemPrice, 0, 1, null, null);
		$paymentItem = new OrderPayment($order, 'paymentName', $paymentItemPrice, 0, 1, $payment);
		$order->addItem($productItem);
		$order->addItem($paymentItem);

		$this->assertSame(1, $order->getProductItemsCount());
	}

	public function testOrderWithDeliveryAddressSameAsBillingAddress() {
		$orderData = new OrderData();

		$orderData->companyName = 'companyName';
		$orderData->telephone = 'telephone';
		$orderData->firstName = 'firstName';
		$orderData->lastName = 'lastName';
		$orderData->street = 'street';
		$orderData->city = 'city';
		$orderData->postcode = 'postcode';
		$orderData->deliveryAddressSameAsBillingAddress = true;

		$order = new Order($orderData, 'orderNumber', 'urlHash', null);

		$this->assertSame('companyName', $order->getDeliveryCompanyName());
		$this->assertSame('telephone', $order->getDeliveryTelephone());
		$this->assertSame('firstName lastName', $order->getDeliveryContactPerson());
		$this->assertSame('street', $order->getDeliveryStreet());
		$this->assertSame('city', $order->getDeliveryCity());
		$this->assertSame('postcode', $order->getDeliveryPostcode());
	}

	public function testOrderWithoutDeliveryAddressSameAsBillingAddress() {
		$orderData = new OrderData();

		$orderData->companyName = 'companyName';
		$orderData->telephone = 'telephone';
		$orderData->firstName = 'firstName';
		$orderData->lastName = 'lastName';
		$orderData->street = 'street';
		$orderData->city = 'city';
		$orderData->postcode = 'postCode';
		$orderData->deliveryAddressSameAsBillingAddress = false;
		$orderData->deliveryCompanyName = 'deliveryCompanyName';
		$orderData->deliveryTelephone = 'deliveryTelephone';
		$orderData->deliveryContactPerson = 'deliveryContactPerson';
		$orderData->deliveryStreet = 'deliveryStreet';
		$orderData->deliveryCity = 'deliveryCity';
		$orderData->deliveryPostcode = 'deliveryPostcode';

		$order = new Order($orderData, 'orderNumber', 'urlHash', null);

		$this->assertSame('deliveryCompanyName', $order->getDeliveryCompanyName());
		$this->assertSame('deliveryTelephone', $order->getDeliveryTelephone());
		$this->assertSame('deliveryContactPerson', $order->getDeliveryContactPerson());
		$this->assertSame('deliveryStreet', $order->getDeliveryStreet());
		$this->assertSame('deliveryCity', $order->getDeliveryCity());
		$this->assertSame('deliveryPostcode', $order->getDeliveryPostCode());
	}

	public function testOrderCreatedWithEmptyCreatedAtIsCreatedNow() {
		$orderData = new OrderData();
		$user = null;

		$orderData->createdAt = null;
		$order = new Order($orderData, 'orderNumber', 'urlHash', $user);

		$this->assertDateTimeIsCloseTo(new DateTime(), $order->getCreatedAt(), 5);
	}

	public function testOrderCanBeCreatedWithSpecificCreatedAt() {
		$orderData = new OrderData();
		$user = null;

		$createAt = new DateTime('2000-01-01 01:00:00');
		$orderData->createdAt = $createAt;
		$order = new Order($orderData, 'orderNumber', 'urlHash', $user);

		$this->assertEquals($createAt, $order->getCreatedAt());
	}

	/**
	 * @param \DateTimeInterface $expected
	 * @param \DateTimeInterface $actual
	 * @param int $deltaInSeconds
	 */
	private function assertDateTimeIsCloseTo(DateTimeInterface $expected, DateTimeInterface $actual, $deltaInSeconds) {
		$diffInSeconds = $expected->getTimestamp() - $actual->getTimestamp();

		if (abs($diffInSeconds) > $deltaInSeconds) {
			$message = sprintf(
				'Failed asserting that %s is close to %s (delta: %d seconds)',
				$expected->format(DateTime::ISO8601),
				$actual->format(DateTime::ISO8601),
				$deltaInSeconds
			);
			$this->fail($message);
		}
	}

}
