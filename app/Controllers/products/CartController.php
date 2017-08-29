<?php

namespace App\Controllers\Products;

use App\Controllers\Controller;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\Product;
use App\Basket\Basket;
use App\Basket\Exceptions\QuantityExceededException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CartController extends Controller
{
    protected $basket;
    protected $product;

    public function __construct(Basket $basket, Product $product)
    {
        $this->basket = $basket;
        $this->product = $product;
    }

    public function indexX(Request $request, Response $response, Twig $view)
    {
        $this->basket->refresh();

        dump($this->basket);

        die();

        /*
        return $view->render($response, 'cart/index.twig', [
            'js_script' => 'renewals'
        ]);
        */
    }

    public function add($slug, $quantity, Request $request, Response $response, Router $router)
    {
        $product = $this->product->where('slug', $slug)->first();

        if(!$product){
            return $response->withRedirect($router->pathFor('members.list'));
        }

        try {
            $this->basket->add($product, $quantity);
        } catch (QuantityExceededException $e) {
            //
        }

        return $response->withRedirect($router->pathFor('cart.index'));
    }

    public function update($slug, Request $request, Response $response, Router $router)
    {
        $product = $this->product->where('slug', $slug)->first();

        dump("updating:", $product);

        if (!$product) {
            return $response->withRedirect($router->pathFor('home'));
        }

        try {
            $this->basket->update($product, $request->getParam('quantity'));
        } catch (QuantityExceededException $e) {
            //FLASH A MESSAGE & REDIRECT
        }

        //die();
        return $response->withRedirect($router->pathFor('cart.index'));
    }

}