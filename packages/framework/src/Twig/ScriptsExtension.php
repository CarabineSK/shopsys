<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ScriptsExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Script\ScriptFacade
     */
    protected $scriptFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFacade $scriptFacade
     */
    public function __construct(
        ScriptFacade $scriptFacade
    ) {
        $this->scriptFacade = $scriptFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAllPagesBeforeContentScripts', [$this, 'getAllPagesBeforeContentScripts']),
            new TwigFunction('getAllPagesAfterContentScripts', [$this, 'getAllPagesAfterContentScripts']),
            new TwigFunction('getOrderSentPageScripts', [$this, 'getOrderSentPageScripts']),
        ];
    }

    /**
     * @return string
     */
    public function getAllPagesBeforeContentScripts(): string
    {
        return implode("\n", $this->scriptFacade->getAllPagesBeforeContentScriptCodes());
    }

    /**
     * @return string
     */
    public function getAllPagesAfterContentScripts(): string
    {
        return implode("\n", $this->scriptFacade->getAllPagesAfterContentScriptCodes());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    public function getOrderSentPageScripts(Order $order): string
    {
        return implode("\n", $this->scriptFacade->getOrderSentPageScriptCodesWithReplacedVariables($order));
    }
}
