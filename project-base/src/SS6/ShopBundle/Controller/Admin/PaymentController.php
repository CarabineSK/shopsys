<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Router\Security\Annotation\CsrfProtection;
use SS6\ShopBundle\Form\Admin\Payment\PaymentEditFormTypeFactory;
use SS6\ShopBundle\Model\AdminNavigation\Breadcrumb;
use SS6\ShopBundle\Model\AdminNavigation\MenuItem;
use SS6\ShopBundle\Model\Payment\Detail\PaymentDetailFactory;
use SS6\ShopBundle\Model\Payment\Grid\PaymentGridFactory;
use SS6\ShopBundle\Model\Payment\PaymentEditDataFactory;
use SS6\ShopBundle\Model\Payment\PaymentEditFacade;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Payment\PaymentEditFormTypeFactory
	 */
	private $paymentEditFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\AdminNavigation\Breadcrumb
	 */
	private $breadcrumb;

	/**
	 * @var \SS6\ShopBundle\Model\Payment\Detail\PaymentDetailFactory
	 */
	private $paymentDetailFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Payment\Grid\PaymentGridFactory
	 */
	private $paymentGridFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Payment\PaymentEditDataFactory
	 */
	private $paymentEditDataFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Payment\PaymentEditFacade
	 */
	private $paymentEditFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade
	 */
	private $currencyFacade;

	public function __construct(
		PaymentEditFormTypeFactory $paymentEditFormTypeFactory,
		PaymentEditDataFactory $paymentEditDataFactory,
		CurrencyFacade $currencyFacade,
		PaymentEditFacade $paymentEditFacade,
		PaymentDetailFactory $paymentDetailFactory,
		PaymentGridFactory $paymentGridFactory,
		Breadcrumb $breadcrumb
	) {
		$this->paymentEditFormTypeFactory = $paymentEditFormTypeFactory;
		$this->paymentEditDataFactory = $paymentEditDataFactory;
		$this->currencyFacade = $currencyFacade;
		$this->paymentEditFacade = $paymentEditFacade;
		$this->paymentDetailFactory = $paymentDetailFactory;
		$this->paymentGridFactory = $paymentGridFactory;
		$this->breadcrumb = $breadcrumb;
	}

	/**
	 * @Route("/payment/new/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function newAction(Request $request) {
		$form = $this->createForm($this->paymentEditFormTypeFactory->create());
		$paymentEditData = $this->paymentEditDataFactory->createDefault();

		$form->setData($paymentEditData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$payment = $this->transactional(
				function () use ($paymentEditData) {
					return $this->paymentEditFacade->create($paymentEditData);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Byla vytvořena platba <strong><a href="{{ url }}">{{ name }}</a></strong>'),
				[
					'name' => $payment->getName(),
					'url' => $this->generateUrl('admin_payment_edit', ['id' => $payment->getId()]),
				]
			);
			return $this->redirectToRoute('admin_transportandpayment_list');
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
		}

		return $this->render('@SS6Shop/Admin/Content/Payment/new.html.twig', [
			'form' => $form->createView(),
			'currencies' => $this->currencyFacade->getAllIndexedById(),
		]);
	}

	/**
	 * @Route("/payment/edit/{id}", requirements={"id" = "\d+"})
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $id
	 */
	public function editAction(Request $request, $id) {
		$payment = $this->paymentEditFacade->getByIdWithTransports($id);

		$form = $this->createForm($this->paymentEditFormTypeFactory->create());

		$paymentEditData = $this->paymentEditDataFactory->createFromPayment($payment);

		$form->setData($paymentEditData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$this->transactional(
				function () use ($payment, $paymentEditData) {
					$this->paymentEditFacade->edit($payment, $paymentEditData);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Byla upravena platba <strong><a href="{{ url }}">{{ name }}</a></strong>'),
				[
					'name' => $payment->getName(),
					'url' => $this->generateUrl('admin_payment_edit', ['id' => $payment->getId()]),
				]
			);
			return $this->redirectToRoute('admin_transportandpayment_list');
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
		}

		$this->breadcrumb->replaceLastItem(new MenuItem(t('Editace platby - %name%', ['%name%' => $payment->getName()])));

		return $this->render('@SS6Shop/Admin/Content/Payment/edit.html.twig', [
			'form' => $form->createView(),
			'paymentDetail' => $this->paymentDetailFactory->createDetailForPayment($payment),
			'currencies' => $this->currencyFacade->getAllIndexedById(),
		]);
	}

	/**
	 * @Route("/payment/delete/{id}", requirements={"id" = "\d+"})
	 * @CsrfProtection
	 * @param int $id
	 */
	public function deleteAction($id) {
		try {
			$paymentName = $this->paymentEditFacade->getById($id)->getName();
			$this->transactional(
				function () use ($id) {
					$this->paymentEditFacade->deleteById($id);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Platba <strong>{{ name }}</strong> byla smazána'),
				[
					'name' => $paymentName,
				]
			);
		} catch (\SS6\ShopBundle\Model\Payment\Exception\PaymentNotFoundException $ex) {
			$this->getFlashMessageSender()->addErrorFlash(t('Zvolená platba neexistuje.'));
		}

		return $this->redirectToRoute('admin_transportandpayment_list');
	}

	public function listAction() {
		$grid = $this->paymentGridFactory->create();

		return $this->render('@SS6Shop/Admin/Content/Payment/list.html.twig', [
			'gridView' => $grid->createView(),
		]);
	}

}
