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

use Assetic\Asset\AssetInterface;
use Symfony\Bundle\AsseticBundle\Exception\InvalidBundleException;
use Symfony\Bundle\AsseticBundle\Factory\AssetFactory;
use Symfony\Bundle\AsseticBundle\Twig\AsseticNode;
use Symfony\Bundle\AsseticBundle\Twig\AsseticTokenParser as BaseAsseticTokenParser;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

/**
 * Assetic token parser.
 */
class AsseticInjectorTokenParser extends BaseAsseticTokenParser
{
    private $templateNameParser;
    private $enabledBundles;
    private $injectedAssets;

    public function __construct(AssetFactory $factory, $tag, $output, $single = false, array $extensions = [])
    {
        $this->factory = $factory;
        $this->tag = $tag;
        $this->output = $output;
        $this->single = $single;
        $this->extensions = $extensions;
        $this->injectedAssets = [];

        parent::__construct($factory, $tag, $output, $single, $extensions);
    }

    public function addAssets($assets)
    {
        $this->injectedAssets = $assets;
    }

    public function setTemplateNameParser(TemplateNameParserInterface $templateNameParser)
    {
        $this->templateNameParser = $templateNameParser;
    }

    public function setEnabledBundles(array $enabledBundles = null)
    {
        $this->enabledBundles = $enabledBundles;
    }

    public function parse(\Twig_Token $token)
    {
        if ($this->templateNameParser && is_array($this->enabledBundles)) {
            // check the bundle
            $templateRef = null;
            try {
                $templateRef = $this->templateNameParser->parse($this->parser->getStream()->getSourceContext()->getName());
            } catch (\RuntimeException $e) {
                // this happens when the filename isn't a Bundle:* url
                // and it contains ".."
            } catch (\InvalidArgumentException $e) {
                // this happens when the filename isn't a Bundle:* url
                // but an absolute path instead
            }
            $bundle = $templateRef instanceof TemplateReference ? $templateRef->get('bundle') : null;
            if ($bundle && !in_array($bundle, $this->enabledBundles)) {
                throw new InvalidBundleException($bundle, "the {% {$this->getTag()} %} tag", $templateRef->getLogicalName(), $this->enabledBundles);
            }
        }

        return $this->parseAndInject($token);
    }

    protected function createNode(AssetInterface $asset, \Twig_Node $body, array $inputs, array $filters, $name, array $attributes = [], $lineno = 0, $tag = null)
    {
        return new AsseticNode($asset, $body, $inputs, $filters, $name, $attributes, $lineno, $tag);
    }

    public function parseAndInject(\Twig_Token $token)
    {
        $inputs = $filters = $injectorLocationsAvailables = [];
        $name = $injectorLocation = null;

        foreach ($this->injectedAssets as $tag => $assets) {
            foreach ($assets as $injectorPath => $asset) {
                $injectorLocationsAvailables[$injectorPath] = $injectorPath;
            }
        }

        $attributes = [
            'output'   => $this->output,
            'var_name' => 'asset_url',
            'vars'     => [],
        ];

        $stream = $this->parser->getStream();
        while (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test(\Twig_Token::STRING_TYPE)) {
                // '@jquery', 'js/src/core/*', 'js/src/extra.js'
                $inputs[] = $stream->next()->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'filter')) {
                // filter='yui_js'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $filters = array_merge($filters, array_filter(array_map('trim', explode(',', $stream->expect(\Twig_Token::STRING_TYPE)->getValue()))));
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'injector')) {
                // injector='header'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $injectorLocation = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'output')) {
                // output='js/packed/*.js' OR output='js/core.js'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['output'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'name')) {
                // name='core_js'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $name = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'as')) {
                // as='the_url'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['var_name'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'debug')) {
                // debug=true
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['debug'] = 'true' == $stream->expect(\Twig_Token::NAME_TYPE, ['true', 'false'])->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'combine')) {
                // combine=true
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['combine'] = 'true' == $stream->expect(\Twig_Token::NAME_TYPE, ['true', 'false'])->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'vars')) {
                // vars=['locale','browser']
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $stream->expect(\Twig_Token::PUNCTUATION_TYPE, '[');

                while ($stream->test(\Twig_Token::STRING_TYPE)) {
                    $attributes['vars'][] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();

                    if (!$stream->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
                        break;
                    }

                    $stream->next();
                }

                $stream->expect(\Twig_Token::PUNCTUATION_TYPE, ']');
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, $this->extensions)) {
                // an arbitrary configured attribute
                $key = $stream->next()->getValue();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes[$key] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } else {
                $token = $stream->getCurrent();
                throw new \Twig_Error_Syntax(sprintf('Unexpected token "%s" of value "%s"', \Twig_Token::typeToEnglish($token->getType(), $token->getLine()), $token->getValue()), $token->getLine());
            }
        }
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse([$this, 'testEndTag'], true);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if ($injectorLocation) {
            $injectorLocationArray = explode(',', $injectorLocation);

            //INJECT
            foreach ($injectorLocationArray as $injectorLocation) {
                $injectorLocation = trim($injectorLocation);
                if (array_key_exists($this->tag, $this->injectedAssets) && in_array($injectorLocation, $injectorLocationsAvailables)) {
                    if (!empty($this->injectedAssets[$this->tag][$injectorLocation])) {
                        if (!is_array($this->injectedAssets[$this->tag][$injectorLocation])) {
                            $this->injectedAssets[$this->tag][$injectorLocation] = [$this->injectedAssets[$this->tag][$injectorLocation]];
                        }
                        $inputs = array_merge($inputs, $this->injectedAssets[$this->tag][$injectorLocation]);
                    }
                }
            }
        }
        if ($this->single && 1 < count($inputs)) {
            $inputs = array_slice($inputs, -1);
        }

        if (!$name) {
            $name = $this->factory->generateAssetName($inputs, $filters, $attributes);
        }

        $asset = $this->factory->createAsset($inputs, $filters, $attributes + ['name' => $name]);

        return $this->createNode($asset, $body, $inputs, $filters, $name, $attributes, $token->getLine(), $this->getTag());
    }
}
