<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// IP Address middleware
// http://www.slimframework.com/docs/cookbook/ip-address.html
$app->add(new RKA\Middleware\IpAddress(false, []));
