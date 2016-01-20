<?php

namespace SS6\ShopBundle\Tests\Unit\Model\AdminNavigation;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\AdminNavigation\Menu;
use SS6\ShopBundle\Model\AdminNavigation\MenuItem;

class MenuTest extends PHPUnit_Framework_TestCase {

	public function testIsRouteMatchingDescendantOfSettings() {
		$itemChild = new MenuItem('label 1', MenuItem::TYPE_REGULAR, 'route 1', null, true, false, null, []);
		$itemParent = new MenuItem('label 2', MenuItem::TYPE_SETTINGS, 'route 2', null, true, false, null, [$itemChild]);
		$menu = new Menu([
			$itemParent,
		]);

		$this->assertTrue($menu->isRouteMatchingDescendantOfSettings('route 1', null));
	}

	public function testIsNotRouteMatchingDescendantOfSettings() {
		$itemChild = new MenuItem('label 1', MenuItem::TYPE_REGULAR, 'route 1', null, true, false, null, []);
		$itemParent = new MenuItem('label 2', MenuItem::TYPE_REGULAR, 'route 2', null, true, false, null, [$itemChild]);
		$itemSettings = new MenuItem('label 3', MenuItem::TYPE_SETTINGS, 'route 3', null, true, false, null, []);
		$menu = new Menu([
			$itemParent,
			$itemSettings,
		]);

		$this->assertFalse($menu->isRouteMatchingDescendantOfSettings('route 1', null));
	}

}
