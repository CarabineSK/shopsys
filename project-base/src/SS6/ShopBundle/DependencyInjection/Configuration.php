<?php

namespace SS6\ShopBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * See {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('ss6_shop');

		$rootNode
			->children()
				->arrayNode('router')
					->children()
						->arrayNode('locale_routers')
						->defaultValue([])
						->prototype('scalar')
						->end()
					->end()
						->scalarNode('friendly_url_router')
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

}
