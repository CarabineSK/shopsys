<?php

namespace SS6\ShopBundle\DataFixtures\Base;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SS6\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\DataFixtures\Base\SettingValueDataFixture;
use SS6\ShopBundle\Model\Category\Category;
use SS6\ShopBundle\Model\Category\CategoryData;
use SS6\ShopBundle\Model\Category\CategoryDomain;

class CategoryRootDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface {

	const ROOT = 'category_root';

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 */
	public function load(ObjectManager $manager) {
		$domain = $this->get(Domain::class);
		/* @var $domain \SS6\ShopBundle\Component\Domain\Domain */

		$rootCategory = new Category(new CategoryData());
		$manager->persist($rootCategory);
		$manager->flush($rootCategory);
		$this->addReference(self::ROOT, $rootCategory);

		foreach ($domain->getAll() as $domainConfig) {
			$categoryDomain = new CategoryDomain($rootCategory, $domainConfig->getId());
			$manager->persist($categoryDomain);
		}

		$manager->flush();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDependencies() {
		return [
			SettingValueDataFixture::class,
		];
	}

}
