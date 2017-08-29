<?php

namespace App\Middleware;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;

class FileFilterMiddleware extends Middleware

{
    protected $router;
    protected $flash;
    protected $view;
    protected $allowedFiles = ['image/png', 'image/jpeg'];

    public function __construct(Twig $view, Router $router, Flash $flash)
    {
        $this->view = $view;
        $this->router = $router;
        $this->flash = $flash;
    }

    public function __invoke($request, $response, $next)
    {

        $files = $request->getUploadedFiles();
        $upload_item = $files['upload_item'];
        $uploadFileType = $upload_item->getClientMediaType();

        //dump($uploadFileType);

        if($uploadFileType) {

            if (!in_array($uploadFileType, $this->allowedFiles)) {

                //return $response->withStatus(415);

                $this->flash->addMessage('error', "Wrong File type");

                return $response->withRedirect($this->router->pathFor('flimsys.index'));

            }
        }

        $response = $next($request, $response);

        return $response;

    }

}


