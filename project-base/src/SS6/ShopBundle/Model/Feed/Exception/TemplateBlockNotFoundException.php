<?php

namespace SS6\ShopBundle\Model\Feed\Exception;

use Exception;
use SS6\ShopBundle\Model\Feed\Exception\FeedException;

class TemplateBlockNotFoundException extends Exception implements FeedException {

	public function __construct($blockName, $templateName, Exception $previous = null) {
		$message = sprintf('Block "%s" does not exist in template "%s".', $blockName, $templateName);
		parent::__construct($message, 0, $previous);
	}

}
