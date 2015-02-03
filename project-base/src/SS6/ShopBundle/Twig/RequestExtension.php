<?php

namespace SS6\ShopBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Extension;
use Twig_SimpleFunction;

class RequestExtension extends Twig_Extension {

	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	private $requestStack;

	/**
	 * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
	 */
	public function __construct(RequestStack $requestStack) {
		$this->requestStack = $requestStack;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction(
				'getAllRequestParams',
				[$this, 'getAllRequestParams']
			),
			new Twig_SimpleFunction(
				'getRoute',
				[$this, 'getRoute']
			),
		];
	}

	/**
	 * @return array
	 */
	public function getAllRequestParams() {
		return array_merge(
			$this->getParamsFromRequest(),
			$this->getRouteParams()
		);
	}

	/**
	 * @return string
	 */
	public function getRoute() {
		return $this->requestStack->getMasterRequest()->attributes->get('_route');
	}

	/**
	 * @return array
	 */
	private function getParamsFromRequest() {
		return $this->requestStack->getMasterRequest()->query->all();
	}

	/**
	 * @return array
	 */
	private function getRouteParams() {
		return $this->requestStack->getMasterRequest()->attributes->get('_route_params');
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'request_extension';
	}

}
