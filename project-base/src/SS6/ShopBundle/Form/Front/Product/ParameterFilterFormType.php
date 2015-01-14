<?php

namespace SS6\ShopBundle\Form\Front\Product;

use SS6\ShopBundle\Model\Product\Filter\ParameterFilterData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParameterFilterFormType extends AbstractType implements DataTransformerInterface {

	/**
	 * @var \SS6\ShopBundle\Model\Product\Filter\ParameterFilterChoice[]
	 */
	private $parameterChoicesIndexedByParameterId;

	/**
	 * @param \SS6\ShopBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterFilterChoices
	 */
	public function __construct(array $parameterFilterChoices) {
		$this->parameterChoicesIndexedByParameterId = [];
		foreach ($parameterFilterChoices as $parameterChoice) {
			$this->parameterChoicesIndexedByParameterId[$parameterChoice->getParameter()->getId()] = $parameterChoice;
		}
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		foreach ($this->parameterChoicesIndexedByParameterId as $parameterId => $parameterFilterChoice) {
			$builder
				->add($parameterId, 'choice', [
					'label' => $parameterFilterChoice->getParameter()->getName(),
					'expanded' => true,
					'multiple' => true,
					'choice_list' => new ObjectChoiceList(
						$parameterFilterChoice->getValues(),
						'text',
						[],
						null,
						'id'
					),
				]);
		}

		$builder->addViewTransformer($this);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'parameterFilter';
	}

	/**
	 * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'attr' => array('novalidate' => 'novalidate'),
		));
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Parameter\ParameterValue[][]|null $value
	 * @return \SS6\ShopBundle\Model\Product\Filter\ParameterFilterData[]|null
	 */
	public function reverseTransform($value) {
		if ($value === null) {
			return null;
		}

		$parametersFilterData = [];
		foreach ($value as $parameterId => $parameterValues) {
			if (!array_key_exists($parameterId, $this->parameterChoicesIndexedByParameterId)) {
				continue; // invalid parameter IDs are ignored
			}

			$parametersFilterData[] = new ParameterFilterData(
				$this->parameterChoicesIndexedByParameterId[$parameterId]->getParameter(),
				$parameterValues
			);
		}

		return $parametersFilterData;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Product\Filter\ParameterFilterData[]|null $value
	 * @return \SS6\ShopBundle\Model\Product\Parameter\ParameterValue[][]|null
	 */
	public function transform($value) {
		if ($value === null) {
			return null;
		}

		$parameterValuesIndexedByParameterId = [];
		foreach ($value as $parameterFilterData) {
			$parameterId = $parameterFilterData->parameter->getId();
			$parameterValuesIndexedByParameterId[$parameterId] = $parameterFilterData->values;
		}

		return $parameterValuesIndexedByParameterId;
	}

}
