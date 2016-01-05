<?php

namespace SS6\ShopBundle\Component\Cron;

use DateTimeInterface;
use SS6\ShopBundle\Component\Cron\Config\CronConfig;
use SS6\ShopBundle\Component\Cron\Config\CronModuleConfig;
use SS6\ShopBundle\Component\Cron\CronModuleExecutor;
use SS6\ShopBundle\Component\Cron\CronModuleExecutorFactory;
use SS6\ShopBundle\Component\Cron\CronModuleFacade;
use Symfony\Bridge\Monolog\Logger;

class CronFacade {

	const TIMEOUT_SECONDS = 4 * 60;

	/**
	 * @var \Symfony\Bridge\Monolog\Logger
	 */
	private $logger;

	/**
	 * @var \SS6\ShopBundle\Component\Cron\Config\CronConfig
	 */
	private $cronConfig;

	/**
	 * @var \SS6\ShopBundle\Component\Cron\CronModuleFacade
	 */
	private $cronModuleFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Cron\CronModuleExecutorFactory
	 */
	private $cronModuleExecutorFactory;

	public function __construct(
		Logger $logger,
		CronConfig $cronConfig,
		CronModuleFacade $cronModuleFacade,
		CronModuleExecutorFactory $cronModuleExecutorFactory
	) {
		$this->logger = $logger;
		$this->cronConfig = $cronConfig;
		$this->cronModuleFacade = $cronModuleFacade;
		$this->cronModuleExecutorFactory = $cronModuleExecutorFactory;
	}

	/**
	 * @param \DateTimeInterface $roundedTime
	 */
	public function scheduleModulesByTime(DateTimeInterface $roundedTime) {
		$cronModulesConfigsToSchedule = $this->cronConfig->getCronModuleConfigsByTime($roundedTime);
		$this->cronModuleFacade->scheduleModules($cronModulesConfigsToSchedule);
	}

	public function runScheduledModules() {
		$cronModuleExecutor = $this->cronModuleExecutorFactory->create(self::TIMEOUT_SECONDS);

		$cronModuleConfigs = $this->cronConfig->getAll();
		$scheduledCronModuleConfigs = $this->cronModuleFacade->getOnlyScheduledCronModuleConfigs($cronModuleConfigs);
		$this->runModules($cronModuleExecutor, $scheduledCronModuleConfigs);
	}

	/**
	 * @param \SS6\ShopBundle\Component\Cron\CronModuleExecutor $cronModuleExecutor
	 * @param \SS6\ShopBundle\Component\Cron\Config\CronModuleConfig[] $cronModuleConfigs
	 */
	private function runModules(CronModuleExecutor $cronModuleExecutor, array $cronModuleConfigs) {
		$this->logger->addInfo('====== Start of cron ======');

		foreach ($cronModuleConfigs as $cronModuleConfig) {
			$this->runModule($cronModuleExecutor, $cronModuleConfig);
			if ($cronModuleExecutor->canRun() === false) {
				break;
			}
		}

		$this->logger->addInfo('======= End of cron =======');
	}

	/**
	 * @param string $moduleId
	 */
	public function runModuleByModuleId($moduleId) {
		$cronModuleConfig = $this->cronConfig->getCronModuleConfigByModuleId($moduleId);

		$cronModuleExecutor = $this->cronModuleExecutorFactory->create(self::TIMEOUT_SECONDS);
		$this->runModule($cronModuleExecutor, $cronModuleConfig);
	}

	/**
	 * @param \SS6\ShopBundle\Component\Cron\CronModuleExecutor $cronModuleExecutor
	 * @param \SS6\ShopBundle\Component\Cron\Config\CronModuleConfig $cronModuleConfig
	 */
	private function runModule(CronModuleExecutor $cronModuleExecutor, CronModuleConfig $cronModuleConfig) {
		$this->logger->addInfo('Start of ' . $cronModuleConfig->getModuleId());
		$status = $cronModuleExecutor->runModule($this->logger, $cronModuleConfig);

		if ($status === CronModuleExecutor::RUN_STATUS_OK) {
			$this->cronModuleFacade->unscheduleModule($cronModuleConfig->getModuleId());
			$this->logger->addInfo('End of ' . $cronModuleConfig->getModuleId());
		} elseif ($status === CronModuleExecutor::RUN_STATUS_SUSPENDED) {
			$this->cronModuleFacade->suspendModule($cronModuleConfig->getModuleId());
			$this->logger->addInfo('Suspend' . $cronModuleConfig->getModuleId());
		} elseif ($status === CronModuleExecutor::RUN_STATUS_TIMEOUT) {
			$this->logger->info('Cron reached timeout.');
		}
	}

}
