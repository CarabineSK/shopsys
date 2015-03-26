<?php

namespace SS6\ShopBundle\Tests\Model\Form;

use SS6\ShopBundle\Component\Router\DomainRouterFactory;
use SS6\ShopBundle\Component\Test\FunctionalTestCase;
use SS6\ShopBundle\Model\Domain\Config\DomainConfig;
use SS6\ShopBundle\Model\Domain\Domain;
use SS6\ShopBundle\Model\Mail\MailTemplate;
use SS6\ShopBundle\Model\Mail\MailTemplateData;
use SS6\ShopBundle\Model\Mail\MessageData;
use SS6\ShopBundle\Model\Order\Item\OrderItemPriceCalculation;
use SS6\ShopBundle\Model\Order\Mail\OrderMailService;
use SS6\ShopBundle\Model\Order\Status\OrderStatus;
use SS6\ShopBundle\Model\Setting\Setting;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;

class OrderMailServiceTest extends FunctionalTestCase {

	public function testGetMailTemplateNameByStatus() {
		$routerMock = $this->getMockBuilder(RouterInterface::class)->setMethods(['generate'])->getMockForAbstractClass();
		$routerMock->expects($this->any())->method('generate')->willReturn('generatedUrl');

		$domainRouterFactoryMock = $this->getMock(DomainRouterFactory::class, ['getRouter'], [], '', false);
		$domainRouterFactoryMock->expects($this->any())->method('getRouter')->willReturn($routerMock);

		$twigMock = $this->getMockBuilder(Twig_Environment::class)
			->disableOriginalConstructor()
			->getMock();
		$orderItemPriceCalculationMock = $this->getMockBuilder(OrderItemPriceCalculation::class)
			->disableOriginalConstructor()
			->getMock();
		$settingMock = $this->getMockBuilder(Setting::class)
			->disableOriginalConstructor()
			->getMock();

		$domainMock = $this->getMockBuilder(Domain::class)
			->disableOriginalConstructor()
			->getMock();

		$orderMailService = new OrderMailService(
			$settingMock,
			$domainRouterFactoryMock,
			$twigMock,
			$orderItemPriceCalculationMock,
			$domainMock
		);

		$orderStatus1 = $this->getMock(OrderStatus::class, ['getId'], [], '', false);
		$orderStatus1->expects($this->atLeastOnce())->method('getId')->willReturn(1);

		$orderStatus2 = $this->getMock(OrderStatus::class, ['getId'], [], '', false);
		$orderStatus2->expects($this->atLeastOnce())->method('getId')->willReturn(2);

		$mailTempleteName1 = $orderMailService->getMailTemplateNameByStatus($orderStatus1);
		$mailTempleteName2 = $orderMailService->getMailTemplateNameByStatus($orderStatus2);

		$this->assertNotEmpty($mailTempleteName1);
		$this->assertInternalType('string', $mailTempleteName1);

		$this->assertNotEmpty($mailTempleteName2);
		$this->assertInternalType('string', $mailTempleteName2);

		$this->assertNotSame($mailTempleteName1, $mailTempleteName2);
	}

	public function testGetMessageByOrder() {
		$routerMock = $this->getMockBuilder(RouterInterface::class)->setMethods(['generate'])->getMockForAbstractClass();
		$routerMock->expects($this->any())->method('generate')->willReturn('generatedUrl');

		$domainRouterFactoryMock = $this->getMock(DomainRouterFactory::class, ['getRouter'], [], '', false);
		$domainRouterFactoryMock->expects($this->any())->method('getRouter')->willReturn($routerMock);

		$twigMock = $this->getMockBuilder(Twig_Environment::class)
			->disableOriginalConstructor()
			->getMock();
		$orderItemPriceCalculationMock = $this->getMockBuilder(OrderItemPriceCalculation::class)
			->disableOriginalConstructor()
			->getMock();
		$settingMock = $this->getMockBuilder(Setting::class)
			->disableOriginalConstructor()
			->getMock();

		$domainConfig = new DomainConfig(1, 'http://example.com:8080', 'example', 'cs', '');
		$domain = new Domain([$domainConfig]);

		$orderMailService = new OrderMailService(
			$settingMock,
			$domainRouterFactoryMock,
			$twigMock,
			$orderItemPriceCalculationMock,
			$domain
		);

		$order = $this->getReference('order_1');

		$mailTemplateData = new MailTemplateData();
		$mailTemplateData->subject = 'subject';
		$mailTemplateData->body = 'body';
		$mailTemplate = new MailTemplate('templateName', 1, $mailTemplateData);

		$messageData = $orderMailService->getMessageDataByOrder($order, $mailTemplate);

		$this->assertInstanceOf(MessageData::class, $messageData);
		$this->assertSame($mailTemplate->getSubject(), $messageData->subject);
		$this->assertSame($mailTemplate->getBody(), $messageData->body);
	}

}
