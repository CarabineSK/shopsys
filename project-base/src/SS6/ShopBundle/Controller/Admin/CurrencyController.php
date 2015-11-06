<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Component\Router\Security\Annotation\CsrfProtection;
use SS6\ShopBundle\Form\Admin\Pricing\Currency\CurrencySettingsFormType;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade;
use SS6\ShopBundle\Model\Pricing\Currency\Grid\CurrencyInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory
	 */
	private $confirmDeleteResponseFactory;

	/**
	 * @var \SS6\ShopBundle\Component\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade
	 */
	private $currencyFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Currency\Grid\CurrencyInlineEdit
	 */
	private $currencyInlineEdit;

	public function __construct(
		CurrencyFacade $currencyFacade,
		CurrencyInlineEdit $currencyInlineEdit,
		ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
		Domain $domain
	) {
		$this->currencyFacade = $currencyFacade;
		$this->currencyInlineEdit = $currencyInlineEdit;
		$this->confirmDeleteResponseFactory = $confirmDeleteResponseFactory;
		$this->domain = $domain;
	}

	/**
	 * @Route("/currency/list/")
	 */
	public function listAction() {
		$grid = $this->currencyInlineEdit->getGrid();

		return $this->render('@SS6Shop/Admin/Content/Currency/list.html.twig', [
			'gridView' => $grid->createView(),
		]);
	}

	/**
	 * @Route("/currency/delete_confirm/{id}", requirements={"id" = "\d+"})
	 * @param int $id
	 */
	public function deleteConfirmAction($id) {
		try {
			$currency = $this->currencyFacade->getById($id);
			$message = t(
				'Opravdu si přejete trvale odstranit měnu "%name%"?',
				['%name%' => $currency->getName()]
			);

			return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_currency_delete', $id);
		} catch (\SS6\ShopBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException $ex) {
			return new Response(t('Zvolená měna neexistuje.'));
		}

	}

	/**
	 * @Route("/currency/delete/{id}", requirements={"id" = "\d+"})
	 * @CsrfProtection
	 * @param int $id
	 */
	public function deleteAction($id) {
		try {
			$fullName = $this->currencyFacade->getById($id)->getName();

			$this->transactional(
				function () use ($id) {
					$this->currencyFacade->deleteById($id);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(
				t('Měna <strong>{{ name }}</strong> byla smazána'),
				[
					'name' => $fullName,
				]
			);
		} catch (\SS6\ShopBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException $ex) {
			$this->getFlashMessageSender()->addErrorFlash(
				t('Tuto měnu nelze smazat, je nastavena jako výchozí nebo je uložena u objednávky')
			);
		} catch (\SS6\ShopBundle\Model\Pricing\Currency\Exception\CurrencyNotFoundException $ex) {
			$this->getFlashMessageSender()->addErrorFlash(t('Zvolená měna neexistuje.'));
		}

		return $this->redirectToRoute('admin_currency_list');
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function settingsAction(Request $request) {
		$currencies = $this->currencyFacade->getAll();
		$form = $this->createForm(new CurrencySettingsFormType($currencies));

		$domainNames = [];

		$currencySettingsFormData = [];
		$currencySettingsFormData['defaultCurrency'] = $this->currencyFacade->getDefaultCurrency();
		$currencySettingsFormData['domainDefaultCurrencies'] = [];

		foreach ($this->domain->getAll() as $domainConfig) {
			$domainId = $domainConfig->getId();
			$currencySettingsFormData['domainDefaultCurrencies'][$domainId] =
				$this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
			$domainNames[$domainId] = $domainConfig->getName();
		}

		$form->setData($currencySettingsFormData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$currencySettingsFormData = $form->getData();

			$this->transactional(
				function () use ($currencySettingsFormData) {
					$this->currencyFacade->setDefaultCurrency($currencySettingsFormData['defaultCurrency']);

					foreach ($this->domain->getAll() as $domainConfig) {
						$domainId = $domainConfig->getId();
						$this->currencyFacade->setDomainDefaultCurrency(
							$currencySettingsFormData['domainDefaultCurrencies'][$domainId],
							$domainId
						);
					}
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig(t('Nastavení měn bylo upraveno'));

			return $this->redirectToRoute('admin_currency_list');
		}

		return $this->render('@SS6Shop/Admin/Content/Currency/currencySettings.html.twig', [
			'form' => $form->createView(),
			'domainNames' => $domainNames,
		]);
	}

}
