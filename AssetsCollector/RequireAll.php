<?php
namespace AppVentus\AsseticInjectorBundle\AssetsCollector;

class RequireAll
{
     protected $resources = array();

    public function compute($item)
    {
        foreach($item as $type => $resource) {
            foreach($resource as $k => $path) {
                $this->resources[$type][$k] = $path;
            }
        }
    }

    public function getResources()
    {
        return $this->resources;
    }
}
