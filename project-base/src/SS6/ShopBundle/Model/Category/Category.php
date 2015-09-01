<?php

namespace SS6\ShopBundle\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use SS6\ShopBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="categories")
 * @ORM\Entity
 */
class Category extends AbstractTranslatableEntity {

	/**
	 * @var int
	 *
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 * @var \SS6\ShopBundle\Model\Category\CategoryTranslation[]
	 *
	 * @Prezent\Translations(targetEntity="SS6\ShopBundle\Model\Category\CategoryTranslation")
	 */
	protected $translations;

	/**
	 * @var \SS6\ShopBundle\Model\Category\Category|null
	 *
	 * @Gedmo\TreeParent
	 * @ORM\ManyToOne(targetEntity="SS6\ShopBundle\Model\Category\Category", inversedBy="children")
	 * @ORM\JoinColumn(nullable=true, name="parent_id", referencedColumnName="id")
	 */
	private $parent;

	/**
	 * @var \SS6\ShopBundle\Model\Category\Category[]
	 *
	 * @ORM\OneToMany(targetEntity="SS6\ShopBundle\Model\Category\Category", mappedBy="parent")
	 * @ORM\OrderBy({"lft" = "ASC"})
	 */
	private $children;

	/**
	 * @var int
	 *
	 * @Gedmo\TreeLevel
	 * @ORM\Column(type="integer")
	 */
	private $level;

	/**
	 * @var int
	 *
	 * @Gedmo\TreeLeft
	 * @ORM\Column(type="integer")
	 */
	private $lft;

	/**
	 * @var int
	 *
	 * @Gedmo\TreeRight
	 * @ORM\Column(type="integer")
	 */
	private $rgt;

	/**
	 * @internal
	 *
	 * @ORM\OneToMany(targetEntity="SS6\ShopBundle\Model\Category\CategoryDomain", mappedBy="category", fetch="EXTRA_LAZY")
	 */
	private $domains;

	/**
	 * @internal
	 *
	 * @ORM\ManyToMany(targetEntity="SS6\ShopBundle\Model\Product\Product", mappedBy="categories", fetch="EXTRA_LAZY")
	 */
	private $products;

	/**
	 * @var \SS6\ShopBundle\Model\Feed\Category\FeedCategory|null
	 *
	 * @ORM\ManyToOne(targetEntity="SS6\ShopBundle\Model\Feed\Category\FeedCategory")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $heurekaCzFeedCategory;

	/**
	 * @param \SS6\ShopBundle\Model\Category\CategoryData $categoryData
	 */
	public function __construct(CategoryData $categoryData) {
		$this->setParent($categoryData->parent);
		$this->translations = new ArrayCollection();
		$this->setTranslations($categoryData);
		$this->heurekaCzFeedCategory = $categoryData->heurekaCzFeedCategory;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\CategoryData $categoryData
	 */
	public function edit(CategoryData $categoryData) {
		$this->setParent($categoryData->parent);
		$this->setTranslations($categoryData);
		$this->heurekaCzFeedCategory = $categoryData->heurekaCzFeedCategory;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\Category|null $parent
	 */
	public function setParent(Category $parent = null) {
		$this->parent = $parent;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string|null $locale
	 * @return string
	 */
	public function getName($locale = null) {
		return $this->translation($locale)->getName();
	}

	/**
	 * @return string[locale]
	 */
	public function getNames() {
		$names = [];
		foreach ($this->translations as $translation) {
			$names[$translation->getLocale()] = $translation->getName();
		}

		return $names;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Category\Category|null
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Category\Category[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @return int
	 */
	public function getLft() {
		return $this->lft;
	}

	/**
	 * @return int
	 */
	public function getRgt() {
		return $this->rgt;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Feed\Category\FeedCategory|null
	 */
	public function getHeurekaCzFeedCategory() {
		return $this->heurekaCzFeedCategory;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Category\CategoryData $categoryData
	 */
	private function setTranslations(CategoryData $categoryData) {
		foreach ($categoryData->name as $locale => $name) {
			$this->translation($locale)->setName($name);
		}
	}

	/**
	 * @return \SS6\ShopBundle\Model\Category\CategoryTranslation
	 */
	protected function createTranslation() {
		return new CategoryTranslation();
	}

}
