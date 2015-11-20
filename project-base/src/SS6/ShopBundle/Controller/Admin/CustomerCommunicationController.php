<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Domain\SelectedDomain;
use SS6\ShopBundle\Component\Setting\Setting;
use SS6\ShopBundle\Form\Admin\CustomerCommunication\CustomerCommunicationFormType;
use SS6\ShopBundle\Model\Order\Mail\OrderMailService;
use Symfony\Component\HttpFoundation\Request;

class CustomerCommunicationController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Component\Domain\SelectedDomain
	 */
	private $selectedDomain;

	/**
	 * @var \SS6\ShopBundle\Component\Setting\Setting
	 */
	private $setting;

	public function __construct(
		Setting $setting,
		SelectedDomain $selectedDomain
	) {
		$this->setting = $setting;
		$this->selectedDomain = $selectedDomain;
	}

	/**
	 * @Route("/customer-communication/")
	 */
	public function indexAction() {
		return $this->render('@SS6Shop/Admin/Content/CustomerCommunication/index.html.twig');
	}

	/**
	 * @Route("/customer-communication/order-submitted/")
	 */
	public function orderSubmittedAction(Request $request) {
		$data = $this->setting->get(Setting::ORDER_SUBMITTED_SETTING_NAME, $this->selectedDomain->getId());
		$form = $this->createForm(new CustomerCommunicationFormType());

		$form->setData(['content' => $data]);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$formData = $form->getData();
			$this->setting->set(Setting::ORDER_SUBMITTED_SETTING_NAME, $formData['content'], $this->selectedDomain->getId());

			$this->getFlashMessageSender()->addSuccessFlash(t('Nastavení textu po potvrzení objednávky bylo upraveno'));

			return $this->redirectToRoute('admin_customercommunication_ordersubmitted');
		}

		return $this->render('@SS6Shop/Admin/Content/CustomerCommunication/orderSubmitted.html.twig', [
			'form' => $form->createView(),
			'VARIABLE_TRANSPORT_INSTRUCTIONS' => OrderMailService::VARIABLE_TRANSPORT_INSTRUCTIONS,
			'VARIABLE_PAYMENT_INSTRUCTIONS' => OrderMailService::VARIABLE_PAYMENT_INSTRUCTIONS,
			'VARIABLE_ORDER_DETAIL_URL' => OrderMailService::VARIABLE_ORDER_DETAIL_URL,
			'VARIABLE_NUMBER' => OrderMailService::VARIABLE_NUMBER,
		]);
	}

}
