<?php

namespace SS6\ShopBundle\Model\Localization\Translation\Grid;

use SS6\ShopBundle\Form\Admin\Localization\TranslationFormType;
use SS6\ShopBundle\Model\Grid\InlineEdit\AbstractGridInlineEdit;
use SS6\ShopBundle\Model\Localization\Translation\Grid\TranslationGridFactory;
use SS6\ShopBundle\Model\Localization\Translation\TranslationEditFacade;
use Symfony\Component\Form\FormFactory;

class TranslationInlineEdit extends AbstractGridInlineEdit {

	/**
	 * @var \SS6\ShopBundle\Model\Localization\Translation\TranslationEditFacade
	 */
	private $translationEditFacade;

	public function __construct(
		FormFactory $formFactory,
		TranslationGridFactory $translationGridFactory,
		TranslationEditFacade $translationEditFacade
	) {
		$this->translationEditFacade = $translationEditFacade;

		parent::__construct($formFactory, $translationGridFactory);
	}

	/**
	 * @param array $translationData
	 */
	protected function createEntityAndGetId($translationData) {
		$message = 'Method "createEntityAndGetId" is not supported in translations.';
		throw new \SS6\ShopBundle\Model\Localization\Grid\Exception\NotImplementedException($message);
	}

	/**
	 * @param string $translationId
	 * @param array $translationData
	 */
	protected function editEntity($translationId, $translationData) {
		$this->translationEditFacade->saveTranslation($translationId, $translationData);
	}

	/**
	 * @param string $translationId
	 * @return array
	 */
	protected function getFormDataObject($translationId = null) {
		if ($translationId === null) {
			$message = 'Method "getFormDataObject" for new translation is not supported in translations.';
			throw new \SS6\ShopBundle\Model\Localization\Grid\Exception\NotImplementedException($message);
		}

		return $this->translationEditFacade->getTranslationById($translationId);
	}

	/**
	 * @param int $rowId
	 * @return \SS6\ShopBundle\Form\Admin\Localization\TranslationFormType
	 */
	protected function getFormType($rowId) {
		return new TranslationFormType($this->translationEditFacade->getTranslatableLocales());
	}

	/**
	 * @return string
	 */
	public function getServiceName() {
		return 'ss6.shop.localization.translation.grid.translation_inline_edit';
	}

	/**
	 * @return bool
	 */
	public function canAddNewRow() {
		return false;
	}

}
