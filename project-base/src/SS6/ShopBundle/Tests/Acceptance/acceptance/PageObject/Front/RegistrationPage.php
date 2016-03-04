<?php

namespace SS6\ShopBundle\Tests\Acceptance\acceptance\PageObject\Front;

use SS6\ShopBundle\Tests\Acceptance\acceptance\PageObject\AbstractPage;

class RegistrationPage extends AbstractPage {

	/**
	 * @param string $firstName
	 * @param string $lastName
	 * @param string $email
	 * @param string $firstPassword
	 * @param string $secondPassword
	 */
	public function register($firstName, $lastName, $email, $firstPassword, $secondPassword) {
		$this->tester->fillFieldByName('registration_form[firstName]', $firstName);
		$this->tester->fillFieldByName('registration_form[lastName]', $lastName);
		$this->tester->fillFieldByName('registration_form[email]', $email);
		$this->tester->fillFieldByName('registration_form[password][first]', $firstPassword);
		$this->tester->fillFieldByName('registration_form[password][second]', $secondPassword);
		$this->tester->wait(5);
		$this->tester->clickByName('registration_form[save]');
	}

}
