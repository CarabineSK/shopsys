<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Translation;

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use SS6\ShopBundle\Component\Translation\PhpFileExtractorFactory;

class PhpFileExtractorTest extends \PHPUnit_Framework_TestCase {

	public function testExtractController() {
		$fileName = 'Controller.php';

		$catalogue = $this->extract(__DIR__ . '/' . $fileName);

		$expected = new MessageCatalogue();

		$message = new Message('ErrorFlash', 'messages');
		$message->addSource(new FileSource($fileName, 16));
		$expected->add($message);

		$message = new Message('InfoFlash', 'messages');
		$message->addSource(new FileSource($fileName, 17));
		$expected->add($message);

		$message = new Message('SuccessFlash', 'messages');
		$message->addSource(new FileSource($fileName, 18));
		$expected->add($message);

		$message = new Message('ErrorFlashTwig', 'messages');
		$message->addSource(new FileSource($fileName, 19));
		$expected->add($message);

		$message = new Message('InfoFlashTwig', 'messages');
		$message->addSource(new FileSource($fileName, 20));
		$expected->add($message);

		$message = new Message('SuccessFlashTwig', 'messages');
		$message->addSource(new FileSource($fileName, 21));
		$expected->add($message);

		$message = new Message('SuccessFlashTwig2', 'messages');
		$message->addSource(new FileSource($fileName, 23));
		$expected->add($message);

		$message = new Message('trans test', 'messages');
		$message->addSource(new FileSource($fileName, 25));
		$expected->add($message);

		$message = new Message('transChoice test', 'messages');
		$message->addSource(new FileSource($fileName, 27));
		$expected->add($message);

		$message = new Message('trans test with domain', 'testDomain');
		$message->addSource(new FileSource($fileName, 29));
		$expected->add($message);

		$message = new Message('transChoice test with domain', 'testDomain');
		$message->addSource(new FileSource($fileName, 30));
		$expected->add($message);

		$this->assertEquals($expected, $catalogue);
	}

	private function getExtractor() {
		$phpFileExtractorFactory = new PhpFileExtractorFactory($this->getDocParser());
		return $phpFileExtractorFactory->create();
	}

	private function extract($filename) {
		if (!is_file($filename)) {
			throw new \RuntimeException(sprintf('The file "%s" does not exist.', $filename));
		}
		$file = new \SplFileInfo($filename);

		$extractor = $this->getExtractor();

		$lexer = new \PHPParser_Lexer();
		$parser = new \PHPParser_Parser($lexer);
		$ast = $parser->parse(file_get_contents($file));

		$catalogue = new MessageCatalogue();
		$extractor->visitPhpFile($file, $catalogue, $ast);

		return $catalogue;
	}

	private function getDocParser() {
		$docParser = new DocParser();
		$docParser->setImports([
			'desc' => 'JMS\TranslationBundle\Annotation\Desc',
			'meaning' => 'JMS\TranslationBundle\Annotation\Meaning',
			'ignore' => 'JMS\TranslationBundle\Annotation\Ignore',
		]);
		$docParser->setIgnoreNotImportedAnnotations(true);

		return $docParser;
	}

}
