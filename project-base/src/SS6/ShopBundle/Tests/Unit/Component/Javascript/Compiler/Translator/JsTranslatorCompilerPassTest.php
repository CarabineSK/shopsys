<?php

namespace SS6\ShopBundle\Tests\Unit\Javascript\Compiler\Translator;

use SS6\ShopBundle\Component\Javascript\Compiler\JsCompiler;
use SS6\ShopBundle\Tests\Test\FunctionalTestCase;

class JsTranslatorCompilerPassTest extends FunctionalTestCase {

	public function testProcess() {
		$translator = $this->getContainer()->get('translator');
		/* @var $translator \SS6\ShopBundle\Component\Translation\Translator */
		$jsTranslatorCompilerPass = $this->getContainer()
			->get('ss6.shop.component.javascript.compiler.translator.js_translator_compiler_pass');
		/* @var $jsTranslatorCompilerPass \SS6\ShopBundle\Component\Javascript\Compiler\Translator\JsTranslatorCompilerPass */

		// set undefined locale to make Translator add '##' prefix
		$translator->setLocale('undefinedLocale');

		$jsCompiler = new JsCompiler([
			$jsTranslatorCompilerPass,
		]);

		$content = file_get_contents(__DIR__ . '/testFoo.js');
		$result = $jsCompiler->compile($content);

		$expectedResult = <<<EOD
var x = SS6.translator.trans ( "##foo" );
var y = SS6.translator.trans ( "##foo2", { 'param' : 'value' }, 'asdf' );
var z = SS6.translator.transChoice ( "##foo3" );
EOD;

		$this->assertSame($expectedResult, $result);
	}

}
