<?php

namespace SS6\ShopBundle\Model\String;

class DatabaseSearching {

	/**
	 * @param string $string
	 * @return string
	 */
	public static function getLikeSearchString($string) {
		return str_replace(
			['%', '_', '*', '?'],
			['\%', '\_', '%', '_'],
			$string
		);
	}
}
