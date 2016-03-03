<?php
/**
 *
 * Configure Dependency Injection
 * see: http://www.slimframework.com/docs/concepts/di.html
 *
 **/

$container = $app->getContainer();
$settings = $container->settings;

// Twig templating engine: http://twig.sensiolabs.org/documentation
$container['view'] = function ($c) use ($settings) {

    $default_config = [
      'cache'            => '../cache',
      'debug'            => false,
      'auto_reload'      => true,
      'autoescape'       => true,
      'strict_variables' => false
    ];

    $path = $settings['view']['template_path'] ?? '../templates';
    $twig_settings = $settings['view']['twig'] ?? $default_config;

    // Create Twig object and add Slim extensions
    $view = new \Slim\Views\Twig($path, $twig_settings);
    $view->addExtension(new Slim\Views\TwigExtension($c->router, $c->request->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Monolog logging service: https://github.com/Seldaek/monolog
$container['logger'] = function ($c) use ($settings) {
    $name = $settings['logger']['name'] ?? __FILE__;
    $location = $settings['logger']['path'] ?? '../logs/application.log';
    $log_level = $settings['logger']['level'] ?? 'DEBUG';

    $logger = new \Monolog\Logger($name);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($location, \Monolog\Logger::toMonologLevel($log_level)));

    return $logger;
};

/**
 * -----------------------------------------------------------------------------
 * Service Layer
 * -----------------------------------------------------------------------------
 *
 * These classes provide the business logic to interact with services and build the information necessary for
 * views.  View routes.php to see how these classes are mapped to URLs
 */

$container['gartner'] =  function ($c) {
    return new \USF\IdM\AuthTransfer\Gartner\Service\GartnerService($c->logger, $c->settings);
};

/**
 * -----------------------------------------------------------------------------
 * Action factories (Controllers)
 * -----------------------------------------------------------------------------
 *
 * These classes provide the business logic to interact with services and build the information necessary for
 * views.  All of them get the view, logging, and settings components along with any other neccesary services passed in
 * as arguments to the class constructors.
 *
 * View routes.php to see how these classes are mapped to URLs
 */

// Application Home Page
$container['AuthTransfer\Gartner\Action\HomeAction'] = function ($c) {
    return new \USF\IdM\AuthTransfer\Gartner\Action\HomeAction($c->view, $c->logger, $c->settings);
};

//// Example Controller with a Service
$container['AuthTransfer\Gartner\Action\GartnerAction'] = function ($c) {
    return new \USF\IdM\AuthTransfer\Gartner\Action\GartnerAction($c->view, $c->logger, $c->settings, $c->gartner);
};
