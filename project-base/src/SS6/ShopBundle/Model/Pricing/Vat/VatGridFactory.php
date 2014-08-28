<?php

namespace SS6\ShopBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\PKGrid\ActionColumn;
use SS6\ShopBundle\Model\PKGrid\GridFactory;
use SS6\ShopBundle\Model\PKGrid\QueryBuilderDataSource;

class VatGridFactory {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\PKGrid\GridFactory
	 */
	private $gridFactory;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Model\PKGrid\GridFactory $gridFactory
	 */
	public function __construct(EntityManager $em, GridFactory $gridFactory) {
		$this->em = $em;
		$this->gridFactory = $gridFactory;
	}

	/**
	 * @return \SS6\ShopBundle\Model\PKGrid\PKGrid
	 */
	public function create() {
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder
			->select('v')
			->from(Vat::class, 'v');
		$dataSource = new QueryBuilderDataSource($queryBuilder);

		$grid = $this->gridFactory->create('vatList', $dataSource);
		$grid->setDefaultOrder('name');
		$grid->addColumn('name', 'v.name', 'Název', true);
		$grid->addColumn('percent', 'v.percent', 'Procent', true);
		$grid->addColumn('coefficient', 'v.percent', 'Koeficient', true);
		$grid->setActionColumnClassAttribute('table-col table-col-10');
		$grid->addActionColumn(ActionColumn::TYPE_DELETE, 'Smazat', 'admin_vat_delete', array('id' => 'v.id'))
			->setConfirmMessage('Opravdu chcete odstranit toto DPH?');

		return $grid;
	}
}
