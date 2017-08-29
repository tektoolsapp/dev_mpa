<?php

namespace App\Middleware;

use Slim\Views\Twig;
use Slim\Csrf\Guard;

class CsrfViewMiddleware
{

    protected $view;
    protected $csrf;

    public function __construct(Twig $view, Guard $guard)
    {
        $this->view = $view;

        $guard->setPersistentTokenMode(true);
        $this->csrf = $guard;

    }

    public function __invoke($request, $response, $next)
    {

        $request = $this->csrf->generateNewToken($request);

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

        //dump($this->csrf);

        $this->view->getEnvironment()->addGlobal('csrf', [

            'field' => '
                <input type="hidden" name="'. $this->csrf->getTokenNameKey() .'" id="'. $this->csrf->getTokenNameKey() .'" value="'. $name .'">
                <input type="hidden" name="'. $this->csrf->getTokenValueKey() .'" id="'. $this->csrf->getTokenValueKey() .'" value="'. $value .'">
            ',

        ]);

        $response = $next($request, $response);

        return $response;

    }

}