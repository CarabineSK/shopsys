<?php

namespace SS6\ShopBundle\Controller\Admin;

use SS6\ShopBundle\Component\Controller\AdminBaseController;

class FlashMessageController extends AdminBaseController {

	public function indexAction() {
		$flashMessageBag = $this->get('ss6.shop.flash_message.bag.admin');
		/* @var $flashMessageBag \SS6\ShopBundle\Model\FlashMessage\Bag */

		return $this->render('@SS6Shop/Admin/Inline/FlashMessage/index.html.twig', [
			'errorMessages' => $flashMessageBag->getErrorMessages(),
			'infoMessages' => $flashMessageBag->getInfoMessages(),
			'successMessages' => $flashMessageBag->getSuccessMessages(),
		]);
	}

}
