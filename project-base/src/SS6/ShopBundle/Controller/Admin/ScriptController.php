<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Grid\GridFactory;
use SS6\ShopBundle\Component\Grid\QueryBuilderDataSource;
use SS6\ShopBundle\Form\Admin\Script\ScriptFormType;
use SS6\ShopBundle\Model\Script\Script;
use SS6\ShopBundle\Model\Script\ScriptData;
use SS6\ShopBundle\Model\Script\ScriptFacade;
use Symfony\Component\HttpFoundation\Request;

class ScriptController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Model\Script\ScriptFacade
	 */
	private $scriptFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Grid\GridFactory
	 */
	private $gridFactory;

	public function __construct(ScriptFacade $scriptFacade, GridFactory $gridFactory) {
		$this->scriptFacade = $scriptFacade;
		$this->gridFactory = $gridFactory;
	}

	/**
	 * @Route("/script/new/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function newAction(Request $request) {
		$form = $this->createForm(new ScriptFormType());
		$scriptData = new ScriptData();
		$scriptVariables = $this->getOrderSentPageScriptVariableLabelsIndexedByVariables();

		$form->setData($scriptData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$scriptData = $form->getData();

			$script = $this->scriptFacade->create($scriptData);

			$this->getFlashMessageSender()
				->addSuccessFlashTwig(
					t('Byl vytvořen skript <a href="{{ url }}"><strong>{{ name }}</strong></a>'),
					[
						'name' => $script->getName(),
						'url' => $this->generateUrl('admin_script_edit', ['scriptId' => $script->getId()]),
					]
				);

			return $this->redirectToRoute('admin_script_list');
		}

		return $this->render('@SS6Shop/Admin/Content/Script/new.html.twig', [
			'form' => $form->createView(),
			'scriptVariables' => $scriptVariables,
		]);
	}

	/**
	 * @Route("/script/edit/{scriptId}")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $scriptId
	 */
	public function editAction(Request $request, $scriptId) {
		$script = $this->scriptFacade->getById($scriptId);
		$scriptVariables = $this->getOrderSentPageScriptVariableLabelsIndexedByVariables();

		$form = $this->createForm(new ScriptFormType());
		$scriptData = new ScriptData();
		$scriptData->setFromEntity($script);

		$form->setData($scriptData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$scriptData = $form->getData();

			$script = $this->scriptFacade->edit($scriptId, $scriptData);

			$this->getFlashMessageSender()
				->addSuccessFlashTwig(
					t('Byl upraven skript <a href="{{ url }}"><strong>{{ name }}</strong></a>'),
					[
						'name' => $script->getName(),
						'url' => $this->generateUrl('admin_script_edit', ['scriptId' => $scriptId]),
					]
				);
			return $this->redirectToRoute('admin_script_list');
		}

		return $this->render('@SS6Shop/Admin/Content/Script/edit.html.twig', [
			'script' => $script,
			'form' => $form->createView(),
			'scriptVariables' => $scriptVariables,
		]);
	}

	/**
	 * @Route("/script/list/")
	 */
	public function listAction() {
		$dataSource = new QueryBuilderDataSource($this->scriptFacade->getAllQueryBuilder(), 's.id');

		$grid = $this->gridFactory->create('scriptsList', $dataSource);

		$grid->addColumn('name', 's.name', t('Název skriptu'));
		$grid->addColumn('placement', 's.placement', t('Umístění'));
		$grid->addActionColumn('edit', t('Upravit'), 'admin_script_edit', ['scriptId' => 's.id']);
		$grid->addActionColumn('delete', t('Smazat'), 'admin_script_delete', ['scriptId' => 's.id'])
			->setConfirmMessage('Opravdu chcete odstranit tento skript?');

		$grid->setTheme('@SS6Shop/Admin/Content/Script/listGrid.html.twig', [
			'PLACEMENT_ORDER_SENT_PAGE' => Script::PLACEMENT_ORDER_SENT_PAGE,
			'PLACEMENT_ALL_PAGES' => Script::PLACEMENT_ALL_PAGES,
		]);

		return $this->render('@SS6Shop/Admin/Content/Script/list.html.twig', [
			'gridView' => $grid->createView(),
		]);
	}

	/**
	 * @Route("/script/delete/{scriptId}")
	 * @param int $scriptId
	 */
	public function deleteAction($scriptId) {
		try {
			$script = $this->scriptFacade->getById($scriptId);

			$this->scriptFacade->delete($scriptId);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Skript <strong>{{ name }}</strong> byl smazán'),
				[
					'name' => $script->getName(),
				]
			);
		} catch (\SS6\ShopBundle\Model\Script\Exception\ScriptNotFoundException $ex) {
			$this->getFlashMessageSender()->addErrorFlash(t('Zvolený skript neexistuje'));
		}

		return $this->redirectToRoute('admin_script_list');
	}

	/**
	 * @return string[]
	 */
	private function getOrderSentPageScriptVariableLabelsIndexedByVariables() {
		return [
			ScriptFacade::VARIABLE_NUMBER => t('Číslo objednávky'),
			ScriptFacade::VARIABLE_TOTAL_PRICE => t('Celková cena objednávky s DPH'),
		];
	}

}
