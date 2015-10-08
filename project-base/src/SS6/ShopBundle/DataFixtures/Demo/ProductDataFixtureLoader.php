<?php

namespace SS6\ShopBundle\DataFixtures\Demo;

use DateTime;
use SS6\ShopBundle\Component\Csv\CsvReader;
use SS6\ShopBundle\Component\String\EncodingConverter;
use SS6\ShopBundle\Component\String\TransformString;
use SS6\ShopBundle\Model\Product\Parameter\ParameterData;
use SS6\ShopBundle\Model\Product\Parameter\ParameterFacade;
use SS6\ShopBundle\Model\Product\Parameter\ParameterValueData;
use SS6\ShopBundle\Model\Product\Parameter\ProductParameterValueData;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductData;
use SS6\ShopBundle\Model\Product\ProductEditData;

class ProductDataFixtureLoader {

	const COLUMN_NAME_CS = 0;
	const COLUMN_NAME_EN = 1;
	const COLUMN_CATNUM = 2;
	const COLUMN_PARTNO = 3;
	const COLUMN_EAN = 4;
	const COLUMN_DESCRIPTION_CS = 5;
	const COLUMN_DESCRIPTION_EN = 6;
	const COLUMN_PRICE = 7;
	const COLUMN_VAT = 8;
	const COLUMN_SELLING_FROM = 9;
	const COLUMN_SELLING_TO = 10;
	const COLUMN_STOCK_QUANTITY = 11;
	const COLUMN_UNIT = 12;
	const COLUMN_AVAILABILITY = 13;
	const COLUMN_PARAMETERS = 14;
	const COLUMN_CATEGORIES_1 = 15;
	const COLUMN_CATEGORIES_2 = 16;
	const COLUMN_FLAGS = 17;
	const COLUMN_SELLING_DENIED = 18;
	const COLUMN_BRAND = 19;
	const COLUMN_MAIN_VARIANT_CATNUM = 20;

	/**
	 * @var \SS6\ShopBundle\Component\Csv\CsvReader
	 */
	private $csvReader;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Parameter\ParameterFacade
	 */
	private $parameterFacade;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	private $vats;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Availability\Availability[]
	 */
	private $availabilities;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Parameter\Parameter[]
	 */
	private $parameters;

	/**
	 * @var \SS6\ShopBundle\Model\Category\Category[]
	 */
	private $categories;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Flag\Flag[]
	 */
	private $flags;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Brand\Brand[]
	 */
	private $brands;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Unit\Unit[]
	 */
	private $units;

	/**
	 * @param string $path
	 * @param \SS6\ShopBundle\Component\Csv\CsvReader $csvReader
	 * @param \SS6\ShopBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
	 */
	public function __construct($path, CsvReader $csvReader, ParameterFacade $parameterFacade) {
		$this->path = $path;
		$this->csvReader = $csvReader;
		$this->parameterFacade = $parameterFacade;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat[] $vats
	 * @param \SS6\ShopBundle\Model\Product\Availability\Availability[] $availabilities
	 * @param \SS6\ShopBundle\Model\Category\Category[] $categories
	 * @param \SS6\ShopBundle\Model\Product\Flag\Flag[] $flags
	 * @param \SS6\ShopBundle\Model\Product\Brand\Brand[] $brands
	 * @param \SS6\ShopBundle\Model\Product\Unit\Unit[] $units
	 */
	public function injectReferences(
		array $vats,
		array $availabilities,
		array $categories,
		array $flags,
		array $brands,
		array $units
	) {
		$this->vats = $vats;
		$this->availabilities = $availabilities;
		$this->categories = $categories;
		$this->flags = $flags;
		$this->brands = $brands;
		$this->parameters = [];
		$this->units = $units;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Product\ProductEditData[]
	 */
	public function getProductsEditData() {
		$rows = $this->csvReader->getRowsFromCsv($this->path);

		foreach ($rows as $rowId => $row) {
			if ($rowId === 0) {
				continue;
			}

			$row = array_map([TransformString::class, 'emptyToNull'], $row);
			$row = EncodingConverter::cp1250ToUtf8($row);
			$productsEditData[] = $this->getProductEditDataFromCsvRow($row);
		}

		return $productsEditData;
	}

	/**
	 * @return int[mainVariantRowId][]
	 */
	public function getVariantCatnumsIndexedByMainVariantCatnum() {
		$rows = $this->csvReader->getRowsFromCsv($this->path);

		$variantCatnumsByMainVariantCatnum = [];
		foreach ($rows as $rowId => $row) {
			if ($rowId === 0) {
				continue;
			}

			$row = array_map([TransformString::class, 'emptyToNull'], $row);
			$row = EncodingConverter::cp1250ToUtf8($row);

			if ($row[self::COLUMN_MAIN_VARIANT_CATNUM] !== null && $row[self::COLUMN_CATNUM] !== null) {
				$variantCatnumsByMainVariantCatnum[$row[self::COLUMN_MAIN_VARIANT_CATNUM]][] = $row[self::COLUMN_CATNUM];
			}
		}

		return $variantCatnumsByMainVariantCatnum;
	}

	/**
	 * @param array $row
	 * @return \SS6\ShopBundle\Model\Product\ProductEditData
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	private function getProductEditDataFromCsvRow(array $row) {
		$productEditData = new ProductEditData();
		$productEditData->productData = new ProductData();
		$productEditData->productData->name = ['cs' => $row[self::COLUMN_NAME_CS], 'en' => $row[self::COLUMN_NAME_EN]];
		$productEditData->productData->catnum = $row[self::COLUMN_CATNUM];
		$productEditData->productData->partno = $row[self::COLUMN_PARTNO];
		$productEditData->productData->ean = $row[self::COLUMN_EAN];
		$productEditData->descriptions = [1 => $row[self::COLUMN_DESCRIPTION_CS], 2 => $row[self::COLUMN_DESCRIPTION_EN]];
		$productEditData->productData->price = $row[self::COLUMN_PRICE];
		switch ($row[self::COLUMN_VAT]) {
			case 'high':
				$productEditData->productData->vat = $this->vats['high'];
				break;
			case 'low':
				$productEditData->productData->vat = $this->vats['low'];
				break;
			case 'zero':
				$productEditData->productData->vat = $this->vats['zero'];
				break;
			default:
				$productEditData->productData->vat = null;
		}
		if ($row[self::COLUMN_SELLING_FROM] !== null) {
			$productEditData->productData->sellingFrom = new DateTime($row[self::COLUMN_SELLING_FROM]);
		}
		if ($row[self::COLUMN_SELLING_TO] !== null) {
			$productEditData->productData->sellingTo = new DateTime($row[self::COLUMN_SELLING_TO]);
		}
		$productEditData->productData->usingStock = $row[self::COLUMN_STOCK_QUANTITY] !== null;
		$productEditData->productData->stockQuantity = $row[self::COLUMN_STOCK_QUANTITY];
		$productEditData->productData->unit = $this->units[$row[self::COLUMN_UNIT]];
		$productEditData->productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;
		switch ($row[self::COLUMN_AVAILABILITY]) {
			case 'in-stock':
				$productEditData->productData->availability = $this->availabilities['in-stock'];
				break;
			case 'out-of-stock':
				$productEditData->productData->availability = $this->availabilities['out-of-stock'];
				break;
			case 'on-request':
				$productEditData->productData->availability = $this->availabilities['on-request'];
				break;
		}
		$productEditData->parameters = $this->getProductParameterValuesDataFromString($row[self::COLUMN_PARAMETERS]);
		$productEditData->productData->categoriesByDomainId = [
			1 => $this->getValuesByKeyString($row[self::COLUMN_CATEGORIES_1], $this->categories),
			2 => $this->getValuesByKeyString($row[self::COLUMN_CATEGORIES_2], $this->categories),
		];
		$productEditData->productData->flags = $this->getValuesByKeyString($row[self::COLUMN_FLAGS], $this->flags);
		$productEditData->productData->sellingDenied = $row[self::COLUMN_SELLING_DENIED];

		if ($row[self::COLUMN_BRAND] !== null) {
			$productEditData->productData->brand = $this->brands[$row[self::COLUMN_BRAND]];
		}

		return $productEditData;
	}

	/**
	 * @param string $string
	 * @return \SS6\ShopBundle\Model\Product\Parameter\ProductParameterValueData[]
	 */
	private function getProductParameterValuesDataFromString($string) {
		$rows = explode(';', $string);

		$productParameterValuesData = [];
		foreach ($rows as $row) {
			$rowData = explode('=', $row);
			if (count($rowData) !== 2) {
				continue;
			}

			list($serializedParameterNames, $serializedValueTexts) = $rowData;
			$serializedParameterNames = trim($serializedParameterNames, '[]');
			$serializedValueTexts = trim($serializedValueTexts, '[]');

			if (!isset($this->parameters[$serializedParameterNames])) {
				$parameterNames = $this->unserializeLocalizedValues($serializedParameterNames);
				$parameter = $this->parameterFacade->findParameterByNames($parameterNames);
				if ($parameter === null) {
					$parameter = $this->parameterFacade->create(new ParameterData($parameterNames));
				}
				$this->parameters[$serializedParameterNames] = $parameter;
			}

			$valueTexts = $this->unserializeLocalizedValues($serializedValueTexts);
			foreach ($valueTexts as $locale => $valueText) {
				$productParameterValueData = new ProductParameterValueData();
				$productParameterValueData->parameterValueData = new ParameterValueData($valueText, $locale);
				$productParameterValueData->parameter = $this->parameters[$serializedParameterNames];
				$productParameterValuesData[] = $productParameterValueData;
			}
		}

		return $productParameterValuesData;
	}

	/**
	 * @param string $string
	 * @return array
	 */
	private function unserializeLocalizedValues($string) {
		$array = [];
		$items = explode(',', $string);
		foreach ($items as $item) {
			list($locale, $value) = explode(':', $item);
			$array[$locale] = $value;
		}
		return $array;
	}

	/**
	 * @param string $keyString
	 * @param array $valuesByKey
	 * @return array $values
	 */
	private function getValuesByKeyString($keyString, array $valuesByKey) {
		$values = [];
		if (!empty($keyString)) {
			$keys = explode(';', $keyString);
			foreach ($keys as $key) {
				$values[] = $valuesByKey[$key];
			}
		}

		return $values;
	}
}
