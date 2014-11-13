<?php

namespace SS6\ShopBundle\Component\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class EmptyWysiwygTransformer implements DataTransformerInterface {

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function reverseTransform($value) {
		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function transform($value) {

		$valueTrimmed = strip_tags(preg_replace('/\s|\&nbsp\;/', '', $value));
		if ($valueTrimmed === '') {
			return null;
		} else {
			return $value;
		}
	}

}
