<?php
// Application middleware

$app->add(function ($request, $response, $next) {
    $access_key = $request->getHeaderLine('HTTP_ACCESS_KEY');
    if (isset($access_key) && $access_key === '123') {
        $response = $next($request, $response);
        return $response;
    } else {
        return $response->withStatus(403)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'status' => 'error',
                'message' => 'You are not authorised for this request'
            ]));
    }
});
