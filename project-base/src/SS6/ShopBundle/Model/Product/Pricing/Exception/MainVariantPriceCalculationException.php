<?php

namespace SS6\ShopBundle\Model\Product\Pricing\Exception;

use Exception;
use SS6\ShopBundle\Model\Product\Pricing\Exception\ProductPricingException;

class MainVariantPriceCalculationException extends Exception implements ProductPricingException {

	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message, Exception $previous = null) {
		parent::__construct($message, 0, $previous);
	}

}
