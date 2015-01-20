<?php

namespace SS6\ShopBundle\Tests\Model\Security;

use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Customer\User;
use SS6\ShopBundle\Model\Order\OrderFlowFacade;
use SS6\ShopBundle\Model\Security\LoginListener;
use SS6\ShopBundle\Model\Security\TimelimitLoginInterface;
use SS6\ShopBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListenerTest extends PHPUnit_Framework_TestCase {

	public function testOnSecurityInteractiveLoginUnique() {
		$emMock = $this->getMockBuilder(EntityManager::class)
			->setMethods(['__construct', 'persist', 'flush'])
			->disableOriginalConstructor()
			->getMock();
		$emMock->expects($this->once())->method('flush');

		$userMock = $this->getMock(UniqueLoginInterface::class);
		$userMock->expects($this->once())->method('setLoginToken');

		$tokenMock = $this->getMock(TokenInterface::class);
		$tokenMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));

		$eventMock = $this->getMockBuilder(InteractiveLoginEvent::class)
			->setMethods(['__construct', 'getAuthenticationToken'])
			->disableOriginalConstructor()
			->getMock();
		$eventMock->expects($this->once())->method('getAuthenticationToken')->will($this->returnValue($tokenMock));

		$orderFlowFacadeMock = $this->getMockBuilder(OrderFlowFacade::class)
			->setMethods(['__construct'])
			->disableOriginalConstructor()
			->getMock();

		$loginListener = new LoginListener($emMock, $orderFlowFacadeMock);
		$loginListener->onSecurityInteractiveLogin($eventMock);
	}

	public function testOnSecurityInteractiveLoginTimelimit() {
		$emMock = $this->getMockBuilder(EntityManager::class)
			->setMethods(['__construct', 'persist', 'flush'])
			->disableOriginalConstructor()
			->getMock();
		$emMock->expects($this->any())->method('flush');

		$userMock = $this->getMock(TimelimitLoginInterface::class);
		$userMock->expects($this->once())->method('setLastActivity');

		$tokenMock = $this->getMock(TokenInterface::class);
		$tokenMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));

		$eventMock = $this->getMockBuilder(InteractiveLoginEvent::class)
			->setMethods(['__construct', 'getAuthenticationToken'])
			->disableOriginalConstructor()
			->getMock();
		$eventMock->expects($this->once())->method('getAuthenticationToken')->will($this->returnValue($tokenMock));

		$orderFlowFacadeMock = $this->getMockBuilder(OrderFlowFacade::class)
			->setMethods(['__construct'])
			->disableOriginalConstructor()
			->getMock();

		$loginListener = new LoginListener($emMock, $orderFlowFacadeMock);
		$loginListener->onSecurityInteractiveLogin($eventMock);
	}

	public function testOnSecurityInteractiveLoginResetOrderForm() {
		$emMock = $this->getMockBuilder(EntityManager::class)
			->setMethods(['__construct', 'persist', 'flush'])
			->disableOriginalConstructor()
			->getMock();
		$emMock->expects($this->any())->method('flush');

		$userMock = $this->getMockBuilder(User::class)
			->setMethods(['__construct'])
			->disableOriginalConstructor()
			->getMock();

		$tokenMock = $this->getMock(TokenInterface::class);
		$tokenMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));

		$eventMock = $this->getMockBuilder(InteractiveLoginEvent::class)
			->setMethods(['__construct', 'getAuthenticationToken'])
			->disableOriginalConstructor()
			->getMock();
		$eventMock->expects($this->once())->method('getAuthenticationToken')->will($this->returnValue($tokenMock));

		$orderFlowFacadeMock = $this->getMockBuilder(OrderFlowFacade::class)
			->setMethods(['__construct', 'resetOrderForm'])
			->disableOriginalConstructor()
			->getMock();
		$orderFlowFacadeMock->expects($this->once())->method('resetOrderForm');

		$loginListener = new LoginListener($emMock, $orderFlowFacadeMock);
		$loginListener->onSecurityInteractiveLogin($eventMock);
	}
}
