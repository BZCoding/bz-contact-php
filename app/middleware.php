<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// IP Address middleware
// http://www.slimframework.com/docs/cookbook/ip-address.html
$app->add(new RKA\Middleware\IpAddress(false, []));

// Add CSRF to public form, if enabled
if ($container->get('settings')['csrfToken'] !== false) {
    $app->add(function ($request, $response, $next) {
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $request->getAttribute($csrfNameKey);
        $csrfValue = $request->getAttribute($csrfValueKey);
        if (!empty($csrfName) && !empty($csrfValue)) {
            $this->form->setToken([
                $csrfNameKey => $csrfName,
                $csrfValueKey => $csrfValue
            ]);
        }
        $response = $next($request, $response);
        return $response;
    });

    // CSRF protection
    $app->add($container->get('csrf'));
}
