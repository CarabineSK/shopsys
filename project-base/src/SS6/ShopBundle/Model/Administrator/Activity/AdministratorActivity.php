<?php

namespace SS6\ShopBundle\Model\Administrator\Activity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use SS6\ShopBundle\Model\Administrator\Administrator;

/**
 * @ORM\Table(name="administrator_activities")
 * @ORM\Entity
 */
class AdministratorActivity {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var \SS6\ShopBundle\Model\Administrator\Administrator
	 *
	 * @ORM\ManyToOne(targetEntity="\SS6\ShopBundle\Model\Administrator\Administrator")
	 * @ORM\JoinColumn(nullable=false, name="administrator_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $administrator;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=45)
	 */
	private $ipAddress;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $loginTime;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $lastActionTime;

	/**
	 * @param \SS6\ShopBundle\Model\Administrator\Administrator $administrator
	 * @param string $ipAddress
	 */
	public function __construct(
		Administrator $administrator,
		$ipAddress
	) {
		$this->administrator = $administrator;
		$this->ipAddress = $ipAddress;
		$this->loginTime = new DateTime();
		$this->lastActionTime = new DateTime();
	}

	public function updateLastActionTime() {
		$this->lastActionTime = new DateTime();
	}

}
