AsseticInjectorBundle
=======

The AsseticInjectorBundle allow you to automaticly include javascripts and stylesheets anywhere in your project

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

Then declare a assetic_injector.json in the Resource/config folder of your application or bundle:

    {
        "require_all":
        {
            "javascripts":
            {
                "resource": "@MyBundle/Resources/public/js/myscript.js"
            },
            "stylesheets":
            {
                "resource": "@MyBundle/Resources/public/css/mystyle.css"
            }
        }
    }


Enjoy !
