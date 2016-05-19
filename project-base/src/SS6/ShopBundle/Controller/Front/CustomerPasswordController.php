<?php

namespace SS6\ShopBundle\Controller\Front;

use SS6\ShopBundle\Component\Controller\FrontBaseController;
use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Form\Front\Registration\NewPasswordFormType;
use SS6\ShopBundle\Form\Front\Registration\ResetPasswordFormType;
use SS6\ShopBundle\Model\Customer\CustomerPasswordFacade;
use SS6\ShopBundle\Model\Security\LoginService;
use Symfony\Component\HttpFoundation\Request;

class CustomerPasswordController extends FrontBaseController {

	/**
	 * @var \SS6\ShopBundle\Model\Customer\CustomerPasswordFacade
	 */
	private $customerPasswordFacade;

	/**
	 * @var \SS6\ShopBundle\Component\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Model\Security\LoginService
	 */
	private $loginService;

	public function __construct(
		Domain $domain,
		CustomerPasswordFacade $customerPasswordFacade,
		LoginService $loginService
	) {
		$this->domain = $domain;
		$this->customerPasswordFacade = $customerPasswordFacade;
		$this->loginService = $loginService;
	}

	public function resetPasswordAction(Request $request) {
		$form = $this->createForm(new ResetPasswordFormType());

		$form->handleRequest($request);

		if ($form->isValid()) {
			$formData = $form->getData();
			$email = $formData['email'];

			try {
				$this->customerPasswordFacade->resetPassword($email, $this->domain->getId());

				$this->getFlashMessageSender()->addSuccessFlashTwig(
					t('Odkaz pro vyresetování hesla byl zaslán na email <strong>{{ email }}</strong>.'),
					[
						'email' => $email,
					]
				);
				return $this->redirectToRoute('front_registration_reset_password');
			} catch (\SS6\ShopBundle\Model\Customer\Exception\UserNotFoundByEmailAndDomainException $ex) {
				$this->getFlashMessageSender()->addErrorFlashTwig(
					t('Zákazník s emailovou adresou <strong>{{ email }}</strong> neexistuje.'
						. ' <a href="{{ registrationLink }}">Zaregistrovat</a>'),
					[
						'email' => $ex->getEmail(),
						'registrationLink' => $this->generateUrl('front_registration_register'),
					]
				);
			}
		}

		return $this->render('@SS6Shop/Front/Content/Registration/resetPassword.html.twig', [
			'form' => $form->createView(),
		]);
	}

	public function setNewPasswordAction(Request $request) {
		$email = $request->query->get('email');
		$hash = $request->query->get('hash');

		if (!$this->customerPasswordFacade->isResetPasswordHashValid($email, $this->domain->getId(), $hash)) {
			$this->getFlashMessageSender()->addErrorFlash(t('Platnost odkazu pro změnu hesla vypršela.'));
			return $this->redirectToRoute('front_homepage');
		}

		$form = $this->createForm(new NewPasswordFormType());

		$form->handleRequest($request);

		if ($form->isValid()) {
			$formData = $form->getData();

			$newPassword = $formData['newPassword'];

			try {
				$user = $this->customerPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

				$this->loginService->loginUser($user, $request);
			} catch (\SS6\ShopBundle\Model\Customer\Exception\UserNotFoundByEmailAndDomainException $ex) {
				$this->getFlashMessageSender()->addErrorFlashTwig(
					t('Zákazník s emailovou adresou <strong>{{ email }}</strong> neexistuje.'
						. ' <a href="{{ registrationLink }}">Zaregistrovat</a>'),
					[
						'email' => $ex->getEmail(),
						'registrationLink' => $this->generateUrl('front_registration_register'),
					]
				);
			} catch (\SS6\ShopBundle\Model\Customer\Exception\InvalidResetPasswordHashException $ex) {
				$this->getFlashMessageSender()->addErrorFlash(t('Platnost odkazu pro změnu hesla vypršela.'));
			}

			$this->getFlashMessageSender()->addSuccessFlash(t('Heslo bylo úspěšně změněno'));
			return $this->redirectToRoute('front_homepage');
		}

		return $this->render('@SS6Shop/Front/Content/Registration/setNewPassword.html.twig', [
			'form' => $form->createView(),
		]);
	}

}
