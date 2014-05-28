<?php

namespace SS6\ShopBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

class FormThemeExtension extends \Twig_Extension {

	const ADMIN_THEME = '@SS6Shop/Admin/Form/theme.html.twig';
	const FRONT_THEME = '@SS6Shop/Front/Form/theme.html.twig';

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requestStack;

	/**
	 * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
	 */
	public function __construct(RequestStack $requestStack) {
		$this->requestStack = $requestStack;
		$this->request = $this->requestStack->getMasterRequest();
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('get_default_form_theme', array($this, 'getDefaultFormTheme')),
		);
	}

	/**
	 * @return string
	 */
	public function getDefaultFormTheme() {
		if (mb_stripos($this->request->get('_controller'), 'SS6\ShopBundle\Controller\Admin') === 0) {
			return self::ADMIN_THEME;
		} else {
			return self::FRONT_THEME;
		}
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'form_theme';
	}

}
