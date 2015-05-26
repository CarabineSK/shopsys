<?php

namespace SS6\ShopBundle\Model\Module;

use Doctrine\ORM\EntityManager;

class ModuleFacade {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Module\EnabledModuleRepository
	 */
	private $enabledModuleRepository;

	public function __construct(
		EntityManager $em,
		EnabledModuleRepository $enabledModuleRepository
	) {
		$this->em = $em;
		$this->enabledModuleRepository = $enabledModuleRepository;
	}

	/**
	 * @param string $moduleName
	 * @return boolean
	 */
	public function isEnabled($moduleName) {
		$enabledModule = $this->enabledModuleRepository->findByName($moduleName);

		return $enabledModule !== null;
	}

	/**
	 * @param string $moduleName
	 * @param boolean $isEnabled
	 */
	public function setEnabled($moduleName, $isEnabled) {
		$enabledModule = $this->enabledModuleRepository->findByName($moduleName);

		if ($enabledModule === null && $isEnabled) {
			$enabledModule = new EnabledModule($moduleName);
			$this->em->persist($enabledModule);
		} elseif ($enabledModule !== null && !$isEnabled) {
			$this->em->remove($enabledModule);
		}

		$this->em->flush();
	}

}
