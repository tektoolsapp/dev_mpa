<?php

namespace App\Controllers\Products;

/*
use App\Models\Product;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

//use Slim\Views\Twig;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

*/

use App\Controllers\Controller;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Basket\Basket;

class ProductController extends Controller

{

    //public function get($slug, ServerRequestInterface $request, ResponseInterface $response, Twig $view, Router $router, Product $product)

    public function get($request, $response, $slug)
    {

        //$basket = $this->container->Basket;

        //dump($basket);

        //die();


        /*
        $product = $product->where('slug', $slug)->first();

        if (!$product) {
            return $response->withRedirect($router->pathFor('members.list'));
        }

        return $view->render($response, 'products/product.twig', [
            'product' => $product,
        ]);
    }
        */


    /*
    public $quantity = null;

    public function get($request, $response, $slug)
    {

    */

    $product_model = new Product;

        //$product_model->load('outOfStock');

        //dump([$product]);

        //die();

        //$product = Product::where('slug', $slug)->first();

        //$product = $this->product->where('slug', $slug)->first();

        $product = $product_model->where('slug', $slug)->get();

        //$product = Product::where('slug', $slug)->get();

        /*

        //$product = Product::where('slug', $slug)->get();

        //dump($product);

        //die();
        */

        if (!$product) {

            return $response->withRedirect($this->router->pathFor('members.list'));

        } else {

            return $this->view->render($response, 'products/product.twig', [

                'product' => $product,
                'js_script' => 'renewals'

            ]);

        }

    }


}