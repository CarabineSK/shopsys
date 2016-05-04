<?php

namespace SS6\ShopBundle\Tests\Acceptance\acceptance\PageObject\Front;

use SS6\ShopBundle\Tests\Acceptance\acceptance\PageObject\AbstractPage;

class FloatingWindowPage extends AbstractPage {

	public function closeFloatingWindow() {
		$this->tester->clickByCss('.js-window-button-close');
	}

}
