<?php

namespace App\Controllers\Events;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Events;
use App\Validation\Contracts\ValidatorInterface;
use App\Validation\Forms\EventForm;

class EventsController
{
    protected $router;
    protected $view;
    protected $flash;
    protected $validator;
    protected $events;

    public function __construct(Router $router, Twig $view, Flash $flash, ValidatorInterface $validator, Events $events )
    {
        $this->router = $router;
        $this->view = $view;
        $this->flash = $flash;
        $this->validator = $validator;
        $this->events = $events;
    }

    public function index(Request $request, Response $response, Twig $view, Flash $flash)
    {
        $events = $this->events->all();

        return $view->render($response, 'events/events.index.twig', [
            'events' => $events,
            'js_script' => 'events'
        ]);
    }

    public function newEvent(Request $request, Response $response)
    {
        return $this->view->render($response, 'events/event.update.twig', [
            'mode' => 'add',
            'js_script' => 'events',
        ]);
    }

    public function add(Request $request, Response $response)
    {
        $_SESSION['min_participants'] = (int)$request->getParam('min_participants');

        $validation = $this->validator->validate($request, EventForm::rules());

        if ($validation->fails()) {
            return 'errors';
        } else {

            $event = $this->events->firstorcreate([
                'event_name' => $request->getParam('event_name'),
                'event_description' => $request->getParam('event_description'),
                'event_type' => $request->getParam('event_type'),
                'event_date' => $request->getParam('event_date'),
                'event_start' => $request->getParam('event_start'),
                'min_participants' => (int)$request->getParam('min_participants'),
                'max_participants' => (int)$request->getParam('max_participants')
            ]);

            $this->flash->addMessage('info', 'New Event has been added!');

            return 'ok';
        }
    }

    public function get($id, Request $request, Response $response)
    {
        $event = $this->events->where('id', $id)->get()->first();

        return $this->view->render($response, 'events/event.update.twig', [
            'mode' => 'edit',
            'event' => $event,
            'js_script' => 'events',
        ]);
    }

    public function edit($id, Request $request, Response $response)
    {
        //dump($request->getParams());
        $_SESSION['min_participants'] = (int)$request->getParam('min_participants');

        $validation = $this->validator->validate($request, EventForm::rules());

        if ($validation->fails()) {
            return 'errors';
        } else {
            $this->events->where('id', $id)
                ->update([
                    'event_name' => $request->getParam('event_name'),
                    'event_description' => $request->getParam('event_description'),
                    'event_type' => $request->getParam('event_type'),
                    'event_date' => $request->getParam('event_date'),
                    'event_start' => $request->getParam('event_start'),
                    'min_participants' => (int)$request->getParam('min_participants'),
                    'max_participants' => (int)$request->getParam('max_participants')
                ]);

            $this->flash->addMessage('info', 'Event details were successfully updated!');

            return 'ok';

        }
    }

    public function attendees($id, Request $request, Response $response)
    {
        return $this->view->render($response, 'events/event.attendees.twig', [
            'js_script' => 'events',
        ]);
    }

    public function planning($id, Request $request, Response $response)
    {
        return $this->view->render($response, 'events/event.planning.twig', [
            'js_script' => 'events',
        ]);
    }

}