<?php
/**
 * Author: Jakub Dolba
 * Date: 25. 4. 2015
 * Description:
 */

namespace SS6\ShopBundle\Model\Order\Listing;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use SS6\ShopBundle\Model\Localization\Localization;
use SS6\ShopBundle\Model\Order\Order;

class OrderListAdminRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Localization\Localization
	 */
	private $localization;

	public function __construct(EntityManager $em, Localization $localization) {
		$this->em = $em;
		$this->localization = $localization;
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getOrderListQueryBuilder() {
		$queryBuilder = $this->em->createQueryBuilder()
			->select('
				o.id,
				o.number,
				o.domainId,
				o.createdAt,
				MAX(ost.name) AS statusName,
				o.totalPriceWithVat,
				(CASE WHEN o.companyName IS NOT NULL
							THEN o.companyName
							ELSE CONCAT(o.firstName, \' \', o.lastName)
						END) AS customerName')
			->from(Order::class, 'o')
			->where('o.deleted = :deleted')
			->join('o.status', 'os')
			->join('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
			->groupBy('o.id')
			->setParameter('deleted', false)
			->setParameter('locale', $this->localization->getLocale());

		return $queryBuilder;
	}
}