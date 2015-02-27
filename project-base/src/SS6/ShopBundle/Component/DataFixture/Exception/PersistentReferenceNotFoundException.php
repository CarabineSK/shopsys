<?php

namespace SS6\ShopBundle\Component\DataFixture\Exception;

use Exception;
use SS6\ShopBundle\Component\DataFixture\Exception\DataFixtureException;

class PersistentReferenceNotFoundException extends Exception implements DataFixtureException {

	/**
	 * @param string $referenceName
	 * @param \Exception $previous
	 */
	public function __construct($referenceName, Exception $previous = null) {
		parent::__construct('Data fixture reference "' . $referenceName . '" not found', 0, $previous);
	}

}
