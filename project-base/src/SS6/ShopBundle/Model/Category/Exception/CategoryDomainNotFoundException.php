<?php

namespace SS6\ShopBundle\Model\Category\Exception;

use SS6\ShopBundle\Model\Category\Exception\CategoryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryDomainNotFoundException extends NotFoundHttpException implements CategoryException {

	/**
	 * @param int $categoryId
	 * @param int $domainId
	 * @param \Exception $previous
	 */
	public function __construct($categoryId, $domainId, \Exception $previous = null) {
		$message = sprintf(
			'CategoryDomain for category ID %d and domain ID %d not found.',
			$categoryId,
			$domainId
		);
		parent::__construct($message, $previous, 0);
	}

}
