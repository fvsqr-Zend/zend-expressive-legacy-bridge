# Zend Expressive Legacy Bridge
Zend Expressive Legacy Bridge is a library which allows legacy apps based on Zend Framework 1 and Symfony 1 to be run as a middleware layer in Zend Expressive. The goal is to provide a REST API for these application without code changes of the original app.
## Prerequisites
Zend Expressive Legacy Bridge requires PHP 7
## Installation
To install with composer
```
composer require janatzend/zend-expressive-legacy-bridge
```
## Loading Legacy Bridge
Legacy Bridge is providing several services and middleware implementations. In order to load them in to the Zend Expressive application one has to insert the array provided by the ConfigProvider service into the ```dependencies``` section of the config container.
### Example
If the the configuration is created according to the documentation here https://zendframework.github.io/zend-expressive/features/container/zend-servicemanager/#configuration-driven-container a file called ```legacybridge.global.php``` should be placed in the  ```config/autoload``` directory with the following content:
```
<?php
use Zend\Expressive\LegacyBridge\ConfigProvider;

return (new ConfigProvider())();
```
This code is also used in the projects https://bitbucket.org/account/user/janatzendteam/projects/ELM
 and https://bitbucket.org/account/user/janatzendteam/projects/EX.

# Usage
As there's probably no one-size-fits-all solution for the modernization problem, the Legacy Bridge is designed to provide a rich feature set usable in most cases, while allowing to modify all of the components so that they fit to the needs. That said, it's not a one-click or one-step solution, but an addition to the framework giving the option to extend the legacy functionality in a flexible way.
The Legacy Bridge is responsible for providing a middleware implementation in which (nearly) the full execution flow of the ZF1 or Symfony 1 MVC application is encapsulated. However there's some manual work to do.
In general one has to provide three different components:
## Initialization
Initialization details of the legacy apps have to be customizable. For example the setting of the include path, the path to the configuration file, environment variables and more. The Legacy Bridge provides an option to define a service in which these details can be specified. In general it's more or less a copy and paste from the original index.php.
See in the Examples section below how to implement.
## Routing
An important question which has to be answered before utilizing the Legacy Bridge is: "How should the routing for the new REST API look like?" There are several options:
* prefix the path in the URL with something like ```/api``` or ```/rest```
* make use of the Accept-Header: if it is ```application/json``` then a JSON response from the API should provided, otherwise the normal HTML output
* combination of both above

Example 1 shows how to use the Accept-Header, Example 2 is programmed to work with an prefix path ```/rest```
## Hydrator
As the Legacy Bridge is not a screen scraper, the functionality of the bridge is taking care of disabling the rendering of the View component of the legacy app. Additionally redirects have to be catched/disabled. The View object is processed by a Hydrator or a Hydrator chain, which is responsible to output only the needed data in a structured format. Thanks to the hydrator, data can be filtered and especially objects that are passed to the view object in the legacy app can be resolved on a customized basis.

# Examples
As there is not the one and only best practice for using the Legacy Bridge, for your convenience two examples have been provided, that are both using the same Legacy Bridge core but in a very different way and setup.
Both examples have in common that the legacy app is running side-by-side to the new API functionality.

## Zend Framework 1 application MyTracking
MyTracking is an Zend Framework 1 application which has been developed originally as a PoC in a customer engagement
several years ago. MyTracking is a CRUD implementation for administrating (tracking) images in the database.
Additionaly PDF's can be generated with the stored data.

MyTracking is also able to run on IBM i with DB2
### Initialization
Zend Framework 1 applications need some information before the bootstrapping can be started. For example the include_path is normally set in the ```index.php```. But also environment variables like the information whether it's a production or a development system is done with constant at a very early stage. As this should not be done for the Expressive app, the Legacy Bridge allows to execute a callable at the very first step in the Bridge middleware. This callable must be found with the name ```zf1-prereq``` in the config container. In this example a file ```zf1-prereq.global.php``` defines the callable like this:
```
'zf1-prereq' => function() {
    defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    // ...
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../src'),
        // ...
        get_include_path(),
    )));
}
```
### Routing
The ```index.php``` of MyTracking is setting up the Zend Expressive application; the original ```index.php``` has been renamed to ```zf.php```.
#### ApiDecider
In order to provide an API the routes of the original app are not modified. Instead the value of the Accept-Header of the request decides whether the output should be HTML or JSON. The decision is made by the ApiDecider middleware which is placed between ```Zend\Expressive\Application::pipeRoutingmiddleware()``` and ```Zend\Expressive\Application::pipeDispatchMiddleware()```:
```
$app = AppFactory::create($container, $container->get(RouterInterface::class));
$app->pipeRoutingMiddleware();
$app->pipe('/', ApiDecider::class);
$app->pipeDispatchMiddleware();
```
That means that the ApiDecider is becoming active immediate after a successful routing. It checks for the Accept-Header value and if it equals to ```application/json``` it calls the next middleware in the pipeline.
Otherwise it is utilizing the LocationRedirector service and the pipeline execution is stopped. The LocationRedirector is rewriting the path from e.g. ```/tracking/list``` to ```/zf.php/tracking/list``` and is redirecting to this location. The LocationRedirector expects a service called ```pathCreator``` to which a request object is being passed. With its help the URL path can be rewritten. In this example this service is defined in a file ```config/autoload/redirectory.global.php```:
```
<?php
return [
    'dependencies' => [
        'services' => [
            'pathCreator' => function ($req) {
                $url  = $req->getUri();

                return '/zf.php' . $url->getPath();
            }
        ]
    ]
]
```
### API definition
The last definitions in ```index.php```, before actually running the application, are specifying the supported API / REST calls.
```
$app->get('/tracking/list', Zf1\Bridge::class);
$app->delete('/tracking/delete/id/{id:[0-9]+}', Zf1\Bridge::class);
$app->get('/tracking/new', Zf1\Bridge::class);
$app->post('/tracking/new', Zf1\Bridge::class);
```
As one can notice, all of the routes are already available in the legacy app. This is necessary because the route is passed to the MVC and would not find a match if it was different to the original routes. However, with some additional functionality (see Example 2) one can also use a new route thanks to a custom route mapping mechanism.

Altough the definitions give the impression that ```Zf1\Bridge``` is directly called for providing the middleware, this is not correct. Instead ```Zf1\Bridge``` is an identifier for loading the ```Zf1\BridgeFactory```. The reason for this can be found in the Legacy Bridge library code in file ```ConfigProvider.php``` - the Legacy Bridge services are defined here. This mechanism gives the flexibility to overwrite the library core functionality very easy.
### Hydrator
As the View is not rendered, custom Hydrators have to translate the View elements in a structure that can be output as JSON. The Zend_View object is holding all view variables as public instance vars. In the MyTracking example with the route ```/tracking/list``` the view object only contains one value, namely ```list```. Unfortunately the content of ```list``` cannot be output without modifying, as it contains binary data (images are stored in DB) that should not appear in the JSON response. In order to provide a filter functionality, the file ```config/autoload/hydrator.global.php``` has been created. It contains a service that maps a route to a set of view items (in this example we only have one: ```list```) and its corresponding Hydrator implementation:
```
'services' => [
    'hydratorstrategies' => [
        '/tracking/list^GET' => [
            'list' => 'HydratorStrategyTrackingList'
        ],
        // ...
    ]
]
```
The ```hydratorstrategies``` array key is reserved by the Legacy Bridge. ```HydratorStrategyTrackingList``` is pointing to a Hydrator implementation, defined in the same file:
```
'factories' => [
    'HydratorStrategyTrackingList' => function() {
        return new StrategyChain([
            new ClosureStrategy(function ($data) {
                if (!is_a($data, 'ArrayAccess')) return $data;

                return $data->toArray();
            }),

            new ClosureStrategy(function ($data) {
                /* HACK for specific situation, Rowset conatins binary data */
                foreach ($data as $rowNr => $row) {
                    foreach ($row as $key => $value) {
                        if (preg_match('~[^\x20-\x7E\t\r\n]~', $value) > 0) unset($data[$rowNr][$key]);
                    }
                }

                return $data;
            })
        ]);
    }
],
```
The interesting part here is not the actual code, as it is an implementation detail, but the fact that two so called Hydrator strategies are combined to a Hydrator chain; so the data in the ```list``` value is transformed twice.

## Symfony 1 application Jobeet
Jobeet is an application which has been developed in the context of a Symfony 1 online training tutorial.

See: http://symfony.com/legacy/doc/jobeet?orm=Doctrine
### Initialization
The Jobeet application only needs a minimal effort to do the initialization, actually only the ```ProjectConfiguration``` class file has to be included with the correct path. Therefore a file ```config/autoload/sf1-prereq.global.php``` is containing the following code:
```
'sf1-prereq' => function() {
    require_once(dirname(__FILE__).'/../../config/ProjectConfiguration.class.php');
}
```
The Legacy Bridge executes the callable ```sf1-prereq``` at the very first step of the Bridge middleware.
### Routing
In this example the Zend Expressive Application setup is done in a file called ```web/expressive.php```. The original file ```web/index.php``` was not touched.
The new Jobeet API is dependent on a path prefix in the URL: ```/rest```. Therefore a rewrite rule was added in the ```web/.htaccess``` file, which makes sure that ```web/expressive.php``` is called when requesting the new path:
```
RewriteRule ^rest/(.*)$ expressive.php [L,NC]
```
### API definition
The last definitions in ```web/expressive.php``` before actually running the application are specifying the supported API / REST calls.
```
$app->get('/rest/categories/', Bridge::class);
$app->post('/rest/search/',  Bridge::class);
```
Altough the definitions give the impression that ```Bridge``` is called directly called for providing the middleware, this is not correct. Instead ```Bridge``` is an identifier for loading the ```BridgeFactory```. The reason for this can be found in the Legacy Bridge library code in file ```ConfigProvider.php``` - the Legacy Bridge services are defined here. This mechanism gives the flexibility to overwrite the library core functionality very easy.

Another thing which might confuse on a first sight is the fact that the original Jobeet app doesn't provide a functionality to display a list of all (job) categories. However, the first route from above does allow exactly this. Why is this possible? First, the start page of the Jobeet app, or better said the view object from the start page, contains information about the categories which are not displayed. In order to display this information we need a hydrator (see below), but also a mapping from ```/rest/categories/``` to the start page route ```/en/``` to load the correct action in the Bridge middleware. This mapping can be found in file ```config/autoload/route-mapping.global.php```:
```
'route-mapping' => [
    '/rest/categories/^GET' => '/en/',
    // ...
]
```
The Legacy Bridge uses this config item to map a route from the API to the route of the legacy app that provides the necessary information.
### Hydrator
As the View is not rendered, custom Hydrators have to translate the View elements in a structure that can be output as JSON. The view in Symfony 1 is holding all view variables in a varHolder container of an action view object. In the Jobeet example with the route ```/rest/categories/``` resp. ```/en/``` the view object contains a ```categories``` variable. This variable contains object arrays instead of scalar values in a structured format. Therefore a specific hydrator has to be defined in file ```config/autoload/hydrator.global.php``` for transforming the data into the needed format:
```
'services' => [
    'hydratorstrategies' => [
        '/rest/categories/^GET' => [
            'categories' =>  'HydratorStrategyCategories'
        ],
        // ...
    ]
]
```
The ```hydratorstrategies``` array key is reserved by the Legacy Bridge. ```HydratorStrategyCategories``` is pointing to a Hydrator definition in the same file:
```
'factories' => [
    'HydratorStrategyCategories' => HydratorStrategyCategoriesFactory::class,
    // ...
],
```
The specified class is available in the ```src``` folder. This class or the appropriate methods are working with the object arrays in order to filter the needed data.
