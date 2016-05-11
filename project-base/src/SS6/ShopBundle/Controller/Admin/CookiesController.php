<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Domain\SelectedDomain;
use SS6\ShopBundle\Form\Admin\Cookies\CookiesSettingFormTypeFactory;
use SS6\ShopBundle\Model\Cookies\CookiesFacade;
use Symfony\Component\HttpFoundation\Request;

class CookiesController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Component\Domain\SelectedDomain
	 */
	private $selectedDomain;

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Cookies\CookiesSettingFormTypeFactory
	 */
	private $cookiesSettingFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Cookies\CookiesFacade
	 */
	private $cookiesFacade;

	public function __construct(
		SelectedDomain $selectedDomain,
		CookiesSettingFormTypeFactory $cookiesSettingFormTypeFactory,
		CookiesFacade $cookiesFacade
	) {
		$this->selectedDomain = $selectedDomain;
		$this->cookiesSettingFormTypeFactory = $cookiesSettingFormTypeFactory;
		$this->cookiesFacade = $cookiesFacade;
	}

	/**
	 * @Route("/cookies/setting/")
	 */
	public function settingAction(Request $request) {
		$selectedDomainId = $this->selectedDomain->getId();
		$cookiesArticle = $this->cookiesFacade->findCookiesArticleByDomainId($selectedDomainId);

		$cookiesSettingData = [
			'cookiesArticle' => $cookiesArticle,
		];

		$form = $this->createForm($this->cookiesSettingFormTypeFactory->createForDomain($selectedDomainId));
		$form->setData($cookiesSettingData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$cookiesArticle = $form->getData()['cookiesArticle'];

			$this->cookiesFacade->setCookiesArticleOnDomain(
				$cookiesArticle,
				$selectedDomainId
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(t('Bylo upraveno nastavení informací o cookies.'));
			return $this->redirectToRoute('admin_cookies_setting');
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
		}

		return $this->render('@SS6Shop/Admin/Content/Cookies/setting.html.twig', [
			'form' => $form->createView(),
		]);
	}
}
