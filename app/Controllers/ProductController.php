<?php

namespace App\Controllers;

use Slim\Views\Twig;
use App\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController
{
    public function index(Request $request, Response $response, Twig $view, Product $product)
    {
        $products = $product->get();
        return $view->render($response, 'products/product.index.twig', [
            'products' => $products
        ]);
    }
}