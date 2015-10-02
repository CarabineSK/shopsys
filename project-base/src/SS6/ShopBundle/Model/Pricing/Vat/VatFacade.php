<?php

namespace SS6\ShopBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Component\Setting\Setting;
use SS6\ShopBundle\Component\Setting\SettingValue;
use SS6\ShopBundle\Model\Payment\PaymentEditFacade;
use SS6\ShopBundle\Model\Pricing\Vat\VatData;
use SS6\ShopBundle\Model\Pricing\Vat\VatRepository;
use SS6\ShopBundle\Model\Pricing\Vat\VatService;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use SS6\ShopBundle\Model\Product\ProductEditFacade;
use SS6\ShopBundle\Model\Transport\TransportEditFacade;

class VatFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Vat\VatRepository
	 */
	private $vatRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Vat\VatService
	 */
	private $vatService;

	/**
	 * @var \SS6\ShopBundle\Component\Setting\Setting
	 */
	private $setting;

	/**
	 * @var \SS6\ShopBundle\Model\Payment\PaymentEditFacade
	 */
	private $paymentEditFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductEditFacade
	 */
	private $productEditFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Transport\TransportEditFacade
	 */
	private $trasnportEditFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
	 */
	private $productPriceRecalculationScheduler;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\VatRepository $vatRepository
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\VatService $vatService
	 * @param \SS6\ShopBundle\Component\Setting\Setting $setting
	 * @param \SS6\ShopBundle\Model\Payment\PaymentEditFacade $paymentEditFacade
	 * @param \SS6\ShopBundle\Model\Product\ProductEditFacade $productEditFacade
	 * @param \SS6\ShopBundle\Model\Transport\TransportEditFacade $trasnportEditFacade
	 */
	public function __construct(
		EntityManager $em,
		VatRepository $vatRepository,
		VatService $vatService,
		Setting $setting,
		PaymentEditFacade $paymentEditFacade,
		ProductEditFacade $productEditFacade,
		TransportEditFacade $trasnportEditFacade,
		ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
	) {
		$this->em = $em;
		$this->vatRepository = $vatRepository;
		$this->vatService = $vatService;
		$this->setting = $setting;
		$this->paymentEditFacade = $paymentEditFacade;
		$this->productEditFacade = $productEditFacade;
		$this->trasnportEditFacade = $trasnportEditFacade;
		$this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
	}

	/**
	 * @param int $vatId
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat
	 */
	public function getById($vatId) {
		return $this->vatRepository->getById($vatId);
	}

	/**
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	public function getAll() {
		return $this->vatRepository->getAll();
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\VatData $vatData
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat
	 */
	public function create(VatData $vatData) {
		$vat = $this->vatService->create($vatData);
		$this->em->persist($vat);
		$this->em->flush();

		return $vat;
	}

	/**
	 * @param int $vatId
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\VatData $vatData
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat
	 */
	public function edit($vatId, VatData $vatData) {
		$vat = $this->vatRepository->getById($vatId);
		$this->vatService->edit($vat, $vatData);
		$this->em->flush();

		$this->productPriceRecalculationScheduler->scheduleRecalculatePriceForAllProducts();

		return $vat;
	}

	/**
	 * @param int $vatId
	 * @param int|null $newVatId
	 */
	public function deleteById($vatId, $newVatId) {
		$oldVat = $this->vatRepository->getById($vatId);
		$newVat = $newVatId ? $this->vatRepository->getById($newVatId) : null;

		if ($oldVat->isMarkedAsDeleted()) {
			throw new \SS6\ShopBundle\Model\Pricing\Vat\Exception\VatMarkedAsDeletedDeleteException();
		}

		if ($this->vatRepository->existsVatToBeReplacedWith($oldVat)) {
			throw new \SS6\ShopBundle\Model\Pricing\Vat\Exception\VatWithReplacedDeleteException();
		}

		$this->em->beginTransaction();

		if ($newVat !== null) {
			$newDefaultVat = $this->vatService->getNewDefaultVat(
				$this->getDefaultVat(),
				$oldVat,
				$newVat
			);
			$this->setDefaultVat($newDefaultVat);

			$this->paymentEditFacade->replaceOldVatWithNewVat($oldVat, $newVat);
			$this->trasnportEditFacade->replaceOldVatWithNewVat($oldVat, $newVat);
			$oldVat->markForDeletion($newVat);
		} else {
			$this->em->remove($oldVat);
		}

		$this->em->flush();
		$this->em->commit();
	}

	/**
	 * @return int
	 */
	public function deleteAllReplacedVats() {
		$vatsForDelete = $this->vatRepository->getVatsWithoutProductsMarkedForDeletion();
		foreach ($vatsForDelete as $vatForDelete) {
			$this->em->remove($vatForDelete);
		}
		$this->em->flush($vatsForDelete);

		return count($vatsForDelete);
	}

	/**
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat
	 */
	public function getDefaultVat() {
		$defaultVatId = $this->setting->get(Vat::SETTING_DEFAULT_VAT, SettingValue::DOMAIN_ID_COMMON);

		return $this->vatRepository->getById($defaultVatId);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat $vat
	 */
	public function setDefaultVat(Vat $vat) {
		$this->setting->set(Vat::SETTING_DEFAULT_VAT, $vat->getId(), SettingValue::DOMAIN_ID_COMMON);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Vat\Vat $vat
	 * @return bool
	 */
	public function isVatUsed(Vat $vat) {
		$defaultVat = $this->getDefaultVat();

		return $defaultVat === $vat || $this->vatRepository->isVatUsed($vat);
	}

	/**
	 * @param int $vatId
	 * @return \SS6\ShopBundle\Model\Pricing\Vat\Vat[]
	 */
	public function getAllExceptId($vatId) {
		return $this->vatRepository->getAllExceptId($vatId);
	}

}
