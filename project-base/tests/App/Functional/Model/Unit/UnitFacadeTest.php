<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Unit;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitData;
use Tests\App\Test\TransactionFunctionalTestCase;

class UnitFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    private $unitFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    public function setUp(): void
    {
        parent::setUp();
        $this->productDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface::class);
        $this->unitFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade::class);
        $this->productFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\ProductFacade::class);
    }

    public function testDeleteByIdAndReplace()
    {
        $unitData = new UnitData();
        $unitData->name = ['cs' => 'name'];
        $unitToDelete = $this->unitFacade->create($unitData);
        /** @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unitToReplaceWith */
        $unitToReplaceWith = $this->getReference(UnitDataFixture::UNIT_PIECES);
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productData = $this->productDataFactory->createFromProduct($product);

        $productData->unit = $unitToDelete;
        $this->productFacade->edit($product->getId(), $productData);

        $this->unitFacade->deleteById($unitToDelete->getId(), $unitToReplaceWith->getId());

        $this->em->refresh($product);

        $this->assertEquals($unitToReplaceWith, $product->getUnit());
    }
}
