<?php

namespace SS6\ShopBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler;

class RedirectFragmentHandler extends LazyLoadingFragmentHandler {

	/**
	 * Copy-pasted from Symfony\Component\HttpKernel\Fragment\FragmentHandler::deliver().
	 *
	 * {@inheritdoc}
	 */
	protected function deliver(Response $response) {
		// Redirect response in fragment is OK, because SubRequestListener will do the redirection
		// when handling the master request.
		if (!$response->isSuccessful() && !$response->isRedirection()) {
			$message = sprintf(
				'Error when rendering "%s" (Status code is %s).',
				$this->getRequest()->getUri(),
				$response->getStatusCode()
			);
			throw new \RuntimeException($message);
		}

		if (!$response instanceof StreamedResponse) {
			return $response->getContent();
		}

		$response->sendContent();
	}

}
