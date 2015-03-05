<?php

namespace SS6\ShopBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SS6ShopExtension extends ConfigurableExtension {

	/**
	 * {@inheritDoc}
	 */
	protected function loadInternal(array $config, ContainerBuilder $container) {
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		$container->setParameter('ss6.router.locale_router_filepaths', $config['router']['locale_router_filepaths']);
		$container->setParameter('ss6.router.friendly_url_router_filepath', $config['router']['friendly_url_router_filepath']);
	}

}
