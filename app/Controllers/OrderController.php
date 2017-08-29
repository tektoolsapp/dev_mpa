<?php

namespace App\Controllers;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\Order;
use App\Basket\Basket;
use App\Models\Product;
use App\Models\Address;
use App\Models\Customer;
use App\Validation\Contracts\ValidatorInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Validation\Forms\OrderForm;
use Braintree_Transaction;

class OrderController
{
    protected $basket;
    protected $router;
    protected $validator;

    public function __construct(Basket $basket, Router $router, ValidatorInterface $validator)
    {
        $this->basket = $basket;
        $this->router = $router;
        $this->validator = $validator;
    }

    public function index(Request $request, Response $response, Twig $view)
    {
        $this->basket->refresh();

        if (!$this->basket->subTotal()) {
            return $response->withRedirect($this->router->pathFor('cart.index'));
        }

        return $view->render($response, 'orders/index.twig');
    }

    public function show($hash, Request $request, Response $response, Twig $view, Order $order)
    {

        $order = $order->with(['address'], ['products'])->where('hash', $hash)->first();

        if(!$order){
            return $response->withRedirect($this->router->pathFor('products'));
        }

        return $view->render($response, 'orders/show.twig', [
            'order' => $order
        ]);
    }
    public function create(Request $request, Response $response, Customer $customer, Address $address)
    {
        $this->basket->refresh();

        if (!$this->basket->subTotal()) {
            return $response->withRedirect($this->router->pathFor('cart.index'));
        }

        if(!$request->getParam('payment_method_nonce')){
            return $response->withRedirect($this->router->pathFor('order.index'));
        }

        $validation = $this->validator->validate($request, OrderForm::rules());

        if ($validation->fails()) {
            return $response->withRedirect($this->router->pathFor('order.index'));
        }

        $hash = bin2hex(openssl_random_pseudo_bytes(32));

        $customer = $customer->firstorcreate([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name')
        ]);

        $address = $address->firstorcreate([
            'address1' => $request->getParam('address1'),
            'address2' => $request->getParam('address2'),
            'suburb' => $request->getParam('suburb'),
            'postcode' => $request->getParam('postcode'),
        ]);

        $order = $customer->orders()->create([
           'hash' => $hash,
           'paid' => false,
           'total' => $this->basket->subTotal() + 5,
           'address_id' => $address->id,
        ]);

        $allItems = $this->basket->all();

        dump($allItems);

        dump($this->getQuantities($allItems));

        die();

        $order->products()->saveMany(
            //$this->basket->all(),
            $allItems,
            //$this->getQuantities($this->basket->all())
            $this->getQuantities($allItems)

        );

        $result = Braintree_Transaction::sale([
            'amount' => $this->basket->subTotal() + 5,
            'paymentMethodNonce' => $request->getParam('payment_method_nonce'),
            'options' => [
                'submitForSettlement' => true,
            ]
        ]);

        $event = new \App\Events\OrderWasCreated($order, $this->basket);

        if(!$result->success){
            $event->attach(new \App\Handlers\RecordFailedPayment);
            $event->dispatch();

            return $response->withRedirect($this->router->pathFor('order.index'));
        }

        $event->attach([
            new \App\Handlers\MarkOrderPaid,
            new \App\Handlers\RecordSuccessfulPayment($result->transaction->id),
            new \App\Handlers\UpdateStock,
            new \App\Handlers\EmptyBasket,
        ]);

        $event->dispatch();

        return $response->withRedirect($this->router->pathFor('order.show', [
            'hash' => $hash,
        ]));
    }

    protected function getQuantities($items)
    {
        $quantities = [];

        foreach ($items as $item) {
            $quantities[] = ['quantity' => $item->quantity];
        }

        return $quantities;
    }

}