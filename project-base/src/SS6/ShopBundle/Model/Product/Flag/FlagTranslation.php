<?php

namespace SS6\ShopBundle\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="flags_translations")
 * @ORM\Entity
 */
class FlagTranslation extends AbstractTranslation {

	/**
	 * @Prezent\Translatable(targetEntity="SS6\ShopBundle\Model\Product\Flag\Flag")
	 */
	protected $translatable;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100)
	 */
	private $name;

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

}
