<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Cron;

use DateTime;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Cron\Config\CronConfig;
use SS6\ShopBundle\Component\Cron\Config\CronModuleConfig;
use SS6\ShopBundle\Component\Cron\CronFacade;
use SS6\ShopBundle\Component\Cron\CronModuleExecutorFactory;
use SS6\ShopBundle\Component\Cron\CronModuleFacade;
use SS6\ShopBundle\Component\Cron\CronModuleInterface;
use SS6\ShopBundle\Component\Cron\CronTimeResolver;
use SS6\ShopBundle\Component\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class CronFacadeTest extends PHPUnit_Framework_TestCase {

	public function testRunModuleByModuleId() {
		$moduleId = 'moduleId';
		$cronModuleServiceMock = $this->getMockForAbstractClass(CronModuleInterface::class);
		$cronModuleServiceMock->expects($this->once())->method('run');
		$cronModuleConfig = new CronModuleConfig($cronModuleServiceMock, $moduleId, '', '');
		$cronTimeResolver = new CronTimeResolver();
		$cronConfig = new CronConfig($cronTimeResolver, [$cronModuleConfig]);
		$loggerMock = $this->getMock(Logger::class, [], [], '', false);
		$cronModuleFacadeMock = $this->getMock(CronModuleFacade::class, [], [], '', false);
		$cronModuleFacadeMock->expects($this->atLeastOnce())->method('unscheduleModule')->with($this->equalTo($moduleId));
		$cronModuleExecutorFactory = new CronModuleExecutorFactory();

		$cronFacade = new CronFacade($loggerMock, $cronConfig, $cronModuleFacadeMock, $cronModuleExecutorFactory);
		$cronFacade->runModuleByModuleId($moduleId);
	}

	public function testRunIterableModuleByModuleId() {
		$moduleId = 'moduleId';
		$cronModuleServiceMock = $this->getMockForAbstractClass(IteratedCronModuleInterface::class);
		$cronModuleServiceMock->expects($this->once())->method('setLogger');
		$iterations = 3;
		$cronModuleServiceMock->expects($this->exactly($iterations))->method('iterate')->willReturnCallback(
			function () use (&$iterations) {
				$iterations--;
				return $iterations > 0;
			}
		);
		$cronModuleConfig = new CronModuleConfig($cronModuleServiceMock, $moduleId, '', '');
		$cronTimeResolver = new CronTimeResolver();
		$cronConfig = new CronConfig($cronTimeResolver, [$cronModuleConfig]);
		$loggerMock = $this->getMock(Logger::class, [], [], '', false);
		$cronModuleFacadeMock = $this->getMock(CronModuleFacade::class, [], [], '', false);
		$cronModuleFacadeMock->expects($this->atLeastOnce())->method('unscheduleModule')->with($this->equalTo($moduleId));
		$cronModuleExecutorFactory = new CronModuleExecutorFactory();

		$cronFacade = new CronFacade($loggerMock, $cronConfig, $cronModuleFacadeMock, $cronModuleExecutorFactory);
		$cronFacade->runModuleByModuleId($moduleId);
	}

	public function testScheduleModulesByTime() {
		$scheduledCronModuleServiceMock = $this->getMockForAbstractClass(CronModuleInterface::class);
		$scheduledCronModuleConfig = new CronModuleConfig($scheduledCronModuleServiceMock, 'scheduled', '', '');

		$skippedCronModuleServiceMock = $this->getMockForAbstractClass(CronModuleInterface::class);
		$skippedCronModuleConfig = new CronModuleConfig($skippedCronModuleServiceMock, 'skipped', '', '');

		$cronTimeResolverMock = $this->getMock(CronTimeResolver::class);
		$cronTimeResolverMock->method('isValidAtTime')->willReturnCallback(
			function (CronModuleConfig $cronModuleConfig) use ($scheduledCronModuleConfig) {
				return $cronModuleConfig === $scheduledCronModuleConfig;
			}
		);

		$cronModuleConfigs = [$scheduledCronModuleConfig, $skippedCronModuleConfig];
		$cronConfig = new CronConfig($cronTimeResolverMock, $cronModuleConfigs);

		$loggerMock = $this->getMock(Logger::class, [], [], '', false);

		$cronModuleFacadeMock = $this->getMock(CronModuleFacade::class, [], [], '', false);
		$cronModuleFacadeMock
			->method('scheduleModules')
			->with($this->equalTo([$scheduledCronModuleConfig]));

		$cronModuleExecutorFactory = new CronModuleExecutorFactory();

		$cronFacade = new CronFacade($loggerMock, $cronConfig, $cronModuleFacadeMock, $cronModuleExecutorFactory);
		$cronFacade->scheduleModulesByTime(new DateTime());
	}

	public function testRunScheduledModules() {
		$scheduledCronModuleServiceMock = $this->getMockForAbstractClass(CronModuleInterface::class);
		$scheduledCronModuleServiceMock->expects($this->once())->method('run');
		$scheduledCronModuleConfig = new CronModuleConfig($scheduledCronModuleServiceMock, 'scheduled', '', '');

		$skippedCronModuleServiceMock = $this->getMockForAbstractClass(CronModuleInterface::class);
		$skippedCronModuleServiceMock->expects($this->never())->method('run');
		$skippedCronModuleConfig = new CronModuleConfig($skippedCronModuleServiceMock, 'skipped', '', '');

		$cronTimeResolverMock = $this->getMock(CronTimeResolver::class);
		$cronModuleConfigs = [$scheduledCronModuleConfig, $skippedCronModuleConfig];
		$cronConfig = new CronConfig($cronTimeResolverMock, $cronModuleConfigs);

		$loggerMock = $this->getMock(Logger::class, [], [], '', false);

		$cronModuleFacadeMock = $this->getMock(CronModuleFacade::class, [], [], '', false);
		$cronModuleFacadeMock
			->method('getOnlyScheduledCronModuleConfigs')
			->willReturn([$scheduledCronModuleConfig]);
		$cronModuleFacadeMock
			->expects($this->atLeastOnce())
			->method('unscheduleModule')
			->with($this->equalTo('scheduled'));

		$cronModuleExecutorFactory = new CronModuleExecutorFactory();

		$cronFacade = new CronFacade($loggerMock, $cronConfig, $cronModuleFacadeMock, $cronModuleExecutorFactory);
		$cronFacade->runScheduledModules();
	}

}
