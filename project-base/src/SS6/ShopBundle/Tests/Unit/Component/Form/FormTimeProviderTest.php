<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Form;

use DateTime;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Form\FormTimeProvider;
use SS6\ShopBundle\Form\TimedFormTypeExtension;
use Symfony\Component\HttpFoundation\Session\Session;

class FormTimeProviderTest extends PHPUnit_Framework_TestCase {

	public function testIsFormTimeValidProvider() {
		return [
			[9, '-10 second', true],
			[11, '-10 second', false],
		];
	}

	/**
	 * @dataProvider testIsFormTimeValidProvider
	 * @param int $minimumSeconds
	 * @param string $formCreatedAt
	 * @param bool $isValid
	 */
	public function testIsFormTimeValid($minimumSeconds, $formCreatedAt, $isValid) {
		$sessionMock = $this->getMockBuilder(Session::class)
			->disableOriginalConstructor()
			->setMethods(['get', 'has'])
			->getMock();
		$sessionMock->expects($this->atLeastOnce())->method('get')->will($this->returnValue(new DateTime($formCreatedAt)));
		$sessionMock->expects($this->atLeastOnce())->method('has')->will($this->returnValue(true));

		$formTimeProvider = new FormTimeProvider($sessionMock);

		$options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS] = $minimumSeconds;
		$this->assertSame($isValid, $formTimeProvider->isFormTimeValid('formName', $options));
	}
}
