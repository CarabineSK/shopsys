<?php

namespace SS6\ShopBundle\Model\Product\Availability;

use SS6\ShopBundle\Component\Cron\IteratedCronModuleInterface;
use SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityRecalculator;
use Symfony\Bridge\Monolog\Logger;

class ProductAvailabilityCronModule implements IteratedCronModuleInterface {

	/**
	 * @var \Symfony\Bridge\Monolog\Logger
	 */
	private $logger;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityRecalculator
	 */
	private $productAvailabilityRecalculator;

	public function __construct(ProductAvailabilityRecalculator $productAvailabilityRecalculator) {
		$this->productAvailabilityRecalculator = $productAvailabilityRecalculator;
	}

	/**
	 * @inheritdoc
	 */
	public function initialize(Logger $logger) {
		$this->logger = $logger;
	}

	/**
	 * @inheritdoc
	 */
	public function iterate() {
		if ($this->productAvailabilityRecalculator->runScheduledRecalculationsBatch()) {
			$this->logger->debug('Batch is recalculated.');
			return true;
		} else {
			$this->logger->debug('All availabilities are recalculated.');
			return false;
		}
	}

}
