[![Troopers](https://cloud.githubusercontent.com/assets/618536/18787530/83cf424e-81a3-11e6-8f66-cde3ec5fa82a.png)](http://troopers.agency/?utm_source=AsseticInjectorBundle&utm_medium=github&utm_campaign=OpenSource)

[![License](https://img.shields.io/packagist/l/troopers/assetic-injector-bundle.svg)](https://packagist.org/packages/troopers/assetic-injector-bundle)
[![Version](https://img.shields.io/packagist/v/troopers/assetic-injector-bundle.svg)](https://packagist.org/packages/troopers/assetic-injector-bundle)
[![Packagist DL](https://img.shields.io/packagist/dt/troopers/assetic-injector-bundle.svg)](https://packagist.org/packages/troopers/assetic-injector-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d10e1e8e-8bd5-462e-994f-419bcfb7da78/mini.png)](https://insight.sensiolabs.com/projects/d10e1e8e-8bd5-462e-994f-419bcfb7da78)
[![Twitter Follow](https://img.shields.io/twitter/follow/troopersagency.svg?style=social&label=Follow%20Troopers)](https://twitter.com/troopersagency)

=============

AsseticInjectorBundle
=======

## Description

The AsseticInjectorBundle allows you to automatically include javascripts and stylesheets anywhere in your project.

This bundle will scan every registered bundles and search for an **assetic_injector.json file**. From this file, the injector will collect the resources and inject them into the assetic engine.
To include them, you have to define a tag (foot, head, custom, ... actually the one you choose) and add the tag in the wanted assetic block.

## Install

With Composer :

Add this line in your composer.json file :

    "troopers/assetic-injector-bundle": "~1.0"

Declare the bundle in your AppKernel.php:

    public function registerBundles() {
        $bundles = array(
            [...]
            new Troopers\AsseticInjectorBundle\TroopersAsseticInjectorBundle(),
            [...]

## How it works

.. **Declare** an assetic_injector.json in the Resource/config folder of your application or bundle.

Within this file, list all the tags and the correspondant resource to inject :

    {
        "require_all":
        {
            "javascripts":
            {
                "head": "@MyBundle/Resources/public/js/myscript.js"
            },
            "stylesheets":
            {
                "head": "@MyBundle/Resources/public/css/mystyle.css"
            }
        }
    }



.. **Include** the correspondant tag in your assetic's block with the code : **injector="tag"**

i.e :

        {% javascripts injector="head"
            '@MyAcmeDemoBundle/Resources/public/jsloremipsumdolorsitamet.js'
         %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}

.. **Results**

The resource associated to the tag is injected with assetic_injector.json.

i.e :

        {% javascripts injector="head"
            '@MyAcmeDemoBundle/Resources/public/jsloremipsumdolorsitamet.js'
            '@MyBundle/Resources/public/js/myscript.js'
         %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}

## Enjoy !

No more needs to include javascript or stylesheets from assetic injector's ready bundles !
Just add the tag and here you are !
