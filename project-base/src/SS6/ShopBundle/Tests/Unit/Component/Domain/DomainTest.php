<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Domain;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Domain\Config\DomainConfig;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Component\Setting\Setting;
use Symfony\Component\HttpFoundation\Request;

class DomainTest extends PHPUnit_Framework_TestCase {

	public function testGetIdNotSet() {
		$domainConfigs = [
			new DomainConfig(1, 'http://example.com:8080', 'example', 'cs', 'design1', 'stylesDirectory'),
			new DomainConfig(2, 'http://example.org:8080', 'example.org', 'en', 'design2', 'stylesDirectory'),
		];
		$settingMock = $this->getMock(Setting::class, [], [], '', false);

		$domain = new Domain($domainConfigs, $settingMock);
		$this->setExpectedException(\SS6\ShopBundle\Component\Domain\Exception\NoDomainSelectedException::class);
		$domain->getId();
	}

	public function testSwitchDomainByRequest() {
		$domainConfigs = [
			new DomainConfig(1, 'http://example.com:8080', 'example.com', 'cs', 'design1', 'stylesDirectory'),
			new DomainConfig(2, 'http://example.org:8080', 'example.org', 'en', 'design2', 'stylesDirectory'),
		];
		$settingMock = $this->getMock(Setting::class, [], [], '', false);

		$domain = new Domain($domainConfigs, $settingMock);

		$requestMock = $this->getMockBuilder(Request::class)
			->setMethods(['getSchemeAndHttpHost'])
			->getMock();
		$requestMock
			->expects($this->atLeastOnce())
			->method('getSchemeAndHttpHost')
			->will($this->returnValue('http://example.com:8080'));

		$domain->switchDomainByRequest($requestMock);
		$this->assertSame(1, $domain->getId());
		$this->assertSame('example.com', $domain->getName());
		$this->assertSame('cs', $domain->getLocale());
		$this->assertSame('design1', $domain->getTemplatesDirectory());
	}

	public function testGetAllIncludingDomainConfigsWithoutDataCreated() {
		$domainConfigs = [
			new DomainConfig(1, 'http://example.com:8080', 'example.com', 'cs', 'design1', 'stylesDirectory'),
			new DomainConfig(2, 'http://example.org:8080', 'example.org', 'en', 'design2', 'stylesDirectory'),
		];
		$settingMock = $this->getMock(Setting::class, [], [], '', false);

		$domain = new Domain($domainConfigs, $settingMock);

		$this->assertSame($domainConfigs, $domain->getAllIncludingDomainConfigsWithoutDataCreated());
	}

	public function testGetAll() {
		$domainConfigWithDataCreated = new DomainConfig(
			1,
			'http://example.com:8080',
			'example.com',
			'cs',
			'design1',
			'stylesDirectory'
		);
		$domainConfigWithoutDataCreated = new DomainConfig(
			2,
			'http://example.org:8080',
			'example.org',
			'en',
			'design2',
			'stylesDirectory'
		);
		$domainConfigs = [
			$domainConfigWithDataCreated,
			$domainConfigWithoutDataCreated,
		];
		$settingMock = $this->getMock(Setting::class, [], [], '', false);
		$settingMock
			->expects($this->exactly(count($domainConfigs)))
			->method('get')
			->willReturnCallback(function ($key, $domainId) use ($domainConfigWithDataCreated) {
				$this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);
				if ($domainId === $domainConfigWithDataCreated->getId()) {
					return true;
				}
				throw new \SS6\ShopBundle\Component\Setting\Exception\SettingValueNotFoundException();
			});

		$domain = new Domain($domainConfigs, $settingMock);

		$this->assertSame([$domainConfigWithDataCreated], $domain->getAll());
	}

	public function testGetDomainConfigById() {
		$domainConfigs = [
			new DomainConfig(1, 'http://example.com:8080', 'example.com', 'cs', 'design1', 'stylesDirectory'),
			new DomainConfig(2, 'http://example.org:8080', 'example.org', 'en', 'design2', 'stylesDirectory'),
		];
		$settingMock = $this->getMock(Setting::class, [], [], '', false);

		$domain = new Domain($domainConfigs, $settingMock);

		$this->assertSame($domainConfigs[0], $domain->getDomainConfigById(1));
		$this->assertSame($domainConfigs[1], $domain->getDomainConfigById(2));

		$this->setExpectedException(\SS6\ShopBundle\Component\Domain\Exception\InvalidDomainIdException::class);
		$domain->getDomainConfigById(3);
	}

}
