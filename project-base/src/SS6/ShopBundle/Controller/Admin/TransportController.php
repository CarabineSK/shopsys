<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Router\Security\Annotation\CsrfProtection;
use SS6\ShopBundle\Form\Admin\Transport\TransportEditFormTypeFactory;
use SS6\ShopBundle\Model\AdminNavigation\Breadcrumb;
use SS6\ShopBundle\Model\AdminNavigation\MenuItem;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade;
use SS6\ShopBundle\Model\Transport\Detail\TransportDetailFactory;
use SS6\ShopBundle\Model\Transport\Grid\TransportGridFactory;
use SS6\ShopBundle\Model\Transport\TransportEditDataFactory;
use SS6\ShopBundle\Model\Transport\TransportEditFacade;
use Symfony\Component\HttpFoundation\Request;

class TransportController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Transport\TransportEditFormTypeFactory
	 */
	private $transportEditFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\AdminNavigation\Breadcrumb
	 */
	private $breadcrumb;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade
	 */
	private $currencyFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Transport\Detail\TransportDetailFactory
	 */
	private $transportDetailFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Transport\Grid\TransportGridFactory
	 */
	private $transportGridFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Transport\TransportEditDataFactory
	 */
	private $transportEditDataFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Transport\TransportEditFacade
	 */
	private $transportEditFacade;

	public function __construct(
		TransportEditFacade $transportEditFacade,
		TransportGridFactory $transportGridFactory,
		TransportEditFormTypeFactory $transportEditFormTypeFactory,
		TransportEditDataFactory $transportEditDataFactory,
		CurrencyFacade $currencyFacade,
		TransportDetailFactory $transportDetailFactory,
		Breadcrumb $breadcrumb
	) {
		$this->transportEditFacade = $transportEditFacade;
		$this->transportGridFactory = $transportGridFactory;
		$this->transportEditFormTypeFactory = $transportEditFormTypeFactory;
		$this->transportEditDataFactory = $transportEditDataFactory;
		$this->currencyFacade = $currencyFacade;
		$this->transportDetailFactory = $transportDetailFactory;
		$this->breadcrumb = $breadcrumb;
	}

	/**
	 * @Route("/transport/new/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function newAction(Request $request) {
		$transportEditData = $this->transportEditDataFactory->createDefault();

		$form = $this->createForm($this->transportEditFormTypeFactory->create(), $transportEditData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$transport = $this->transactional(
				function () use ($form) {
					return $this->transportEditFacade->create($form->getData());
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Byla vytvořena doprava <strong><a href="{{ url }}">{{ name }}</a></strong>'),
				[
				'name' => $transport->getName(),
				'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
				]
			);
			return $this->redirectToRoute('admin_transportandpayment_list');
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
		}

		return $this->render('@SS6Shop/Admin/Content/Transport/new.html.twig', [
			'form' => $form->createView(),
			'currencies' => $this->currencyFacade->getAllIndexedById(),
		]);
	}

	/**
	 * @Route("/transport/edit/{id}", requirements={"id" = "\d+"})
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $id
	 */
	public function editAction(Request $request, $id) {
		$transport = $this->transportEditFacade->getById($id);
		/* @var $transport \SS6\ShopBundle\Model\Transport\Transport */

		$transportEditData = $this->transportEditDataFactory->createFromTransport($transport);

		$form = $this->createForm($this->transportEditFormTypeFactory->create(), $transportEditData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$this->transactional(
				function () use ($transport, $transportEditData) {
					$this->transportEditFacade->edit($transport, $transportEditData);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Byla upravena doprava <strong><a href="{{ url }}">{{ name }}</a></strong>'),
				[
					'name' => $transport->getName(),
					'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
				]
			);
			return $this->redirectToRoute('admin_transportandpayment_list');
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlash(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
		}

		$this->breadcrumb->replaceLastItem(new MenuItem(t('Editace dopravy - ') . $transport->getName()));

		return $this->render('@SS6Shop/Admin/Content/Transport/edit.html.twig', [
			'form' => $form->createView(),
			'transportDetail' => $this->transportDetailFactory->createDetailForTransportWithIndependentPrices($transport),
			'currencies' => $this->currencyFacade->getAllIndexedById(),
		]);
	}

	/**
	 * @Route("/transport/delete/{id}", requirements={"id" = "\d+"})
	 * @CsrfProtection
	 * @param int $id
	 */
	public function deleteAction($id) {
		try {
			$transportName = $this->transportEditFacade->getById($id)->getName();
			$this->transactional(
				function () use ($id) {
					$this->transportEditFacade->deleteById($id);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Doprava <strong>{{ name }}</strong> byla smazána'),
				[
					'name' => $transportName,
				]
			);
		} catch (\SS6\ShopBundle\Model\Transport\Exception\TransportNotFoundException $ex) {
			$this->getFlashMessageSender()->addErrorFlash(t('Zvolená doprava neexistuje.'));
		}

		return $this->redirectToRoute('admin_transportandpayment_list');
	}

	public function listAction() {
		$grid = $this->transportGridFactory->create();

		return $this->render('@SS6Shop/Admin/Content/Transport/list.html.twig', [
			'gridView' => $grid->createView(),
		]);
	}

}
