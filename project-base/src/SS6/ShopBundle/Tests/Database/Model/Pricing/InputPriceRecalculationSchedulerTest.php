<?php

namespace SS6\ShopBundle\Tests\Database\Model\Pricing;

use SS6\ShopBundle\Component\Setting\Setting;
use SS6\ShopBundle\Component\Setting\SettingValue;
use SS6\ShopBundle\DataFixtures\Base\CurrencyDataFixture;
use SS6\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use SS6\ShopBundle\Model\Payment\PaymentEditData;
use SS6\ShopBundle\Model\Payment\PaymentEditFacade;
use SS6\ShopBundle\Model\Pricing\InputPriceRecalculationScheduler;
use SS6\ShopBundle\Model\Pricing\InputPriceRecalculator;
use SS6\ShopBundle\Model\Pricing\PricingSetting;
use SS6\ShopBundle\Model\Pricing\Vat\Vat;
use SS6\ShopBundle\Model\Pricing\Vat\VatData;
use SS6\ShopBundle\Model\Product\Availability\Availability;
use SS6\ShopBundle\Model\Product\Availability\AvailabilityData;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductEditDataFactory;
use SS6\ShopBundle\Model\Product\ProductEditFacade;
use SS6\ShopBundle\Model\Transport\TransportEditData;
use SS6\ShopBundle\Model\Transport\TransportEditFacade;
use SS6\ShopBundle\Tests\Test\DatabaseTestCase;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class InputPriceRecalculationSchedulerTest extends DatabaseTestCase {

	public function testOnKernelResponseNoAction() {
		$setting = $this->getContainer()->get(Setting::class);
		/* @var $setting \SS6\ShopBundle\Component\Setting\Setting */

		$inputPriceRecalculatorMock = $this->getMockBuilder(InputPriceRecalculator::class)
			->setMethods(['__construct', 'recalculateToInputPricesWithoutVat', 'recalculateToInputPricesWithVat'])
			->disableOriginalConstructor()
			->getMock();
		$inputPriceRecalculatorMock->expects($this->never())->method('recalculateToInputPricesWithoutVat');
		$inputPriceRecalculatorMock->expects($this->never())->method('recalculateToInputPricesWithVat');

		$filterResponseEventMock = $this->getMockBuilder(FilterResponseEvent::class)
			->disableOriginalConstructor()
			->setMethods(['isMasterRequest'])
			->getMock();
		$filterResponseEventMock->expects($this->any())->method('isMasterRequest')
			->willReturn(true);

		$inputPriceRecalculationScheduler = new InputPriceRecalculationScheduler($inputPriceRecalculatorMock, $setting);

		$inputPriceRecalculationScheduler->onKernelResponse($filterResponseEventMock);
	}

	public function inputPricesTestDataProvider() {
		return [
			['inputPriceWithoutVat' => '100', 'inputPriceWithVat' => '121', 'vatPercent' => '21'],
			['inputPriceWithoutVat' => '17261.983471', 'inputPriceWithVat' => '20887', 'vatPercent' => '21'],
		];
	}

	/**
	 * @param string $inputPrice
	 * @param string $vatPercent
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	private function createProductWithInputPriceAndVatPercentAndAutoCalculationPriceType(
		$inputPrice,
		$vatPercent
	) {
		$em = $this->getEntityManager();
		$productEditDataFactory = $this->getContainer()->get(ProductEditDataFactory::class);
		/* @var $productEditDataFactory \SS6\ShopBundle\Model\Product\ProductEditDataFactory */
		$productEditFacade = $this->getContainer()->get(ProductEditFacade::class);
		/* @var $productEditFacade \SS6\ShopBundle\Model\Product\ProductEditFacade */

		$vat = new Vat(new VatData('vat', $vatPercent));
		$em->persist($vat);

		$templateProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
		$productEditData = $productEditDataFactory->createFromProduct($templateProduct);
		$productEditData->productData->priceCalculationType = Product::PRICE_CALCULATION_TYPE_AUTO;
		$productEditData->productData->price = $inputPrice;
		$productEditData->productData->vat = $vat;

		return $productEditFacade->create($productEditData);
	}

	/**
	 * @dataProvider inputPricesTestDataProvider
	 */
	public function testOnKernelResponseRecalculateInputPricesWithoutVat(
		$inputPriceWithoutVat,
		$inputPriceWithVat,
		$vatPercent
	) {
		$em = $this->getEntityManager();

		$setting = $this->getContainer()->get(Setting::class);
		/* @var $setting \SS6\ShopBundle\Component\Setting\Setting */
		$inputPriceRecalculationScheduler = $this->getContainer()->get(InputPriceRecalculationScheduler::class);
		/* @var $inputPriceRecalculationScheduler \SS6\ShopBundle\Model\Pricing\InputPriceRecalculationScheduler */
		$paymentEditFacade = $this->getContainer()->get(PaymentEditFacade::class);
		/* @var $paymentEditFacade \SS6\ShopBundle\Model\Payment\PaymentEditFacade */
		$transportEditFacade = $this->getContainer()->get(TransportEditFacade::class);
		/* @var $transportEditFacade \SS6\ShopBundle\Model\Transport\TransportEditFacade */

		$setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT, SettingValue::DOMAIN_ID_COMMON);

		$vat = new Vat(new VatData('vat', $vatPercent));
		$availability = new Availability(new AvailabilityData([], 0));
		$em->persist($vat);
		$em->persist($availability);

		$currency1 = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
		$currency2 = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);

		$product = $this->createProductWithInputPriceAndVatPercentAndAutoCalculationPriceType(
			$inputPriceWithVat,
			$vatPercent
		);

		$paymentEditData = new PaymentEditData();
		$paymentEditData->paymentData->name = ['cs' => 'name'];
		$paymentEditData->prices = [$currency1->getId() => $inputPriceWithVat, $currency2->getId() => $inputPriceWithVat];
		$paymentEditData->paymentData->vat = $vat;
		$payment = $paymentEditFacade->create($paymentEditData);
		/* @var $payment \SS6\ShopBundle\Model\Payment\Payment */

		$transportEditData = new \SS6\ShopBundle\Model\Transport\TransportEditData();
		$transportEditData->transportData->name = ['cs' => 'name'];
		$transportEditData->transportData->description = ['cs' => 'desc'];
		$transportEditData->prices = [$currency1->getId() => $inputPriceWithVat, $currency2->getId() => $inputPriceWithVat];
		$transportEditData->transportData->vat = $vat;
		$transport = $transportEditFacade->create($transportEditData);
		/* @var $transport \SS6\ShopBundle\Model\Transport\Transport */
		$em->flush();

		$filterResponseEventMock = $this->getMockBuilder(FilterResponseEvent::class)
			->disableOriginalConstructor()
			->setMethods(['isMasterRequest'])
			->getMock();
		$filterResponseEventMock->expects($this->any())->method('isMasterRequest')
			->willReturn(true);

		$inputPriceRecalculationScheduler->scheduleSetInputPricesWithoutVat();
		$inputPriceRecalculationScheduler->onKernelResponse($filterResponseEventMock);

		$em->refresh($product);
		$em->refresh($payment);
		$em->refresh($transport);

		$this->assertSame(round($inputPriceWithoutVat, 6), round($product->getPrice(), 6));
		$this->assertSame(round($inputPriceWithoutVat, 6), round($payment->getPrice($currency1)->getPrice(), 6));
		$this->assertSame(round($inputPriceWithoutVat, 6), round($transport->getPrice($currency1)->getPrice(), 6));
	}

	/**
	 * @dataProvider inputPricesTestDataProvider
	 */
	public function testOnKernelResponseRecalculateInputPricesWithVat(
		$inputPriceWithoutVat,
		$inputPriceWithVat,
		$vatPercent
	) {
		$em = $this->getEntityManager();

		$setting = $this->getContainer()->get(Setting::class);
		/* @var $setting \SS6\ShopBundle\Component\Setting\Setting */
		$inputPriceRecalculationScheduler = $this->getContainer()->get(InputPriceRecalculationScheduler::class);
		/* @var $inputPriceRecalculationScheduler \SS6\ShopBundle\Model\Pricing\InputPriceRecalculationScheduler */
		$paymentEditFacade = $this->getContainer()->get(PaymentEditFacade::class);
		/* @var $paymentEditFacade \SS6\ShopBundle\Model\Payment\PaymentEditFacade */
		$transportEditFacade = $this->getContainer()->get(TransportEditFacade::class);
		/* @var $transportEditFacade \SS6\ShopBundle\Model\Transport\TransportEditFacade */

		$setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT, SettingValue::DOMAIN_ID_COMMON);

		$currency1 = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
		$currency2 = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);

		$vat = new Vat(new VatData('vat', $vatPercent));
		$availability = new Availability(new AvailabilityData([], 0));
		$em->persist($vat);
		$em->persist($availability);

		$product = $this->createProductWithInputPriceAndVatPercentAndAutoCalculationPriceType(
			$inputPriceWithoutVat,
			$vatPercent
		);

		$paymentEditData = new PaymentEditData();
		$paymentEditData->paymentData->name = ['cs' => 'name'];
		$paymentEditData->prices = [$currency1->getId() => $inputPriceWithoutVat, $currency2->getId() => $inputPriceWithoutVat];
		$paymentEditData->paymentData->vat = $vat;
		$payment = $paymentEditFacade->create($paymentEditData);
		/* @var $payment \SS6\ShopBundle\Model\Payment\Payment */

		$transportEditData = new TransportEditData();
		$transportEditData->transportData->name = ['cs' => 'name'];
		$transportEditData->prices = [$currency1->getId() => $inputPriceWithoutVat, $currency2->getId() => $inputPriceWithoutVat];
		$transportEditData->transportData->vat = $vat;
		$transport = $transportEditFacade->create($transportEditData);
		/* @var $transport \SS6\ShopBundle\Model\Transport\Transport */

		$em->flush();

		$filterResponseEventMock = $this->getMockBuilder(FilterResponseEvent::class)
			->disableOriginalConstructor()
			->setMethods(['isMasterRequest'])
			->getMock();
		$filterResponseEventMock->expects($this->any())->method('isMasterRequest')
			->willReturn(true);

		$inputPriceRecalculationScheduler->scheduleSetInputPricesWithVat();
		$inputPriceRecalculationScheduler->onKernelResponse($filterResponseEventMock);

		$em->refresh($product);
		$em->refresh($payment);
		$em->refresh($transport);

		$this->assertSame(round($inputPriceWithVat, 6), round($product->getPrice(), 6));
		$this->assertSame(round($inputPriceWithVat, 6), round($payment->getPrice($currency1)->getPrice(), 6));
		$this->assertSame(round($inputPriceWithVat, 6), round($transport->getPrice($currency1)->getPrice(), 6));
	}

}
