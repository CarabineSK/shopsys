<?php

namespace SS6\ShopBundle\Model\Product\Unit;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use SS6\ShopBundle\Component\Grid\GridFactory;
use SS6\ShopBundle\Component\Grid\GridFactoryInterface;
use SS6\ShopBundle\Component\Grid\QueryBuilderDataSource;
use SS6\ShopBundle\Component\Translation\Translator;
use SS6\ShopBundle\Model\Localization\Localization;

class UnitGridFactory implements GridFactoryInterface {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Component\Grid\GridFactory
	 */
	private $gridFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Localization\Localization
	 */
	private $localization;

	/**
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	private $translator;

	public function __construct(
		EntityManager $em,
		GridFactory $gridFactory,
		Localization $localization,
		Translator $translator
	) {
		$this->em = $em;
		$this->gridFactory = $gridFactory;
		$this->localization = $localization;
		$this->translator = $translator;
	}

	/**
	 * @return \SS6\ShopBundle\Component\Grid\Grid
	 */
	public function create() {
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder
			->select('u, ut')
			->from(Unit::class, 'u')
			->join('u.translations', 'ut', Join::WITH, 'ut.locale = :locale')
			->setParameter('locale', $this->localization->getDefaultLocale());
		$dataSource = new QueryBuilderDataSource($queryBuilder, 'u.id');

		$grid = $this->gridFactory->create('unitList', $dataSource);
		$grid->setDefaultOrder('name');

		$grid->addColumn('name', 'ut.name', $this->translator->trans('Název'), true);

		$grid->setActionColumnClassAttribute('table-col table-col-10');
		$grid->addActionColumn(
				'delete',
				$this->translator->trans('Smazat'),
				'admin_unit_deleteconfirm',
				['id' => 'u.id']
			)
			->setAjaxConfirm();

		$grid->setTheme('@SS6Shop/Admin/Content/Unit/listGrid.html.twig');

		return $grid;
	}
}
