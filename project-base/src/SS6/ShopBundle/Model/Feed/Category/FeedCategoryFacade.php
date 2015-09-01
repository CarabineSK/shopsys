<?php

namespace SS6\ShopBundle\Model\Feed\Category;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Feed\Category\FeedCategory;
use SS6\ShopBundle\Model\Feed\Category\FeedCategoryRepository;

class FeedCategoryFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Feed\Category\FeedCategoryRepository
	 */
	private $feedCategoryRepository;

	public function __construct(
		EntityManager $em,
		FeedCategoryRepository $feedCategoryRepository
	) {
		$this->em = $em;
		$this->feedCategoryRepository = $feedCategoryRepository;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Feed\Category\FeedCategoryData[] $feedCategoriesData
	 */
	public function replaceFeedCategories(array $feedCategoriesData) {
		$this->deleteOldFeedCategories($feedCategoriesData);
		$this->createOrEditCategories($feedCategoriesData);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Feed\Category\FeedCategoryData[] $currentFeedCategoriesData
	 */
	private function deleteOldFeedCategories(array $currentFeedCategoriesData) {
		$currentExtIds = [];

		foreach ($currentFeedCategoriesData as $currentFeedCategoryData) {
			$currentExtIds[] = $currentFeedCategoryData->extId;
		}

		$this->feedCategoryRepository->deleteByExtIdNotIn($currentExtIds);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Feed\Category\FeedCategoryData[] $feedCategoriesData
	 */
	private function createOrEditCategories(array $feedCategoriesData) {
		$feedCategoriesByExtId = $this->feedCategoryRepository->getAllIndexedByExtId();

		foreach ($feedCategoriesData as $feedCategoryData) {
			if (!array_key_exists($feedCategoryData->extId, $feedCategoriesByExtId)) {
				$feedCategory = new FeedCategory($feedCategoryData);
				$this->em->persist($feedCategory);
				$feedCategoriesByExtId[$feedCategoryData->extId] = $feedCategory;
			} else {
				$feedCategory = $feedCategoriesByExtId[$feedCategoryData->extId];
				$feedCategory->edit($feedCategoryData);
			}
		}

		$this->em->flush();
	}

}
