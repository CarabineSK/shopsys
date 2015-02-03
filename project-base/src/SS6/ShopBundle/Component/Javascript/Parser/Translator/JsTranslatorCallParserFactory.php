<?php

namespace SS6\ShopBundle\Component\Javascript\Parser\Translator;

use SS6\ShopBundle\Component\Javascript\Parser\JsFunctionCallParser;
use SS6\ShopBundle\Component\Javascript\Parser\JsStringParser;
use SS6\ShopBundle\Component\Javascript\Parser\Translator\JsTranslatorCallParser;
use SS6\ShopBundle\Component\Translation\TransMethodSpecification;

class JsTranslatorCallParserFactory {

	/**
	 * @var \SS6\ShopBundle\Component\Javascript\Parser\JsFunctionCallParser
	 */
	private $jsFunctionCallParser;

	/**
	 * @var \SS6\ShopBundle\Component\Javascript\Parser\JsStringParser
	 */
	private $jsStringParser;

	/**
	 * @param \SS6\ShopBundle\Component\Javascript\Parser\JsFunctionCallParser $jsFunctionCallParser
	 * @param \SS6\ShopBundle\Component\Javascript\Parser\JsStringParser $jsStringParser
	 */
	public function __construct(
		JsFunctionCallParser $jsFunctionCallParser,
		JsStringParser $jsStringParser
	) {
		$this->jsFunctionCallParser = $jsFunctionCallParser;
		$this->jsStringParser = $jsStringParser;
	}

	/**
	 * @return \SS6\ShopBundle\Component\Javascript\Parser\Translator\JsTranslatorCallParser
	 */
	public function create() {
		$transMethodSpecifications = [
			new TransMethodSpecification('SS6.translator.trans', 0, 2),
			new TransMethodSpecification('SS6.translator.transChoice', 0, 3),
		];

		return new JsTranslatorCallParser(
			$this->jsFunctionCallParser,
			$this->jsStringParser,
			$transMethodSpecifications
		);
	}

}
