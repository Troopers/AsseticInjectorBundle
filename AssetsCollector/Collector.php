<?php
namespace AppVentus\AsseticInjectorBundle\AssetsCollector;

use Symfony\Component\Finder\Finder;

/**
 * Assets collector.
 *
 * @author Paul Andrieux <paul@appventus.com>
 */
class Collector
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function injectAssets(\Twig_TokenParser $parser)
    {
        $parser->addAssets($this->getAssets());
    }
    public function getAssets()
    {
        $resources = array();
        $finder = new Finder();
        $finder->files()->name('assetic_injector.json');
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            if (file_exists($bundle->getPath().'/Resources/config/')){
                $finder->in($bundle->getPath().'/Resources/config/');
            }
        }
        $injectArray = array();
        foreach($finder as $file) {
            $json = file_get_contents($file);

            if (is_array(json_decode($json, true))) {
                $injectArray = array_merge($injectArray, json_decode($json, true));
            }
        }

        foreach ($injectArray as $engine => $assets) {

            $engine = $this->container->get('assetic_injector.'.$engine);
            $engine->compute($assets);
            $resources = array_merge_recursive($resources, $engine->getResources());
        }

        return $resources;
    }
}
