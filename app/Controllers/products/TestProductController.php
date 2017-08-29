<?php
namespace App\Controllers\Products;

use App\Controllers\Controller;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
use App\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestProductController extends Controller
{
    protected $router;
    protected $validator;
    protected $flash;
    protected $view;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash, Twig $view)
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;
        $this->view = $view;

    }

    public function test(Request $request, Response $response, Product $product)
    {
        $products = $product->get();
        //dump($products);
        return $this->view->render($response, 'products/products.test.twig', [
            'products' => $products,
            'js_script' => 'renewals'
        ]);
    }
}