<?php

namespace SS6\ShopBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl;

class FriendlyUrlRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getFriendlyUrlRepository() {
		return $this->em->getRepository(FriendlyUrl::class);
	}

	/**
	 * @param int $domainId
	 * @param string $slug
	 * @return \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
	 */
	public function findByDomainIdAndSlug($domainId, $slug) {
		return $this->getFriendlyUrlRepository()->findOneBy(
			[
				'domainId' => $domainId,
				'slug' => $slug,
			]
		);
	}

	/**
	 * @param int $domainId
	 * @param string $routeName
	 * @param int $entityId
	 * @return \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl
	 */
	public function getMainFriendlyUrl($domainId, $routeName, $entityId) {
		$criteria = [
			'domainId' => $domainId,
			'routeName' => $routeName,
			'entityId' => $entityId,
			'main' => true,
		];
		$friendlyUrl = $this->getFriendlyUrlRepository()->findOneBy($criteria);

		if ($friendlyUrl === null) {
			throw new \SS6\ShopBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException();
		}

		return $friendlyUrl;
	}

	/**
	 * @param int $domainId
	 * @param string $routeName
	 * @param int $entityId
	 * @return \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
	 */
	public function findMainFriendlyUrl($domainId, $routeName, $entityId) {
		$criteria = [
			'domainId' => $domainId,
			'routeName' => $routeName,
			'entityId' => $entityId,
			'main' => true,
		];

		return $this->getFriendlyUrlRepository()->findOneBy($criteria);
	}

	/**
	 *
	 * @param string $routeName
	 * @param int $entityId
	 * @return \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
	 */
	public function getAllByRouteNameAndEntityId($routeName, $entityId) {
		$criteria = [
			'routeName' => $routeName,
			'entityId' => $entityId,
		];

		return $this->getFriendlyUrlRepository()->findBy(
			$criteria, [
				'domainId' => 'ASC',
				'slug' => 'ASC',
			]
		);
	}

	/**
	 *
	 * @param string $routeName
	 * @param int $entityId
	 * @param int $domainId
	 * @return \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
	 */
	public function getAllByRouteNameAndEntityIdAndDomainId($routeName, $entityId, $domainId) {
		$criteria = [
			'routeName' => $routeName,
			'entityId' => $entityId,
			'domainId' => $domainId,
		];

		return $this->getFriendlyUrlRepository()->findBy($criteria);
	}

	/**
	 * @param \SS6\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
	 * @return bool
	 */
	public function isMainFriendlyUrl(FriendlyUrl $friendlyUrl) {
		$mainFriendlyUrl = $this->getMainFriendlyUrl(
			$friendlyUrl->getDomainId(),
			$friendlyUrl->getRouteName(),
			$friendlyUrl->getEntityId()
		);

		return $mainFriendlyUrl === $friendlyUrl;
	}
}
