<?php

namespace SS6\ShopBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCollection extends Constraint {

	public $message = 'Values are duplicate.';
	public $fields = array();

}
