<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Router;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Router\CurrentDomainRouter;
use SS6\ShopBundle\Component\Router\DomainRouterFactory;
use SS6\ShopBundle\Model\Domain\Config\DomainConfig;
use SS6\ShopBundle\Model\Domain\Domain;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class CurrentDomainRouterTest extends PHPUnit_Framework_TestCase {

	public function testDelegateRouter() {
		$domainConfigs = new DomainConfig(1, 'http://example.com:8080', 'example', 'en', 'en');
		$domain = new Domain([$domainConfigs]);
		$domain->switchDomainById(1);

		$generateResult = 'generateResult';
		$pathInfo = 'pathInfo';
		$matchResult = 'matchResult';
		$getRouteCollectionResult = 'getRouteCollectionResult';
		$routerMock = $this->getMockBuilder(Router::class)
			->setMethods(['__construct', 'generate', 'match', 'getRouteCollection'])
			->disableOriginalConstructor()
			->getMock();
		$routerMock->expects($this->once())->method('generate')->willReturn($generateResult);
		$routerMock->expects($this->once())->method('match')->with($this->equalTo($pathInfo))->willReturn($matchResult);
		$routerMock->expects($this->once())->method('getRouteCollection')->willReturn($getRouteCollectionResult);

		$domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
			->setMethods(['__construct', 'getRouter'])
			->disableOriginalConstructor()
			->getMock();
		$domainRouterFactoryMock->expects($this->exactly(3))->method('getRouter')->willReturn($routerMock);

		$currentDomainRouter = new CurrentDomainRouter($domain, $domainRouterFactoryMock);

		$this->assertSame($generateResult, $currentDomainRouter->generate(''));
		$this->assertSame($matchResult, $currentDomainRouter->match($pathInfo));
		$this->assertSame($getRouteCollectionResult, $currentDomainRouter->getRouteCollection());
	}

}
