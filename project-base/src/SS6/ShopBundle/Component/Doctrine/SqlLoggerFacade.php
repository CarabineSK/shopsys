<?php

namespace SS6\ShopBundle\Component\Doctrine;

use Doctrine\ORM\EntityManager;

class SqlLoggerFacade {

	/**
	 * @var \Doctrine\DBAL\Logging\SQLLogger|null
	 */
	private $sqlLogger;

	/**
	 * @var bool
	 */
	private $isLoggerTemporarilyDisabled;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	public function temporarilyDisableLogging() {
		if ($this->isLoggerTemporarilyDisabled) {
			$message = 'Trying to disable already disabled SQL logger.';
			throw new \SS6\ShopBundle\Component\Doctrine\Exception\SqlLoggerAlreadyDisabledException($message);
		}
		$this->sqlLogger = $this->em->getConnection()->getConfiguration()->getSQLLogger();
		$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
		$this->isLoggerTemporarilyDisabled = true;
	}

	public function reenableLogging() {
		if (!$this->isLoggerTemporarilyDisabled) {
			$message = 'Trying to reenable already enabled SQL logger.';
			throw new \SS6\ShopBundle\Component\Doctrine\Exception\SqlLoggerAlreadyEnabledException($message);
		}
		$this->em->getConnection()->getConfiguration()->setSQLLogger($this->sqlLogger);
		$this->sqlLogger = null;
		$this->isLoggerTemporarilyDisabled = true;
	}

}
