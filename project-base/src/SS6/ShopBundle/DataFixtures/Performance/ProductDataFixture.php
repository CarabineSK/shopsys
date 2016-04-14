<?php

namespace SS6\ShopBundle\DataFixtures\Performance;

use Doctrine\ORM\EntityManager;
use Faker\Generator as Faker;
use SS6\ShopBundle\Component\DataFixture\PersistentReferenceFacade;
use SS6\ShopBundle\Component\DataFixture\ProductDataFixtureReferenceInjector;
use SS6\ShopBundle\Component\Doctrine\SqlLoggerFacade;
use SS6\ShopBundle\DataFixtures\Demo\ProductDataFixtureLoader;
use SS6\ShopBundle\DataFixtures\Performance\CategoryDataFixture;
use SS6\ShopBundle\Model\Category\Category;
use SS6\ShopBundle\Model\Category\CategoryRepository;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Product\ProductEditData;
use SS6\ShopBundle\Model\Product\ProductEditFacade;
use SS6\ShopBundle\Model\Product\ProductVariantFacade;

class ProductDataFixture {

	const PRODUCTS = 40000;
	const BATCH_SIZE = 1000;

	const FIRST_PERFORMANCE_PRODUCT = 'first_performance_product';

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductEditFacade
	 */
	private $productEditFacade;

	/**
	 * @var \SS6\ShopBundle\DataFixtures\Demo\ProductDataFixtureLoader
	 */
	private $productDataFixtureLoader;

	/**
	 * @var \SS6\ShopBundle\Component\Doctrine\SqlLoggerFacade
	 */
	private $sqlLoggerFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductVariantFacade
	 */
	private $productVariantFacade;

	/**
	 * @var \SS6\ShopBundle\Component\DataFixture\ProductDataFixtureReferenceInjector
	 */
	private $productDataReferenceInjector;

	/**
	 * @var \SS6\ShopBundle\Component\DataFixture\PersistentReferenceFacade
	 */
	private $persistentReferenceFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Category\CategoryRepository
	 */
	private $categoryRepository;

	/**
	 * @var int
	 */
	private $countImported;

	/**
	 * @var int
	 */
	private $demoDataIterationCounter;

	/**
	 * @var float
	 */
	private $batchStartMicrotime;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Product[catnum]
	 */
	private $productsByCatnum;

	/**
	 * @var \Faker\Generator
	 */
	private $faker;

	public function __construct(
		EntityManager $em,
		ProductEditFacade $productEditFacade,
		ProductDataFixtureLoader $productDataFixtureLoader,
		SqlLoggerFacade $sqlLoggerFacade,
		ProductVariantFacade $productVariantFacade,
		ProductDataFixtureReferenceInjector $productDataReferenceInjector,
		PersistentReferenceFacade $persistentReferenceFacade,
		CategoryRepository $categoryRepository,
		Faker $faker
	) {
		$this->em = $em;
		$this->productEditFacade = $productEditFacade;
		$this->productDataFixtureLoader = $productDataFixtureLoader;
		$this->sqlLoggerFacade = $sqlLoggerFacade;
		$this->productVariantFacade = $productVariantFacade;
		$this->productDataReferenceInjector = $productDataReferenceInjector;
		$this->persistentReferenceFacade = $persistentReferenceFacade;
		$this->categoryRepository = $categoryRepository;
		$this->countImported = 0;
		$this->demoDataIterationCounter = 0;
		$this->faker = $faker;
	}

	public function load() {
		// Sql logging during mass data import makes memory leak
		$this->sqlLoggerFacade->temporarilyDisableLogging();

		$productsEditData = $this->cleanAndWarmUp($this->em);
		$variantCatnumsByMainVariantCatnum = $this->productDataFixtureLoader->getVariantCatnumsIndexedByMainVariantCatnum();

		while ($this->countImported < self::PRODUCTS) {
			$productEditData = next($productsEditData);
			if ($productEditData === false) {
				$this->createVariants($variantCatnumsByMainVariantCatnum);
				$productEditData = reset($productsEditData);
				$this->demoDataIterationCounter++;
			}
			$this->makeProductEditDataUnique($productEditData);
			$this->setRandomPerformanceCategoriesToProductEditData($productEditData);
			$product = $this->productEditFacade->create($productEditData);

			if ($this->countImported === 0) {
				$this->persistentReferenceFacade->persistReference(self::FIRST_PERFORMANCE_PRODUCT, $product);
			}

			if ($product->getCatnum() !== null) {
				$this->productsByCatnum[$product->getCatnum()] = $product;
			}

			$this->printProgress();
			if ($this->countImported % self::BATCH_SIZE === 0) {
				$currentKey = key($productsEditData);
				$productsEditData = $this->cleanAndWarmUp($this->em);
				$this->setArrayPointerByKey($productsEditData, $currentKey);
			}

			$this->countImported++;
		}
		$this->createVariants($variantCatnumsByMainVariantCatnum);
		$this->em->clear();
		$this->sqlLoggerFacade->reenableLogging();
	}

	private function printProgress() {
		$spentMicrotime = microtime(true) - $this->batchStartMicrotime;
		$batchNumber = ceil($this->countImported / self::BATCH_SIZE);
		$totalBatches = ceil(self::PRODUCTS / self::BATCH_SIZE);
		$batchImported = $this->countImported % self::BATCH_SIZE;
		$batchImported = $batchImported ?: self::BATCH_SIZE;
		echo sprintf(
			'Batch %2d / %2d - %3d%% - %4.1f s / %2.3f s' . "\r",
			$batchNumber,
			$totalBatches,
			100 * $this->countImported / self::PRODUCTS,
			$spentMicrotime,
			$spentMicrotime / $batchImported
		);
	}

	/**
	 * @param string[catnum][] $variantCatnumsByMainVariantCatnum
	 */
	private function createVariants(array $variantCatnumsByMainVariantCatnum) {
		$uniqueIndex = $this->getUniqueIndex();

		foreach ($variantCatnumsByMainVariantCatnum as $mainVariantCatnum => $variantsCatnums) {
			try {
				$mainProduct = $this->getProductByCatnum($mainVariantCatnum . $uniqueIndex);
				$variants = [];
				foreach ($variantsCatnums as $variantCatnum) {
					$variants[] = $this->getProductByCatnum($variantCatnum . $uniqueIndex);
				}
				$this->productVariantFacade->createVariant($mainProduct, $variants);
			} catch (\Doctrine\ORM\NoResultException $e) {
				continue;
			}
		}
	}

	/**
	 * @param string $catnum
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	private function getProductByCatnum($catnum) {
		if (!array_key_exists($catnum, $this->productsByCatnum)) {
			$query = $this->em->createQuery('SELECT p FROM ' . Product::class . ' p WHERE p.catnum = :catnum')
				->setParameter('catnum', $catnum);
			$this->productsByCatnum[$catnum] = $query->getSingleResult();
		}

		return $this->productsByCatnum[$catnum];
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 */
	private function makeProductEditDataUnique(ProductEditData $productEditData) {
		$matches = [];
		$uniqueIndex = $this->getUniqueIndex();

		if (preg_match('/^(.*) #\d+$/', $productEditData->productData->catnum, $matches)) {
			$productEditData->productData->catnum = $matches[1] . $uniqueIndex;
		} else {
			$productEditData->productData->catnum .= $uniqueIndex;
		}

		foreach ($productEditData->productData->name as $locale => $name) {
			if (preg_match('/^(.*) #\d+$/', $name, $matches)) {
				$productEditData->productData->name[$locale] = $matches[1] . $uniqueIndex;
			} else {
				$productEditData->productData->name[$locale] .= $uniqueIndex;
			}
		}
	}

	/**
	 * @return string
	 */
	private function getUniqueIndex() {
		return ' #' . $this->demoDataIterationCounter;
	}

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	private function clearResources(EntityManager $em) {
		$em->clear();
		gc_collect_cycles();
		echo "\nMemory usage: " . round(memory_get_usage() / 1024 / 1024, 1) . "MB\n";
	}

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @return \SS6\ShopBundle\Model\Product\ProductEditData[]
	 */
	private function cleanAndWarmUp(EntityManager $em) {
		$this->clearResources($em);
		$this->batchStartMicrotime = microtime(true);
		$this->productsByCatnum = [];

		$this->productDataReferenceInjector->loadReferences($this->productDataFixtureLoader, $this->persistentReferenceFacade);

		return $this->productDataFixtureLoader->getProductsEditData();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 */
	private function setRandomPerformanceCategoriesToProductEditData(ProductEditData $productEditData) {
		$this->cleanPerformanceCategoriesFromProductEditDataByDomainId($productEditData, 1);
		$this->cleanPerformanceCategoriesFromProductEditDataByDomainId($productEditData, 2);
		$this->addRandomPerformanceCategoriesToProductEditDataByDomainId($productEditData, 1);
		$this->addRandomPerformanceCategoriesToProductEditDataByDomainId($productEditData, 2);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 * @param int $domainId
	 */
	private function cleanPerformanceCategoriesFromProductEditDataByDomainId(ProductEditData $productEditData, $domainId) {
		foreach ($productEditData->productData->categoriesByDomainId[$domainId] as $key => $category) {
			if ($this->isPerformanceCategory($category)) {
				unset($productEditData->productData->categoriesByDomainId[$domainId][$key]);
			}
		}
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductEditData $productEditData
	 * @param int $domainId
	 */
	private function addRandomPerformanceCategoriesToProductEditDataByDomainId(ProductEditData $productEditData, $domainId) {
		$performanceCategoryIds = $this->getPerformanceCategoryIds();
		$randomPerformanceCategoryIds = $this->faker->randomElements(
			$performanceCategoryIds,
			$this->faker->numberBetween(1, 4)
		);
		$randomPerformanceCategories = $this->categoryRepository->getCategoriesByIds($randomPerformanceCategoryIds);

		foreach ($randomPerformanceCategories as $performanceCategory) {
			if (!in_array($performanceCategory, $productEditData->productData->categoriesByDomainId[$domainId], true)) {
				$productEditData->productData->categoriesByDomainId[$domainId][] = $performanceCategory;
			}
		}
	}

	/**
	 * @return int[]
	 */
	private function getPerformanceCategoryIds() {
		$allCategoryIds = $this->categoryRepository->getAllIds();
		$firstPerformanceCategory = $this->persistentReferenceFacade->getReference(
			CategoryDataFixture::FIRST_PERFORMANCE_CATEGORY
		);
		$firstPerformanceCategoryKey = array_search($firstPerformanceCategory->getId(), $allCategoryIds, true);

		return array_slice($allCategoryIds, $firstPerformanceCategoryKey);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @return bool
	 */
	private function isPerformanceCategory(Category $category) {
		$firstPerformanceCategory = $this->persistentReferenceFacade->getReference(
			CategoryDataFixture::FIRST_PERFORMANCE_CATEGORY
		);
		/* @var $firstPerformanceCategory \SS6\ShopBundle\Model\Category\Category */

		return $category->getId() >= $firstPerformanceCategory->getId();
	}

	/**
	 * @param array $array
	 * @param string|int $key
	 */
	private function setArrayPointerByKey(array &$array, $key) {
		reset($array);
		while (key($array) !== $key) {
			if (each($array) === false) {
				throw \SS6\ShopBundle\DataFixtures\Performance\Exception\UndefinedArrayKeyException($key);
			}
		}
	}
}
