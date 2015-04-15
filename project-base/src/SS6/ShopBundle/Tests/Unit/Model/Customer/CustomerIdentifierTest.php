<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Customer;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Customer\CustomerIdentifier;
use SS6\ShopBundle\Model\Customer\Exception\EmptyCustomerIdentifierException;

class CustomerIdentifierTest extends PHPUnit_Framework_TestCase {

	public function testCreateEmpty() {
		$sessionId = '';
		$user = null;

		$this->setExpectedException(EmptyCustomerIdentifierException::class);
		$customerIdentifier = new CustomerIdentifier($sessionId, $user);
	}

}
