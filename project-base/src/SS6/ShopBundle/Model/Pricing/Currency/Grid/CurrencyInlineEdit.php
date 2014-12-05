<?php

namespace SS6\ShopBundle\Model\Pricing\Currency\Grid;

use SS6\ShopBundle\Form\Admin\Pricing\Currency\CurrencyFormType;
use SS6\ShopBundle\Model\Grid\InlineEdit\AbstractGridInlineEdit;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyData;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\FormFactory;

class CurrencyInlineEdit extends AbstractGridInlineEdit {

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade
	 */
	private $currencyFacade;

	/**
	 * @param \Symfony\Component\Form\FormFactory $formFactory
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\Grid\CurrencyGridFactory $currencyGridFactory
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
	 */
	public function __construct(
		FormFactory $formFactory,
		CurrencyGridFactory $currencyGridFactory,
		CurrencyFacade $currencyFacade
	) {
		$this->currencyFacade = $currencyFacade;

		parent::__construct($formFactory, $currencyGridFactory);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\currencyData $currencyData
	 * @return int
	 */
	protected function createEntityAndGetId($currencyData) {
		$currency = $this->currencyFacade->create($currencyData);

		return $currency->getId();
	}

	/**
	 * @param int $currencyId
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\CurrencyData $currencyData
	 */
	protected function editEntity($currencyId, $currencyData) {
		$this->currencyFacade->edit($currencyId, $currencyData);
	}

	/**
	 * @param int $currencyId
	 * @return \SS6\ShopBundle\Model\Pricing\Currency\CurrencyData
	 */
	protected function getFormDataObject($currencyId = null) {
		$currencyData = new CurrencyData();

		if ($currencyId !== null) {
			$currencyId = (int)$currencyId;
			$currency = $this->currencyFacade->getById($currencyId);
			$currencyData->setFromEntity($currency);
		}

		return $currencyData;
	}

	/**
	 * @return \SS6\ShopBundle\Form\Admin\Pricing\Currency\CurrencyFormType
	 */
	protected function getFormType() {
		return new CurrencyFormType();
	}

	/**
	 * @return string
	 */
	public function getServiceName() {
		return 'ss6.shop.pricing.currency.currency_inline_edit';
	}

}
