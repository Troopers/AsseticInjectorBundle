<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Troopers\AsseticInjectorBundle\Twig;

use Assetic\Extension\Twig\AsseticExtension as BaseAsseticExtension;
use Assetic\Factory\AssetFactory;
use Assetic\ValueSupplierInterface;
use Symfony\Bundle\AsseticBundle\Twig\AsseticNodeVisitor;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Assetic extension.
 *
 * @author Leny Bernard <leny@troopers.email>
 * @author Paul Andrieux <paul@troopers.email>
 */
class AsseticExtension extends BaseAsseticExtension implements \Twig_Extension_GlobalsInterface
{
    private $useController;
    private $templateNameParser;
    private $enabledBundles;
    private $collector;

    public function __construct(AssetFactory $factory, TemplateNameParserInterface $templateNameParser, $useController = false, $functions = [], $enabledBundles = [], ValueSupplierInterface $valueSupplier = null)
    {
        parent::__construct($factory, $functions, $valueSupplier);

        $this->useController = $useController;
        $this->templateNameParser = $templateNameParser;
        $this->enabledBundles = $enabledBundles;
    }

    public function setCollector($collector)
    {
        $this->collector = $collector;
    }

    public function getTokenParsers()
    {
        return [
            $this->createTokenParser('javascripts', 'js/*.js'),
            $this->createTokenParser('stylesheets', 'css/*.css'),
            $this->createTokenParser('image', 'images/*', true),
        ];
    }

    public function getNodeVisitors()
    {
        return [
            new AsseticNodeVisitor($this->templateNameParser, $this->enabledBundles),
        ];
    }

    public function getGlobals()
    {
        $globals = parent::getGlobals();
        $globals['assetic']['use_controller'] = $this->useController;

        return $globals;
    }

    private function createTokenParser($tag, $output, $single = false)
    {
        $tokenParser = new AsseticInjectorTokenParser($this->factory, $tag, $output, $single, ['package']);
        if (in_array($tag, ['stylesheets', 'javascripts'])) {
            $this->collector->injectAssets($tokenParser);
        }
        $tokenParser->setTemplateNameParser($this->templateNameParser);
        $tokenParser->setEnabledBundles($this->enabledBundles);

        return $tokenParser;
    }
}
