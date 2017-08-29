<?php

namespace App\Controllers;

use App\Basket\Exceptions\QuantityExceededException;
use Slim\Router;
use Slim\Views\Twig;
use App\Basket\Basket;
use App\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CartController
{
    protected $basket;
    protected $product;

    public function __construct(Basket $basket, Product $product)
    {
        $this->basket = $basket;
        $this->product = $product;
    }

    public function index(Request $request, Response $response, Twig $view)
    {
        dump($this->basket->all());
        //die();

        $this->basket->refresh();
        return $view->render($response, 'cart/index.twig');
    }

    public function add($slug, $quantity, Request $request, Response $response, Router $router )
    {
        $product = $this->product->where('slug', $slug)->first();

        if (!$product) {
            return $response->withRedirect($router->pathFor('home'));
        }

        try {
            $this->basket->add($product, $quantity);
        } catch(QuantityExceededException $e){
            //
        }

        return $response->withRedirect($router->pathFor('cart.index'));
    }

    public function update($slug, Request $request, Response $response, Router $router, Order $order)
    {
        $product = $this->product->where('slug', $slug)->first();

        if (!$product) {
            return $response->withRedirect($router->pathFor('home'));
        }

        //die('update');

        try {
            $this->basket->update($product, $request->getParam('quantity'));
        } catch(QuantityExceededException $e){
            //
        }

        return $response->withRedirect($router->pathFor('cart.index'));
    }
}