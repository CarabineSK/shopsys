<?php

namespace SS6\ShopBundle\Tests\Component\String;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\String\TransformString;

class TransformStringTest extends PHPUnit_Framework_TestCase {

	public function safeFilenameProvider() {
		return [
			[
				'actual' => 'ěščřžýáíé.dat',
				'expected' => 'escrzyaie.dat',
			],
			[
				'actual' => 'ĚŠČŘŽÝÁÍÉ.DAT',
				'expected' => 'ESCRZYAIE.DAT',
			],
			[
				'actual' => 'Foo     Bar.dat',
				'expected' => 'Foo_Bar.dat',
			],
			[
				'actual' => 'Foo-Bar.dat',
				'expected' => 'Foo-Bar.dat',
			],
			[
				'actual' => '../../Foo.dat',
				'expected' => '_._Foo.dat',
			],
			[
				'actual' => '..\\..\\Foo.dat',
				'expected' => '_._Foo.dat',
			],
			[
				'actual' => '.foo.dat',
				'expected' => 'foo.dat',
			],
		];
	}

	/**
	 * @dataProvider safeFilenameProvider
	 */
	public function testSafeFilename($actual, $expected) {
		$this->assertSame($expected, TransformString::safeFilename($actual));
	}

	public function stringToFriendlyUrlSlugProvider() {
		return [
			[
				'actual' => 'ěščřžýáíé foo',
				'expected' => 'escrzyaie-foo',
			],
			[
				'actual' => 'ĚŠČŘŽÝÁÍÉ   ',
				'expected' => 'escrzyaie',
			],
			[
				'actual' => 'Foo     Bar-Baz',
				'expected' => 'foo-bar-baz',
			],
			[
				'actual' => 'foo-bar_baz',
				'expected' => 'foo-bar_baz',
			],
			[
				'actual' => '$€@!?<>=;~%^&',
				'expected' => '',
			],
			[
				'actual' => 'Příliš žluťoučký kůň úpěl ďábelské ódy',
				'expected' => 'prilis-zlutoucky-kun-upel-dabelske-ody',
			]
		];
	}

	/**
	 * @dataProvider stringToFriendlyUrlSlugProvider
	 */
	public function testStringToFriendlyUrlSlug($actual, $expected) {
		$this->assertSame($expected, TransformString::stringToFriendlyUrlSlug($actual));
	}

}
