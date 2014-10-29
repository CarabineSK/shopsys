<?php

namespace SS6\ShopBundle\Component;

class Condition {

	/**
	 * @param mixed $testVariable
	 * @param mixed $default
	 * @return mixed
	 */
	public static function ifNull($testVariable, $default) {
		return $testVariable !== null ? $testVariable : $default;
	}

	/**
	 * @param array $array
	 * @param string|int $key
	 * @param mixed $defaultValue
	 */
	public static function setArrayDefaultValue(&$array, $key, $defaultValue = null) {
		if (!array_key_exists($key, $array)) {
			$array[$key] = $defaultValue;
		}
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	public static function mixedToArray($value) {
		if ($value === null) {
			$value = [];
		} elseif (!is_array($value)) {
			$value = [$value];
		}

		return $value;
	}

	/**
	 * @param string $string
	 * @return boolean
	 */
	public static function stringToBooleanValue($string) {
		if ($string === 'true') {
			return true;
		} elseif ($string === 'false') {
			return false;
		}
	}
}
