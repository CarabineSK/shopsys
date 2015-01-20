<?php

namespace SS6\ShopBundle\Model\AdminNavigation;

class Menu {

	/**
	 * @var \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]
	 */
	private $items;

	/**
	 * @var \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]
	 */
	private $regularItems;

	/**
	 * @var \SS6\ShopBundle\Model\AdminNavigation\MenuItem
	 */
	private $settingsItem;

	/**
	 * @param \SS6\ShopBundle\Model\AdminNavigation\MenuItem[] $items
	 */
	public function __construct(array $items) {
		$this->items = $items;

		$this->regularItems = [];

		foreach ($items as $item) {
			if ($item->getType() === MenuItem::TYPE_REGULAR) {
				$this->regularItems[] = $item;
			} elseif ($item->getType() === MenuItem::TYPE_SETTINGS) {
				$this->settingsItem = $item;
			}
		}

		if (!isset($this->settingsItem)) {
			throw new \SS6\ShopBundle\Model\AdminNavigation\Exception\MissingSettingsItemException(
				'Menu item of type ' . MenuItem::TYPE_SETTINGS . ' not found in config'
			);
		}
	}

	/**
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]
	 */
	public function getRegularItems() {
		return $this->regularItems;
	}

	/**
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem
	 */
	public function getSettingsItem() {
		return $this->settingsItem;
	}

	/**
	 * Finds deepest item matching specified route.
	 *
	 * @param string $route
	 * @param array|null $parameters
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem|null
	 */
	private function getItemMatchingRoute($route, array $parameters = null) {
		$item = $this->getItemMatchingRouteRecursive($this->getItems(), $route, $parameters);

		return $item;
	}

	/**
	 * Finds deepest item matching specified route.
	 *
	 * @param string $route
	 * @param array|null $parameters
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem|null
	 */
	private function getItemMatchingRouteRecursive(array $items, $route, array $parameters = null) {
		foreach ($items as $item) {
			if ($item->getItems() !== null) {
				$matchingItem = $this->getItemMatchingRouteRecursive($item->getItems(), $route, $parameters);

				if ($matchingItem !== null) {
					return $matchingItem;
				}
			}

			if ($this->isItemMatchingRoute($item, $route, $parameters)) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * @param \SS6\ShopBundle\Model\AdminNavigation\MenuItem $item
	 * @param string $route
	 * @param array|null $parameters
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem
	 */
	private function isItemMatchingRoute(MenuItem $item, $route, array $parameters = null) {
		if ($item->getRoute() !== $route) {
			return false;
		}

		if ($item->getRouteParameters() !== null) {
			foreach ($item->getRouteParameters() as $itemRouteParameterName => $itemRouteParameterValue) {
				if (!isset($parameters[$itemRouteParameterName])) {
					return false;
				}

				if ($parameters[$itemRouteParameterName] != $itemRouteParameterValue) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param \SS6\ShopBundle\Model\AdminNavigation\MenuItem $item
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]|null
	 */
	private function getItemPath(MenuItem $item) {
		return $this->getItemPathRecursive($this->getItems(), $item);
	}

	/**
	 * @param \SS6\ShopBundle\Model\AdminNavigation\MenuItem $items
	 * @param \SS6\ShopBundle\Model\AdminNavigation\MenuItem $item
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]|null
	 */
	private function getItemPathRecursive(array $items, MenuItem $item) {
		foreach ($items as $subitem) {
			if ($subitem === $item) {
				return [$item];
			}

			if ($subitem->getItems() !== null) {
				$path = $this->getItemPathRecursive($subitem->getItems(), $item);

				if ($path !== null) {
					array_unshift($path, $subitem);
					return $path;
				}
			}
		}

		return null;
	}

	/**
	 * @param string $route
	 * @param array|null $parameters
	 * @return \SS6\ShopBundle\Model\AdminNavigation\MenuItem[]
	 */
	public function getMenuPath($route, $parameters) {
		$menuPath = [];
		$matchingItem = $this->getItemMatchingRoute($route, $parameters);
		if ($matchingItem !== null) {
			$menuPath = $this->getItemPath($matchingItem);
		}

		return $menuPath;
	}

}
