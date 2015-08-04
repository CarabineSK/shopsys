<?php

namespace SS6\ShopBundle\Model\Grid\Ordering;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Grid\Ordering\GridOrderingService;

class GridOrderingFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Grid\Ordering\GridOrderingService
	 */
	private $gridOrderingService;

	public function __construct(EntityManager $em, GridOrderingService $gridOrderingService) {
		$this->em = $em;
		$this->gridOrderingService = $gridOrderingService;
	}

	/**
	 * @param string $entityClass
	 * @param array $rowIds
	 */
	public function saveOrdering($entityClass, array $rowIds) {
		$entityRepository = $this->getEntityRepository($entityClass);
		$position = 0;

		foreach ($rowIds as $rowId) {
			$entity = $entityRepository->find($rowId);
			$this->gridOrderingService->setPosition($entity, $position++);
		}

		$this->em->flush();
	}

	/**
	 * @param string $entityClass
	 * @return mixed
	 */
	private function getEntityRepository($entityClass) {
		$interfaces = class_implements($entityClass);
		if (array_key_exists(OrderableEntityInterface::class, $interfaces)) {
			return $this->em->getRepository($entityClass);
		} else {
			throw new \SS6\ShopBundle\Model\Grid\Ordering\Exception\EntityIsNotOrderableException();
		}
	}

}
