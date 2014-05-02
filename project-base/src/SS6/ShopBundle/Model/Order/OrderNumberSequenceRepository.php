<?php

namespace SS6\ShopBundle\Model\Order;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;

class OrderNumberSequenceRepository {
	const ID = 1;

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
	private function getOrderNumberSequenceRepository() {
		return $this->em->getRepository(OrderNumberSequence::class);
	}

	/**
	 * @return string
	 * @throws \SS6\ShopBundle\Model\Order\Exception\OrderNumberSequenceNotFoundException
	 */
	public function getNextNumber() {
		try {
			$this->em->beginTransaction();

			$requestedNumber = time();

			$orderNumberSequence = $this->getOrderNumberSequenceRepository()->find(self::ID, LockMode::PESSIMISTIC_WRITE);
			/* @var $orderNumberSequence \SS6\ShopBundle\Model\Order\OrderNumberSequence|null */
			if ($orderNumberSequence === null) {
				throw new \SS6\ShopBundle\Model\Order\Exception\OrderNumberSequenceNotFoundException(
					'Order number sequence ID ' . self::ID . ' not found');
			}

			$lastNumber = $orderNumberSequence->getNumber();

			if ($requestedNumber <= $lastNumber) {
				$requestedNumber = $lastNumber + 1;
			}

			$orderNumberSequence->setNumber($requestedNumber);
			$this->em->persist($orderNumberSequence);
			$this->em->flush($orderNumberSequence);
			$this->em->commit();
		} catch (Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $requestedNumber;
	}
}
