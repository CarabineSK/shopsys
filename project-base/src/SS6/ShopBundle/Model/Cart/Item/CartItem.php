<?php

namespace SS6\ShopBundle\Model\Cart\Item;

use Doctrine\ORM\Mapping as ORM;
use SS6\ShopBundle\Model\Customer\CustomerIdentifier;
use SS6\ShopBundle\Model\Product\Product;

/**
 * @ORM\Table(name="cart_items")
 * @ORM\Entity
 */
class CartItem {

	/**
	 * @var int
	 *
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=127)
	 */
	private $sessionId;

	/**
	 * @var \SS6\ShopBundle\Model\Customer\User
	 *
	 * @ORM\ManyToOne(targetEntity="SS6\ShopBundle\Model\Customer\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable = true)
	 */
	private $user;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Product
	 *
	 * @ORM\ManyToOne(targetEntity="SS6\ShopBundle\Model\Product\Product")
	 * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	private $product;

	/**
	 * @var int
	 *
	 * @ORM\Column(type="integer")
	 */
	private $quantity;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="decimal", precision=20, scale=6, nullable=true)
	 */
	private $watchedPrice;

	/**
	 * @param \SS6\ShopBundle\Model\Customer\CustomerIdentifier $customerIdentifier
	 * @param \SS6\ShopBundle\Model\Product\Product $product
	 * @param int $quantity
	 * @param string $watchedPrice
	 */
	public function __construct(
		CustomerIdentifier $customerIdentifier,
		Product $product,
		$quantity,
		$watchedPrice
	) {
		$this->sessionId = $customerIdentifier->getSessionId();
		$this->user = $customerIdentifier->getUser();
		$this->product = $product;
		$this->watchedPrice = $watchedPrice;
		$this->changeQuantity($quantity);
	}

	/**
	 * @param int $newQuantity
	 */
	public function changeQuantity($newQuantity) {
		if (!is_int($newQuantity) || $newQuantity <= 0) {
			throw new \SS6\ShopBundle\Model\Cart\Exception\InvalidQuantityException($newQuantity);
		}

		$this->quantity = $newQuantity;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return \SS6\ShopBundle\Model\Product\Product|null
	 */
	public function getProduct() {
		if ($this->product === null) {
			throw new \SS6\ShopBundle\Model\Product\Exception\ProductNotFoundException();
		}

		return $this->product;
	}

	/**
	 * @param string|null $locale
	 * @return \SS6\ShopBundle\Model\Product\Product
	 */
	public function getName($locale = null) {
		return $this->getProduct()->getName($locale);
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * @return string|null
	 */
	public function getWatchedPrice() {
		return $this->watchedPrice;
	}

	/**
	 * @param string|null $watchedPrice
	 */
	public function setWatchedPrice($watchedPrice) {
		$this->watchedPrice = $watchedPrice;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Cart\Item\CartItem $cartItem
	 * @return bool
	 */
	public function isSimilarItemAs(CartItem $cartItem) {
		return $this->getProduct()->getId() === $cartItem->getProduct()->getId();
	}

	/**
	 * @return string
	 */
	public function getSessionId() {
		return $this->sessionId;
	}
}
