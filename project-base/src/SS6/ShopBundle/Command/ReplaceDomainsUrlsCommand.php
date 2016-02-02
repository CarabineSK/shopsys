<?php

namespace SS6\ShopBundle\Command;

use SS6\ShopBundle\Component\Domain\Domain;
use SS6\ShopBundle\Component\Domain\DomainUrlService;
use SS6\ShopBundle\Component\Setting\Setting;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceDomainsUrlsCommand extends ContainerAwareCommand {

	protected function configure() {
		$this
			->setName('ss6:domains-urls:replace')
			->setDescription('Replace domains urls in database by urls in domains config');
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$domain = $this->getContainer()->get(Domain::class);
		/* @var $domain \SS6\ShopBundle\Component\Domain\Domain */
		$domainUrlService = $this->getContainer()->get(DomainUrlService::class);
		/* @var $domainUrlService \SS6\ShopBundle\Component\Domain\DomainUrlService */
		$setting = $this->getContainer()->get(Setting::class);
		/* @var $setting \SS6\ShopBundle\Component\Setting\Setting */

		foreach ($domain->getAll() as $domainConfig) {
			$domainConfigUrl = $domainConfig->getUrl();
			$domainSettingUrl = $setting->get(Setting::BASE_URL, $domainConfig->getId());

			if ($domainConfigUrl !== $domainSettingUrl) {
				$output->writeln(
					'Domain ' . $domainConfig->getId() . ' URL is not matching URL stored in database.'
				);
				$output->writeln('Replacing domain URL in all string columns...');
				$domainUrlService->replaceUrlInStringColumns($domainConfigUrl, $domainSettingUrl);
				$output->writeln('<fg=green>URL successfully replaced.</fg=green>');
			} else {
				$output->writeln('Domain ' . $domainConfig->getId() . ' URL is matching URL stored in database.');
			}
		}
	}

}
