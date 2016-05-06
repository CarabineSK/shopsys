<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Product\Pricing;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Pricing\BasePriceCalculation;
use SS6\ShopBundle\Model\Pricing\Currency\Currency;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroup;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupData;
use SS6\ShopBundle\Model\Pricing\PriceCalculation;
use SS6\ShopBundle\Model\Pricing\PricingService;
use SS6\ShopBundle\Model\Pricing\PricingSetting;
use SS6\ShopBundle\Model\Pricing\Rounding;
use SS6\ShopBundle\Model\Pricing\Vat\Vat;
use SS6\ShopBundle\Model\Pricing\Vat\VatData;
use SS6\ShopBundle\Model\Product\Pricing\ProductManualInputPriceRepository;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculation;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductData;
use SS6\ShopBundle\Model\Product\ProductRepository;

class ProductPriceCalculationTest extends PHPUnit_Framework_TestCase {

	public function calculatePriceProvider() {
		return [
			[
				'inputPriceType' => PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
				'inputPrice' => '6999',
				'vatPercent' => '21',
				'pricingGroupCoefficient' => '1',
				'priceWithoutVat' => '6998.78',
				'priceWithVat' => '8469',
			],
			[
				'inputPriceType' => PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
				'inputPrice' => '6999.99',
				'vatPercent' => '21',
				'pricingGroupCoefficient' => '2',
				'priceWithoutVat' => '11569.6',
				'priceWithVat' => '14000',
			],
		];
	}

	/**
	 * @param int $inputPriceType
	 * @param \SS6\ShopBundle\Model\Product\Product[] $variants
	 * @return \SS6\ShopBundle\Model\Product\Pricing\ProductPriceCalculation
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	private function getProductPriceCalculationWithInputPriceTypeAndVariants($inputPriceType, $variants) {
		$pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
			->setMethods(['getInputPriceType', 'getRoundingType', 'getDomainDefaultCurrencyIdByDomainId'])
			->disableOriginalConstructor()
			->getMock();
		$pricingSettingMock
			->expects($this->any())->method('getInputPriceType')
				->will($this->returnValue($inputPriceType));
		$pricingSettingMock
			->expects($this->any())->method('getRoundingType')
				->will($this->returnValue(PricingSetting::ROUNDING_TYPE_INTEGER));
		$pricingSettingMock
			->expects($this->any())->method('getDomainDefaultCurrencyIdByDomainId')
				->will($this->returnValue(1));

		$productManualInputPriceRepositoryMock = $this->getMockBuilder(ProductManualInputPriceRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$currencyFacadeMock = $this->getMockBuilder(CurrencyFacade::class)
			->setMethods(['getById'])
			->disableOriginalConstructor()
			->getMock();

		$currencyMock = $this->getMockBuilder(Currency::class)
			->setMethods(['getReversedExchangeRate'])
			->disableOriginalConstructor()
			->getMock();

		$currencyMock
			->expects($this->any())->method('getReversedExchangeRate')
				->will($this->returnValue(1));

		$currencyFacadeMock
			->expects($this->any())->method('getById')
			->will($this->returnValue($currencyMock));

		$productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
			->setMethods(['getAllSellableVariantsByMainVariant'])
			->disableOriginalConstructor()
			->getMock();
		$productRepositoryMock
			->expects($this->any())->method('getAllSellableVariantsByMainVariant')
			->will($this->returnValue($variants));

		$pricingService = new PricingService();

		$rounding = new Rounding($pricingSettingMock);
		$priceCalculation = new PriceCalculation($rounding);
		$basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

		return new ProductPriceCalculation(
			$basePriceCalculation,
			$pricingSettingMock,
			$productManualInputPriceRepositoryMock,
			$currencyFacadeMock,
			$productRepositoryMock,
			$pricingService
		);
	}

	/**
	 * @param string $inputPrice
	 * @param string $vatPercent
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	private function getProductWithInputPriceAndVatPercentAndAutoCalculationPriceType(
		$inputPrice,
		$vatPercent
	) {
		$vat = new Vat(new VatData('vat', $vatPercent));

		$productData = new ProductData();
		$productData->name = ['cs' => 'Product 1'];
		$productData->price = $inputPrice;
		$productData->vat = $vat;

		return Product::create($productData);
	}

	/**
	 * @dataProvider calculatePriceProvider
	 */
	public function testCalculatePriceWithAutoCalculationPriceType(
		$inputPriceType,
		$inputPrice,
		$vatPercent,
		$pricingGroupCoefficient,
		$priceWithoutVat,
		$priceWithVat
	) {
		$productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
			$inputPriceType,
			[]
		);

		$product = $this->getProductWithInputPriceAndVatPercentAndAutoCalculationPriceType(
			$inputPrice,
			$vatPercent
		);

		$pricingGroup = new PricingGroup(new PricingGroupData('name', $pricingGroupCoefficient), 1);

		$productPrice = $productPriceCalculation->calculatePrice($product, $pricingGroup->getDomainId(), $pricingGroup);

		$this->assertSame(round($priceWithoutVat, 6), round($productPrice->getPriceWithoutVat(), 6));
		$this->assertSame(round($priceWithVat, 6), round($productPrice->getPriceWithVat(), 6));
	}

	public function calculatePriceMainVariantProvider() {
		$vat = new Vat(new VatData('vat', 10));
		$productData1 = new ProductData();
		$productData1->name = ['cs' => 'Product 1'];
		$productData1->price = '100';
		$productData1->vat = $vat;

		$productData2 = new ProductData();
		$productData2->name = ['cs' => 'Product 2'];
		$productData2->price = '200';
		$productData2->vat = $vat;

		return [
			[
				'variants' => [
					Product::create($productData1),
					Product::create($productData2),
				],
				'expectedPriceWithVat' => 100,
				'expectedFrom' => true,
			],
			[
				'variants' => [
					Product::create($productData2),
					Product::create($productData2),
				],
				'expectedPriceWithVat' => 200,
				'expectedFrom' => false,
			],
		];
	}

	/**
	 * @dataProvider calculatePriceMainVariantProvider
	 */
	public function testCalculatePriceOfMainVariantWithVariantsAndAutoCalculationPriceType(
		$variants,
		$expectedPriceWithVat,
		$expectedFrom
	) {
		$productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
			PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
			$variants
		);

		$pricingGroup = new PricingGroup(new PricingGroupData('name', 1), 1);

		$product = Product::createMainVariant(new ProductData(), $variants);

		$productPrice = $productPriceCalculation->calculatePrice($product, $pricingGroup->getDomainId(), $pricingGroup);
		/* @var $productPrice \SS6\ShopBundle\Model\Product\Pricing\ProductPrice */

		$this->assertSame(round($expectedPriceWithVat, 6), round($productPrice->getPriceWithVat(), 6));
		$this->assertSame($expectedFrom, $productPrice->isPriceFrom());
	}

	public function testCalculatePriceMainVariantWithoutSellableVariants() {
		$pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
			->disableOriginalConstructor()
			->getMock();

		$productManualInputPriceRepositoryMock = $this->getMockBuilder(ProductManualInputPriceRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$currencyFacadeMock = $this->getMockBuilder(CurrencyFacade::class)
			->disableOriginalConstructor()
			->getMock();

		$productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
			->setMethods(['getAllSellableVariantsByMainVariant'])
			->disableOriginalConstructor()
			->getMock();
		$productRepositoryMock
			->expects($this->once())->method('getAllSellableVariantsByMainVariant')
				->will($this->returnValue([]));

		$pricingServiceMock = $this->getMockBuilder(PricingService::class)
			->disableOriginalConstructor()
			->getMock();

		$rounding = new Rounding($pricingSettingMock);
		$priceCalculation = new PriceCalculation($rounding);
		$basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

		$productPriceCalculation = new ProductPriceCalculation(
			$basePriceCalculation,
			$pricingSettingMock,
			$productManualInputPriceRepositoryMock,
			$currencyFacadeMock,
			$productRepositoryMock,
			$pricingServiceMock
		);

		$pricingGroup = new PricingGroup(new PricingGroupData('name', 1), 1);

		$variant = Product::create(new ProductData());
		$product = Product::createMainVariant(new ProductData(), [$variant]);

		$this->setExpectedException(\SS6\ShopBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException::class);

		$productPriceCalculation->calculatePrice($product, $pricingGroup->getDomainId(), $pricingGroup);
	}

}
