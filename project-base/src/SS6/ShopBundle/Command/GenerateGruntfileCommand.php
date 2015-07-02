<?php

namespace SS6\ShopBundle\Command;

use SS6\ShopBundle\Model\Domain\Domain;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig_Environment;

class GenerateGruntfileCommand extends ContainerAwareCommand {

	protected function configure() {
		$this
			->setName('ss6:generate:gruntfile')
			->setDescription('Generate Gruntfile.js by domain settings');
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$domain = $this->getContainer()->get(Domain::class);
		/* @var $domain \SS6\ShopBundle\Model\Domain\Domain */
		$twig = $this->getContainer()->get(Twig_Environment::class);
		/* @var $twig \Twig_Environment */

		$output->writeln('Start of generating Gruntfile.js.');
		$gruntfileContents = $twig->render('@SS6Shop/Grunt/gruntfile.js.twig', [
			'domains' => $domain->getAll(),
			'rootStylesDirectory' => $this->getContainer()->getParameter('ss6.styles_dir'),
		]);
		$path = $this->getContainer()->getParameter('ss6.root_dir');
		file_put_contents($path . '/Gruntfile.js', $gruntfileContents);
		$output->writeln('<fg=green>Gruntfile.js was successfully generated.</fg=green>');
	}

}
