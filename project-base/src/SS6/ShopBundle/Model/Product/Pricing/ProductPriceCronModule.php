<?php

namespace SS6\ShopBundle\Model\Product\Pricing;

use SS6\ShopBundle\Component\Cron\IteratedCronModuleInterface;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculator;
use Symfony\Bridge\Monolog\Logger;

class ProductPriceCronModule implements IteratedCronModuleInterface {

	/**
	 * @var \Symfony\Bridge\Monolog\Logger
	 */
	private $logger;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculator
	 */
	private $productPriceRecalculator;

	public function __construct(ProductPriceRecalculator $productPriceRecalculator) {
		$this->productPriceRecalculator = $productPriceRecalculator;
	}

	/**
	 * @inheritdoc
	 */
	public function setLogger(Logger $logger) {
		$this->logger = $logger;
	}

	public function sleep() {

	}

	public function wakeUp() {

	}

	/**
	 * @inheritdoc
	 */
	public function iterate() {
		if ($this->productPriceRecalculator->runScheduledRecalculationsBatch()) {
			$this->logger->debug('Batch is recalculated.');
			return true;
		} else {
			$this->logger->debug('All prices are recalculated.');
			return false;
		}
	}

}
