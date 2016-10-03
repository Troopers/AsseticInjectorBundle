<?php

namespace Troopers\AsseticInjectorBundle\AssetsCollector;

use Symfony\Component\Finder\Finder;

/**
 * Assets collector.
 *
 * @author Paul Andrieux <paul@troopers.email>
 */
class Collector
{
    /**
     * Constructor.
     *
     * @param Container $container The container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Inject the assets.
     *
     * @param \Twig_TokenParser $parser
     */
    public function injectAssets(\Twig_TokenParser $parser)
    {
        $parser->addAssets($this->getAssets());
    }

    /**
     * Get the assets for each bundle.
     *
     * @return multitype:
     */
    public function getAssets()
    {
        //this is the app directory.
        //the console is in this directory
        $rootDir = $this->container->get('kernel')->getRootDir();
        $appOverrideDir = '/Resources';

        $resources = [];
        $finder = new Finder();
        $finder->files()->name('assetic_injector.json');
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            //the number of files found in the override path
            $finderCount = 0;
            //the bundle name
            $bundleName = $bundle->getName();

            //the override path
            $overridePath = $rootDir.$appOverrideDir.'/'.$bundleName.'/config/';

            //does the file exists in the app/Resources
            if (file_exists($overridePath)) {
                $finder->in($overridePath);
                $finderCount = $finder->count();
            }

            //if no files were found
            if ($finderCount === 0) {
                if (file_exists($bundle->getPath().'/Resources/config/')) {
                    $finder->in($bundle->getPath().'/Resources/config/');
                }
            }
        }
        $injectArray = [];
        foreach ($finder as $file) {
            $json = file_get_contents($file);
            if (is_array(json_decode($json, true))) {
                $injectArray = array_merge_recursive($injectArray, json_decode($json, true));
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
