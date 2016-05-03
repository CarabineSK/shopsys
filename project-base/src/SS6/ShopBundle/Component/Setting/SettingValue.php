<?php

namespace SS6\ShopBundle\Component\Setting;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="setting_values")
 * @ORM\Entity
 */
class SettingValue {

	const TYPE_STRING = 'string';
	const TYPE_INTEGER = 'integer';
	const TYPE_FLOAT = 'float';
	const TYPE_BOOLEAN = 'boolean';
	const TYPE_NULL = 'none';

	const BOOLEAN_TRUE = 'true';
	const BOOLEAN_FALSE = 'false';

	const DOMAIN_ID_COMMON = 0;
	const DOMAIN_ID_DEFAULT = null;

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $domainId;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $value;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=7)
	 */
	private $type;

	/**
	 * @param string $name
	 * @param string|int|float|bool|null $value
	 * @param int|null $domainId
	 */
	public function __construct($name, $value, $domainId) {
		$this->name = $name;
		$this->setValue($value);
		$this->domainId = $domainId;
	}

	/**
	 * @param string|int|float|bool|null $value
	 */
	public function edit($value) {
		$this->setValue($value);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string|int|float|bool|null
	 */
	public function getValue() {
		switch ($this->type) {
			case self::TYPE_INTEGER:
				return (int)$this->value;
			case self::TYPE_FLOAT:
				return (float)$this->value;
			case self::TYPE_BOOLEAN:
				return $this->value === self::BOOLEAN_TRUE;
			default:
				return $this->value;
		}
	}

	/**
	 * @return int|null
	 */
	public function getDomainId() {
		return $this->domainId;
	}

	/**
	 * @param string|int|float|bool|null $value
	 */
	private function setValue($value) {
		$this->type = $this->getValueType($value);
		if ($this->type === self::TYPE_BOOLEAN) {
			$this->value = $value === true ? self::BOOLEAN_TRUE : self::BOOLEAN_FALSE;
		} elseif ($this->type === self::TYPE_NULL) {
			$this->value = $value;
		} else {
			$this->value = (string)$value;
		}
	}

	/**
	 * @param string|int|float|bool|null $value
	 * @return string
	 */
	private function getValueType($value) {
		if (is_int($value)) {
			return self::TYPE_INTEGER;
		} elseif (is_float($value)) {
			return self::TYPE_FLOAT;
		} elseif (is_bool($value)) {
			return self::TYPE_BOOLEAN;
		} elseif (is_string($value)) {
			return self::TYPE_STRING;
		} elseif (is_null($value)) {
			return self::TYPE_NULL;
		}

		$message = 'Setting value type of "' . gettype($value) . '" is unsupported.'
			. ' Supported is string, integer, float, boolean or null.';
		throw new \SS6\ShopBundle\Component\Setting\Exception\InvalidArgumentException($message);
	}

}
