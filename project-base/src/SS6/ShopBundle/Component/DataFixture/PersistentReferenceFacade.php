<?php

namespace SS6\ShopBundle\Component\DataFixture;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Component\DataFixture\PersistentReferenceRepository;

class PersistentReferenceFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Component\DataFixture\PersistentReferenceRepository
	 */
	private $persistentReferenceRepository;

	/**
	 * @var \SS6\ShopBundle\Component\DataFixture\PersistentReference
	 */
	private $persistentReferencesByName = [];

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Component\DataFixture\PersistentReferenceRepository $persistentReferenceRepository
	 */
	public function __construct(EntityManager $em, PersistentReferenceRepository $persistentReferenceRepository) {
		$this->em = $em;
		$this->persistentReferenceRepository = $persistentReferenceRepository;
	}

	/**
	 * @param string $name
	 * @return object
	 */
	public function getReference($name) {
		$persistentReference = $this->persistentReferenceRepository->getByReferenceName($name);
		$entity = $this->em->find($persistentReference->getEntityName(), $persistentReference->getEntityId());

		if ($entity === null) {
			throw new \SS6\ShopBundle\Component\DataFixture\Exception\EntityNotFoundException($name);
		}

		return $entity;
	}

	/**
	 * @param string $name
	 * @param object $object
	 */
	public function persistReference($name, $object) {
		$entityName = get_class($object);

		if (method_exists($object, 'getId')) {
			$objectId = $object->getId();

			if ($objectId === null) {
				throw new \SS6\ShopBundle\Component\DataFixture\Exception\EntityIdIsNotSetException($name, $object);
			}

			if (array_key_exists($name, $this->persistentReferencesByName)) {
				$this->persistentReferencesByName[$name]->replace($entityName, $objectId);
			} else {
				$persistentReference = new PersistentReference($name, $entityName, $objectId);
				$this->persistentReferencesByName[$name] = $persistentReference;
				$this->em->persist($persistentReference);
			}
			$this->em->flush($persistentReference);
		} else {
			$message = 'Entity "' . $entityName . '" does not have a method "getId", which is necessary for persistent references.';
			throw new \SS6\ShopBundle\Component\DataFixture\Exception\MethodGetIdDoesNotExistException($message);
		}
	}

}
