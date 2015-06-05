<?php

namespace SS6\ShopBundle\Model\Pricing\Vat;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Payment\Payment;
use SS6\ShopBundle\Model\Pricing\Vat\Vat;
use SS6\ShopBundle\Model\Product\Product;
use SS6\ShopBundle\Model\Transport\Transport;

class VatRepository {

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
	private function getVatRepository() {
		return $this->em->getRepository(Vat::class);
	}

	/**
	 * @param string $vatAlias
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function getQueryBuilderForAll($vatAlias) {
		return $this->getVatRepository()
			->createQueryBuilder($vatAlias)
			->where($vatAlias . '.replaceWith IS NULL')
			->orderBy($vatAlias . '.percent');
	}

	/**
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	public function getAll() {
		return $this->getQueryBuilderForAll('v')->getQuery()->getResult();
	}

	/**
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	public function getAllIncludingMarkedForDeletion() {
		return $this->getVatRepository()->findAll();
	}

	/**
	 * @param int $vatId
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat|null
	 */
	public function findById($vatId) {
		return $this->getVatRepository()->find($vatId);
	}

	/**
	 * @param int $vatId
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat
	 */
	public function getById($vatId) {
		$vat = $this->findById($vatId);

		if ($vat === null) {
			throw new \SS6\ShopBundle\Model\Pricing\Vat\Exception\VatNotFoundException('Vat with ID ' . $vatId . ' not found.');
		}

		return $vat;
	}

	/**
	 * @param int $vatId
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	public function getAllExceptId($vatId) {
		$qb = $this->getQueryBuilderForAll('v')
			->andWhere('v.id != :id')
			->setParameter('id', $vatId);

		return $qb->getQuery()->getResult();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat $vat
	 * @return bool
	 */
	public function existsVatToBeReplacedWith(Vat $vat) {
		$query = $this->em->createQuery('
			SELECT COUNT(v)
			FROM ' . Vat::class . ' v
			WHERE v.replaceWith = :vat')
			->setParameter('vat', $vat);
		return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	public function getVatsWithoutProductsMarkedForDeletion() {
		$query = $this->em->createQuery('
			SELECT v
			FROM ' . Vat::class . ' v
			LEFT JOIN ' . Product::class . ' p WITH p.vat = v
			WHERE v.replaceWith IS NOT NULL
			GROUP BY v
			HAVING COUNT(p) = 0');

		return $query->getResult();
	}

	public function isVatUsed(Vat $vat) {
		return $this->existsPaymentWithVat($vat)
			|| $this->existsTransportWithVat($vat)
			|| $this->existsProductWithVat($vat);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat $vat
	 * @return bool
	 */
	private function existsPaymentWithVat(Vat $vat) {
		$query = $this->em->createQuery('
			SELECT COUNT(p)
			FROM ' . Payment::class . ' p
			WHERE p.vat= :vat')
			->setParameter('vat', $vat);
		return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat $vat
	 * @return bool
	 */
	private function existsTransportWithVat(Vat $vat) {
		$query = $this->em->createQuery('
			SELECT COUNT(t)
			FROM ' . Transport::class . ' t
			WHERE t.vat= :vat')
			->setParameter('vat', $vat);
		return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat $vat
	 * @return bool
	 */
	private function existsProductWithVat(Vat $vat) {
		$query = $this->em->createQuery('
			SELECT COUNT(p)
			FROM ' . Product::class . ' p
			WHERE p.vat= :vat')
			->setParameter('vat', $vat);
		return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
	}

}
