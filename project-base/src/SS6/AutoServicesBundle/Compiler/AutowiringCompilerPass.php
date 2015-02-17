<?php

namespace SS6\AutoServicesBundle\Compiler;

use ReflectionClass;
use SS6\AutoServicesBundle\Compiler\AutoServicesCollector;
use SS6\AutoServicesBundle\Compiler\ClassConstructorFiller;
use SS6\AutoServicesBundle\Compiler\ContainerClassList;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AutowiringCompilerPass implements CompilerPassInterface {

	/**
	 * @var \SS6\AutoServicesBundle\Compiler\ClassConstructorFiller
	 */
	private $classConstructorFiller;

	/**
	 * @param \SS6\AutoServicesBundle\Compiler\ClassConstructorFiller $classConstructorFiller
	 */
	public function __construct(ClassConstructorFiller $classConstructorFiller) {
		$this->classConstructorFiller = $classConstructorFiller;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 */
	public function process(ContainerBuilder $containerBuilder) {
		$containerClassListDefinition = $containerBuilder->getDefinition('ss6.auto_services.container_class_list');
		$containerClassList = $containerBuilder->resolveServices($containerClassListDefinition);
		/* @var $containerClassList \SS6\AutoServicesBundle\Compiler\ContainerClassList */
		$autoServicesCollectorDefinition = $containerBuilder->getDefinition('ss6.auto_services.auto_services_collector');
		$autoServicesCollector = $containerBuilder->resolveServices($autoServicesCollectorDefinition);
		/* @var $autoServicesCollector \SS6\AutoServicesBundle\Compiler\AutoServicesCollector */

		$containerClassList->clean();
		$this->loadContainerClassList($containerBuilder, $containerClassList);
		$this->autowireContainerBuilderDefinitions($containerBuilder, $containerClassList);
		$this->processAutoServicesCollectorData($containerBuilder, $containerClassList, $autoServicesCollector);
		$this->replaceDefaultServiceContainer($containerBuilder);
		$containerClassList->save();
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 * @param \SS6\AutoServicesBundle\Compiler\ContainerClassList $containerClassList
	 */
	private function loadContainerClassList(ContainerBuilder $containerBuilder, ContainerClassList $containerClassList) {
		foreach ($containerBuilder->getDefinitions() as $serviceId => $definition) {
			if ($definition->getClass() !== null) {
				$containerClassList->addClass($serviceId, $definition->getClass());
			}
		}
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 * @param \SS6\AutoServicesBundle\Compiler\ContainerClassList $containerClassList
	 */
	private function autowireContainerBuilderDefinitions(
		ContainerBuilder $containerBuilder,
		ContainerClassList $containerClassList
	) {
		foreach ($containerBuilder->getDefinitions() as $serviceId => $definition) {
			if ($definition->isAbstract()
				|| !$definition->isPublic()
				|| $definition->getClass() === null
				|| $definition->getFactoryClass()
				|| $definition->getFactoryMethod()
			) {
				continue;
			}

			$this->autowireClassDefinition($containerBuilder, $serviceId, $definition, $containerClassList);
		}
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 * @param \SS6\AutoServicesBundle\Compiler\ContainerClassList $containerClassList
	 * @param \SS6\AutoServicesBundle\Compiler\AutoServicesCollector $autoServicesCollector
	 */
	private function processAutoServicesCollectorData(
		ContainerBuilder $containerBuilder,
		ContainerClassList $containerClassList,
		AutoServicesCollector $autoServicesCollector
	) {
		$newCollectorData = [];
		foreach ($autoServicesCollector->getServicesClassesIndexedByServiceId() as $serviceId => $class) {
			if (!$containerClassList->hasClass($class)) {
				$newCollectorData[$serviceId] = $class;
				$definition = new Definition($class);
				$this->autowireClassDefinition($containerBuilder, $serviceId, $definition, $containerClassList);
				$containerBuilder->setDefinition($serviceId, $definition);
			}
		}
		$autoServicesCollector->setServices($newCollectorData);
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 */
	private function replaceDefaultServiceContainer(ContainerBuilder $containerBuilder) {
		foreach ($containerBuilder->getDefinitions() as $serviceId => $definition) {
			if ($serviceId !== 'ss6.auto_services.auto_container') {
				$this->replaceDefaultServiceContainerInDefinition($definition);
			}
		}
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\Definition $definition
	 */
	private function replaceDefaultServiceContainerInDefinition(Definition $definition) {
		foreach ($definition->getArguments() as $argumentIndex => $argument) {
			if ($argument instanceof Reference) {
				$argumentId = (string)$argument;
				if ($argumentId === 'service_container') {
					$newArgument = new Reference(
						'ss6.auto_services.auto_container',
						$argument->getInvalidBehavior(),
						$argument->isStrict()
					);
					$definition->replaceArgument($argumentIndex, $newArgument);
				}
			}
		}
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 * @param string $serviceId
	 * @param \Symfony\Component\DependencyInjection\Definition $definition
	 * @param \SS6\AutoServicesBundle\Compiler\ContainerClassList $containerClassList
	 */
	private function autowireClassDefinition(
		ContainerBuilder $containerBuilder,
		$serviceId,
		Definition $definition,
		ContainerClassList $containerClassList
	) {
		$reflectionClass = new ReflectionClass($definition->getClass());
		$constructor = $reflectionClass->getConstructor();

		if ($constructor !== null && $constructor->isPublic()) {
			$this->classConstructorFiller->autowireParams($constructor, $serviceId, $definition, $containerClassList);
		}

		$this->watchServiceClassForChanges($reflectionClass, $containerBuilder);
	}

	/**
	 * @param \ReflectionClass $reflectionClass
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
	 */
	private function watchServiceClassForChanges(ReflectionClass $reflectionClass, ContainerBuilder $containerBuilder) {
		do {
			$containerBuilder->addResource(new FileResource($reflectionClass->getFileName()));
		} while ($reflectionClass = $reflectionClass->getParentClass());
	}

}
