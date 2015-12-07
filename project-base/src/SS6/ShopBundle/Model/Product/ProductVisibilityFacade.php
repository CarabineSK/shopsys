<?php

namespace SS6\ShopBundle\Model\Product;

use SS6\ShopBundle\Model\Product\ProductVisibilityRepository;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductVisibilityFacade {

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductVisibilityRepository
	 */
	private $productVisibilityRepository;

	/**
	 * @var bool
	 */
	private $recalcVisibilityForMarked = false;

	/**
	 * @var bool
	 */
	private $recalcVisibility = false;

	/**
	 * @param \SS6\ShopBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
	 */
	public function __construct(ProductVisibilityRepository $productVisibilityRepository) {
		$this->productVisibilityRepository = $productVisibilityRepository;
	}

	public function refreshProductsVisibilityForMarkedDelayed() {
		$this->recalcVisibilityForMarked = true;
	}

	public function refreshProductsVisibilityDelayed() {
		$this->recalcVisibility = true;
	}

	public function refreshProductsVisibility() {
		$this->productVisibilityRepository->refreshProductsVisibility();
	}

	public function refreshProductsVisibilityForMarked() {
		$this->productVisibilityRepository->refreshProductsVisibility(true);
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event) {
		if (!$event->isMasterRequest()) {
			return;
		}

		if ($this->recalcVisibilityForMarked) {
			$this->refreshProductsVisibilityForMarked();
		}

		if ($this->recalcVisibility) {
			$this->refreshProductsVisibility();
		}
	}
}
