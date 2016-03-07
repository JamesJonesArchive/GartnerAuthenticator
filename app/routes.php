<?php
/**
 *
 * Configure URL routes
 * see: http://www.slimframework.com/docs/objects/router.html
 *
 */

// Application Home Page
$app->get('/', 'AuthTransfer\Gartner\Action\HomeAction:dispatch')
    ->setName('homepage');

// Display images from a webservice.  The album id piece of URL is optional.
//$app->get('/example[/{album_id}]', 'AuthTransfer\Gartner\Action\GartnerAction:dispatch')
//    ->setName('displayPlaceholderImages');

// If you need to use POST or PUT you can config the route like this.
//$app->post('/example', 'SlimSkeleton\Action\ExampleAction:runThisMethod')
//    ->setName('examplePOST');

// Gartner handling
$app->get('/gartner', 'AuthTransfer\Gartner\Action\GartnerAction:dispatch')
    ->setName('gartner');