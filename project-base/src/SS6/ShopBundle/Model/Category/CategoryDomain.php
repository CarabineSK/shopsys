<?php

namespace SS6\ShopBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use SS6\ShopBundle\Model\Category\Category;

/**
 * @ORM\Table(name="category_domains")
 * @ORM\Entity
 */
class CategoryDomain {

	/**
	 * @var \SS6\ShopBundle\Model\Product\Product
	 *
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="SS6\ShopBundle\Model\Category\Category", inversedBy="domains")
	 * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $category;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	private $domainId;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $hidden;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $visible;

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category $category
	 * @param int $domainId
	 */
	public function __construct(Category $category, $domainId) {
		$this->category = $category;
		$this->domainId = $domainId;
		$this->hidden = false;
		$this->visible = false;
	}

	/**
	 * @return int
	 */
	public function getDomainId() {
		return $this->domainId;
	}

	/**
	 * @return bool
	 */
	public function isHidden() {
		return $this->hidden;
	}

	/**
	 * @param bool $hidden
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	/**
	 * @return bool
	 */
	public function isVisible() {
		return $this->visible;
	}

}
