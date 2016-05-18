<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Domain;

use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Domain\Config\DomainConfig;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Component\Domain\DomainDataCreator;
use SS6\ShopBundle\Component\Domain\Multidomain\MultidomainEntityDataCreator;
use SS6\ShopBundle\Component\Setting\Setting;
use SS6\ShopBundle\Component\Setting\SettingValueRepository;
use SS6\ShopBundle\Component\Translation\TranslatableEntityDataCreator;

class DomainDataCreatorTest extends PHPUnit_Framework_TestCase {

	public function testCreateNewDomainsDataNoNewDomain() {
		$domainConfigs = [
			new DomainConfig(1, 'http://example.com:8080', 'example', 'cs', 'design1', 'stylesDirectory'),
		];

		$settingMock = $this->getMock(Setting::class, [], [], '', false);
		$settingMock
			->expects($this->once())
			->method('get')
			->with($this->equalTo(Setting::DOMAIN_DATA_CREATED), $this->equalTo(1))
			->willReturn(true);

		$domain = new Domain($domainConfigs, $settingMock);

		$emMock = $this->getMock(EntityManager::class, [], [], '', false);

		$settingValueRepositoryMock = $this->getMock(SettingValueRepository::class, [], [], '', false);
		$multidomainEntityDataCreatorMock = $this->getMock(MultidomainEntityDataCreator::class, [], [], '', false);
		$translatableEntityDataCreatorMock = $this->getMock(TranslatableEntityDataCreator::class, [], [], '', false);

		$domainDataCreator = new DomainDataCreator(
			$domain,
			$settingMock,
			$settingValueRepositoryMock,
			$emMock,
			$multidomainEntityDataCreatorMock,
			$translatableEntityDataCreatorMock
		);
		$newDomainsDataCreated = $domainDataCreator->createNewDomainsData();

		$this->assertEquals(0, $newDomainsDataCreated);
	}

	public function testCreateNewDomainsDataOneNewDomain() {
		$domainConfigs = [
			new DomainConfig(1, 'http://example.com:8080', 'example', 'cs', 'design1', 'stylesDirectory'),
			new DomainConfig(2, 'http://example.com:8080', 'example', 'cs', 'design2', 'stylesDirectory'),
		];

		$settingMock = $this->getMock(Setting::class, [], [], '', false);
		$settingMock
			->method('get')
			->willReturnCallback(function ($key, $domainId) {
				$this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);
				if ($domainId === 1) {
					return true;
				}
				throw new \SS6\ShopBundle\Component\Setting\Exception\SettingValueNotFoundException();
			});

		$domain = new Domain($domainConfigs, $settingMock);

		$emMock = $this->getMock(EntityManager::class, [], [], '', false);

		$settingValueRepositoryMock = $this->getMock(SettingValueRepository::class, [], [], '', false);
		$settingValueRepositoryMock
			->expects($this->any())
			->method('copyAllMultidomainSettings')
			->with($this->equalTo(DomainDataCreator::TEMPLATE_DOMAIN_ID), $this->equalTo(2));

		$multidomainEntityDataCreatorMock = $this->getMock(MultidomainEntityDataCreator::class, [], [], '', false);
		$multidomainEntityDataCreatorMock
			->method('copyAllMultidomainDataForNewDomain')
			->with($this->equalTo(DomainDataCreator::TEMPLATE_DOMAIN_ID), $this->equalTo(2));

		$translatableEntityDataCreatorMock = $this->getMock(TranslatableEntityDataCreator::class, [], [], '', false);

		$domainDataCreator = new DomainDataCreator(
			$domain,
			$settingMock,
			$settingValueRepositoryMock,
			$emMock,
			$multidomainEntityDataCreatorMock,
			$translatableEntityDataCreatorMock
		);
		$newDomainsDataCreated = $domainDataCreator->createNewDomainsData();

		$this->assertEquals(1, $newDomainsDataCreated);
	}

	public function testCreateNewDomainsDataNewLocale() {
		$domainConfigWithDataCreated = new DomainConfig(1, 'http://example.com:8080', 'example', 'cs', 'design1', 'stylesDirectory');
		$domainConfigWithNewLocale = new DomainConfig(2, 'http://example.com:8080', 'example', 'en', 'design2', 'stylesDirectory');
		$domainConfigs = [
			$domainConfigWithDataCreated,
			$domainConfigWithNewLocale,
		];

		$settingMock = $this->getMock(Setting::class, [], [], '', false);
		$settingMock
			->method('get')
			->willReturnCallback(function ($key, $domainId) {
				$this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);
				if ($domainId === 1) {
					return true;
				}
				throw new \SS6\ShopBundle\Component\Setting\Exception\SettingValueNotFoundException();
			});

		$domainMock = $this->getMock(Domain::class, [], [], '', false);
		$domainMock
			->expects($this->any())
			->method('getAllIncludingDomainConfigsWithoutDataCreated')
			->willReturn($domainConfigs);
		$domainMock
			->expects($this->any())
			->method('getAll')
			->willReturn([$domainConfigWithDataCreated]);
		$domainMock
			->expects($this->any())
			->method('getDomainConfigById')
			->willReturn($domainConfigWithDataCreated);

		$emMock = $this->getMock(EntityManager::class, [], [], '', false);
		$settingValueRepositoryMock = $this->getMock(SettingValueRepository::class, [], [], '', false);
		$multidomainEntityDataCreatorMock = $this->getMock(MultidomainEntityDataCreator::class, [], [], '', false);
		$translatableEntityDataCreatorMock = $this->getMock(TranslatableEntityDataCreator::class, [], [], '', false);
		$translatableEntityDataCreatorMock
			->expects($this->any())
			->method('copyAllTranslatableDataForNewLocale')
			->with($domainConfigWithDataCreated->getLocale(), $domainConfigWithNewLocale->getLocale());

		$domainDataCreator = new DomainDataCreator(
			$domainMock,
			$settingMock,
			$settingValueRepositoryMock,
			$emMock,
			$multidomainEntityDataCreatorMock,
			$translatableEntityDataCreatorMock
		);

		$domainDataCreator->createNewDomainsData();
	}

}
