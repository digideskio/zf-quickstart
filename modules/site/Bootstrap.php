<?php

namespace site;

use Zend\Config\Config,
    Zend\Di\Configuration,
    Zend\Di\Di,
    Zend\EventManager\StaticEventManager,
    Zend\Stdlib\ResponseDescription as Response,
    Zend\View\Variables as ViewVariables,
    Zend\Mvc\Application,
    Zend\Mvc\Router\Http\TreeRouteStack as Router;

class Bootstrap
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function bootstrap(Application $app)
    {
        $this->setupLocator($app);
        $this->setupRoutes($app);
        $this->setupEvents($app);
    }

    protected function setupLocator(Application $app)
    {
        /**
         * Instantiate and configure a DependencyInjector instance, or 
         * a ServiceLocator, and return it.
         */
        $di         = new Di();
        $definition = $di->definitions();
        $di->instanceManager()->addTypePreference('Zend\Di\Locator', $di);

        $config = new Configuration($this->config->di);
        $config->configure($di);

        $app->setLocator($di);
    }

    protected function setupRoutes(Application $app)
    {
        /**
         * Pull the routing table from configuration, and pass it to the
         * router composed in the Application instance.
         */
        $router = new Router();
        $router->addRoutes($this->config->routes);
        $app->setRouter($router);
    }

    protected function setupEvents(Application $app)
    {
        /**
         * Wire events into the Application's EventManager, and/or setup
         * static listeners for events that may be invoked.
         */
        $di     = $app->getLocator();
        $view   = $di->get('view');
        $url    = $view->plugin('url');
        $url->setRouter($app->getRouter());

        $listener = new View\Listener($view, 'layouts/layout.phtml');
        $listener->setDisplayExceptionsFlag($this->config->display_exceptions);
        $app->events()->attachAggregate($listener);
    }
}
