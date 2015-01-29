<?php

namespace SS6\ShopBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Product\Flag\Flag;

class FlagRepository {

	/**
	 * @var \Doctrine\ORM\EntityRepository
	 */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->em = $entityManager;
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getFlagRepository() {
		return $this->em->getRepository(Flag::class);
	}

	/**
	 * @param int $flagId
	 * @return \SS6\ShopBundle\Model\Product\Flag\Flag|null
	 */
	public function findById($flagId) {
		return $this->getFlagRepository()->find($flagId);
	}

	/**
	 * @param int $flagId
	 * @return \SS6\ShopBundle\Model\Product\Flag\Flag
	 */
	public function getById($flagId) {
		$flag = $this->findById($flagId);

		if ($flag === null) {
			throw new \SS6\ShopBundle\Model\Product\Flag\Exception\FlagNotFoundException($flagId);
		}

		return $flag;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Product\Flag\Flag[]
	 */
	public function findAll() {
		return $this->getFlagRepository()->findBy([], ['id' => 'asc']);
	}

}
