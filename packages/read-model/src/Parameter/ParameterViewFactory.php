<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;

class ParameterViewFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView
     */
    public function createFromProductParameterValue(ProductParameterValue $productParameterValue): ParameterView
    {
        return new ParameterView(
            $productParameterValue->getParameter()->getId(),
            $productParameterValue->getParameter()->getName(),
            $productParameterValue->getValue()->getId(),
            $productParameterValue->getValue()->getText()
        );
    }

    /**
     * @param array $parameter
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView
     */
    public function createFromElasticsearchParameter(array $parameter): ParameterView
    {
        return new ParameterView(
            $parameter['parameter_id'],
            $parameter['parameter_name'],
            $parameter['parameter_value_id'],
            $parameter['parameter_value_text']
        );
    }
}
