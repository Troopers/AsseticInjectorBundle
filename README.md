[![AppVentus](https://github.com/AppVentus/AvAlertifyBundle/blob/master/Media/appventus.png)](http://appventus.com)


[![Dependency Status](https://www.versioneye.com/php/appventus:assetic-injector-bundle/dev-master/badge.svg)](https://www.versioneye.com/php/appventus:assetic-injector-bundle/dev-master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d10e1e8e-8bd5-462e-994f-419bcfb7da78/mini.png)](https://insight.sensiolabs.com/projects/d10e1e8e-8bd5-462e-994f-419bcfb7da78)

=============

AsseticInjectorBundle
=======

The AsseticInjectorBundle allows you to automatically include javascripts and stylesheets anywhere in your project.
This bundle will scan every registered bundles and search for an assetic_injector.json file. From this file, the injector will collect the resources and inject them into the assetic engine.
To include them, you have to define the tag (foot, head, custom, actually the one you choose) and add the tag in the wanted assetic block.

## Install

With Composer :


Add this line in your composer.json file :

    "appventus/assetic-injector-bundle": "dev-master"

Declare the bundle in your AppKernel.php:

    public function registerBundles() {
        $bundles = array(
            [...]
            new AppVentus\AsseticInjectorBundle\AvAsseticInjectorBundle(),
            [...]

Then declare an assetic_injector.json in the Resource/config folder of your application or bundle:

    {
        "require_all":
        {
            "javascripts":
            {
                "foot": "@MyBundle/Resources/public/js/myscript.js"
            },
            "stylesheets":
            {
                "head": "@MyBundle/Resources/public/css/mystyle.css"
            }
        }
    }


Now, to include the resources, just add the name of the resource tag (foot, head etc) in your assetic's block.

For example, this code ...

        {% javascripts injector="head"
            '@MyAcmeDemoBundle/Resources/public/jsloremipsumdolorsitamet.js'
         %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}


will inject assetic_injector.json and regarding to the example wrote before equals to :

        {% javascripts injector="head"
            '@MyAcmeDemoBundle/Resources/public/jsloremipsumdolorsitamet.js'
            '@MyBundle/Resources/public/js/myscript.js'
         %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}

So, no more needs to include javascript or stylesheets from assetic injector's ready bundles !
Just add the tag and here we are !

Enjoy !
