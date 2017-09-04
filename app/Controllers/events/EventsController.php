<?php

namespace App\Controllers\Events;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Events;
use App\Models\Contacts;
use App\Models\Schedule;
use App\Validation\Contracts\ValidatorInterface;
use App\Validation\Forms\EventForm;
use App\Validation\Forms\ScheduleForm;
use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;

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

    public function eventCalendar(Request $request, Response $response, Twig $view, Flash $flash)
    {
        //$events = $this->events->all();

        return $view->render($response, 'events/event.calendar.twig', [
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

    public function attendees($id, $conf, Request $request, Response $response, Contacts $contacts)
    {

        //dump($conf);

        $attendee_options = DB::table('event_attendee_options')->get();

        $event_attendee_options = $this->events->select('event_attendance_options')->where('id', $id)->get()->first();

        $form_options = $event_attendee_options->event_attendance_options;

        //dump($form_options);
        //die();

        if($conf == 'Y'){
            //GET THE CONFIRMED ATTENDANCE LIST
            $raw_attendees = DB::table('event_attendees')->where('event_id', $id)->get();
            $attendees = array();

            foreach($raw_attendees as $attendee){
                //GET THE CONTACT DETAILS FOR EACH LISTEE
                $contact_dets = $contacts->where('id',$attendee->contact_id)->get()->first();
                array_push($attendees, array('id' => $attendee->id, 'fullname' => $contact_dets->fullname, 'email' => $contact_dets->email));
            }

            $filters = $request->getQueryParams();
            $currentPage = $filters['page'];
            $perPage = 30;

            $arr = $attendees;
            $offset = ($currentPage * $perPage) - $perPage;
            $arr_splice = array_slice($arr, $offset, $perPage, true);
            $paginator = new Paginator($arr_splice, count($arr), $perPage, $currentPage, [
                'path' => Paginator::resolveCurrentPath()
            ]);
        }

        return $this->view->render($response, 'events/event.attendees.twig', [
            'display_type' => $conf,
            'attendees' => $paginator,
            'event_id' => $id,
            'event_attendee_options' => (array)json_decode($form_options),
            'options' => $attendee_options,
            'js_script' => 'events'
        ]);
    }

    public function buildAttendees($id, Request $request, Response $response, Contacts $contacts)
    {
        $_SESSION['current_page'] = 1;
        $posting_array = $request->getParams();
        $_SESSION['event_attendee_params'] = $posting_array;

        //BUILD THE WHERE QUERY ELEMENTS
        //MEMBER SELECTION

        $form_options = array();

        $member_conditions = array();

        foreach ($posting_array as $key => $value) {
            if (substr($key, 0, 1) == 'M') {
                $option_array = explode("|", $key);
                $form_options[] = $value;
                array_push($member_conditions, array($option_array[1] => $option_array[2]));
            }
        }

        $query = $contacts
            ->leftJoin('members', 'members.id', '=', 'contacts.members_id');

        foreach($member_conditions as $key => $value)
            for($i=0 ; $i<sizeof($member_conditions); $i++ )
        {
                foreach($member_conditions[$i] as $key => $value){
                    $query->orWhere("members.".$key, '=', $value);
                }
        }

        if(sizeof($member_conditions) > 0) {

            $member_attendees = $query->select(
                'contacts.members_id as entity_id',
                'members.member_type as option_type',
                'contacts.fullname',
                'contacts.email'
            )->orderBy('contacts.fullname', 'ASC')->get();

            $num_members = $member_attendees->count();
            //dump("members", $num_members);

        } else {
            $member_attendees = [];
        }

        //STAKEHOLDER SELECTION

        $stakeholder_conditions = array();

        //BUILD THE WHERE QUERY ELEMENTS
        foreach ($posting_array as $key => $value) {
            if (substr($key, 0, 1) == 'S') {
                $option_array = explode("|", $key);
                $form_options[] = $value;
                array_push($stakeholder_conditions, array($option_array[1] => $option_array[2]));
            }
        }

        $query = $contacts
            ->leftJoin('stakeholders', 'stakeholders.id', '=', 'contacts.stakeholders_id');

        foreach($stakeholder_conditions as $key => $value)
            for($i=0 ; $i<sizeof($stakeholder_conditions); $i++ )
            {
                foreach($stakeholder_conditions[$i] as $key => $value){
                    $query->orWhere("stakeholders.".$key, '=', $value);
                }
            }

        if(sizeof($stakeholder_conditions) > 0) {

            $stakeholder_attendees = $query->select(
                'contacts.stakeholders_id as entity_id',
                'stakeholders.member_type as option_type',
                'contacts.fullname',
                'contacts.email'
            )->orderBy('contacts.fullname', 'ASC')->get();

            $num_stakeholders = $stakeholder_attendees->count();
            //dump("sthldrs", $num_stakeholders);

        } else {
            $stakeholder_attendees = [];
        }

        //FAILED MERGE SCRIPT
        //$attendees = $member_attendees->merge($stakeholder_attendees);
        //$num_attendees = $attendees->count();
        //dump("att", $num_attendees);

        //SAVE THE EVENT ATTENDEE (FORM) OPTIONS
        $this->events->where('id', $id)
            ->update([
                'event_attendance_options' => json_encode($form_options)
            ]);

        $collection = collect();

        if(sizeof($member_attendees) > 0) {

            foreach ($member_attendees as $members) {
                $collection->push($members);
            }

        }

        if(sizeof($stakeholder_attendees) > 0) {

            foreach ($stakeholder_attendees as $stakeholders) {
                $collection->push($stakeholders);
            }
        }

        //dump($collection);

        //$num_collection = $collection->count();
        //dump("coll", $num_collection);
        //die();

        if(sizeof($collection) > 0) {

            $currentPage = 1;
            $perPage = 30;

            $arr = $collection->toArray();
            $offset = ($currentPage * $perPage) - $perPage;
            $arr_splice = array_slice($arr, $offset, $perPage, true);
            $paginator = new Paginator($arr_splice, count($arr), $perPage, $currentPage);

            return $response->withJson($paginator);

        } else {
            return 'none';
        }

    }

    public function deleteAttendees($id, Request $request, Response $response, Contacts $contacts)
    {
        $attendees = $request->getParam('contacts');

        $list_array = array();

        foreach ($attendees as $value) {
            array_push($list_array, array("id" => $value));
        }

        dump($list_array);

        DB::table('event_attendees')->whereIn('id', $list_array)->delete();

        //CHECK REMAINING
        $remaining_attendees = DB::table('event_attendees')->where('event_id', $id)->get();

        dump($remaining_attendees->count());
        die();

        if(sizeof($remaining_attendees) > 0) {
            $currentPage = 1;
            $perPage = 30;
            $arr = $remaining_attendees->toArray();
            $offset = ($currentPage * $perPage) - $perPage;
            $arr_splice = array_slice($arr, $offset, $perPage, true);
            $paginator = new Paginator($arr_splice, count($arr), $perPage, $currentPage);

            return $response->withJson($paginator);
        } else {
            //IF ALL ATTENDEES ARE DELETED, RESET THE EVENT ATTENDEES TO Y
            $this->events->where('id', $id)
                ->update([
                    'event_attendance_confirmed' => 'N'
                ]);

            return 'none';
        }

    }

    public function confirmAttendees($id, Request $request, Response $response, Contacts $contacts)
    {
        $posting_array = $request->getParams();

        //BUILD THE WHERE QUERY ELEMENTS

        //MEMBER SELECTION
        $member_conditions = array();

        foreach ($posting_array as $key => $value) {
            if (substr($key, 0, 1) == 'M') {
                $option_array = explode("|", $key);
                $form_options[] = $value;
                array_push($member_conditions, array($option_array[1] => $option_array[2]));
            }
        }

        $query = $contacts
            ->leftJoin('members', 'members.id', '=', 'contacts.members_id');

        foreach($member_conditions as $key => $value)
            for($i=0 ; $i<sizeof($member_conditions); $i++ )
            {
                foreach($member_conditions[$i] as $key => $value){
                    $query->orWhere("members.".$key, '=', $value);
                }
            }

        if(sizeof($member_conditions) > 0) {

            $member_attendees = $query->select(
                'contacts.id',
                'contacts.members_id as entity_id',
                'members.member_type as option_type',
                'contacts.fullname',
                'contacts.email'
            )->orderBy('contacts.fullname', 'ASC')->get();

            $num_members = $member_attendees->count();
            //dump("members", $num_members);

        } else {
            $member_attendees = [];
        }

        //STAKEHOLDER SELECTION
        $stakeholder_conditions = array();

        //BUILD THE WHERE QUERY ELEMENTS
        foreach ($posting_array as $key => $value) {
            if (substr($key, 0, 1) == 'S') {
                $option_array = explode("|", $key);
                $form_options[] = $value;
                array_push($stakeholder_conditions, array($option_array[1] => $option_array[2]));
            }
        }

        $query = $contacts
            ->leftJoin('stakeholders', 'stakeholders.id', '=', 'contacts.stakeholders_id');

        foreach($stakeholder_conditions as $key => $value)
            for($i=0 ; $i<sizeof($stakeholder_conditions); $i++ )
            {
                foreach($stakeholder_conditions[$i] as $key => $value){
                    $query->orWhere("stakeholders.".$key, '=', $value);
                }
            }

        if(sizeof($stakeholder_conditions) > 0) {

            $stakeholder_attendees = $query->select(
                'contacts.id',
                'contacts.stakeholders_id as entity_id',
                'stakeholders.member_type as option_type',
                'contacts.fullname',
                'contacts.email'
            )->orderBy('contacts.fullname', 'ASC')->get();

            $num_stakeholders = $stakeholder_attendees->count();
            //dump("sthldrs", $num_stakeholders);

        } else {
            $stakeholder_attendees = [];
        }

        //SAVE THE EVENT ATTENDEE (FORM) OPTIONS
        $this->events->where('id', $id)
            ->update([
                'event_attendance_options' => json_encode($form_options)
            ]);

        $collection = collect();

        if(sizeof($member_attendees) > 0) {
            foreach ($member_attendees as $members) {
                $collection->push($members);
            }
        }

        if(sizeof($stakeholder_attendees) > 0) {
            foreach ($stakeholder_attendees as $stakeholders) {
                $collection->push($stakeholders);
            }
        }

        if(sizeof($collection) > 0) {
            $list_array = array();

            foreach ($collection as $list_contact) {
                array_push($list_array, array("event_id" => $id, "contact_id" => $list_contact->id));
            }

            DB::table('event_attendees')->insert($list_array);

            $this->events->where('id', $id)
                ->update([
                    'event_attendance_confirmed' => 'Y'
                ]);

            return 'ok';
        } else {
            return 'failed';
        }

    }

    public function displayAttendees($id, Request $request, Response $response, Contacts $contacts)
    {
        $filters = $request->getQueryParams();
        $currentPage = $filters['page'];
        $posting_array = $_SESSION['event_attendee_params'];
        $member_conditions = array();

        $form_options = array();

        //BUILD THE WHERE QUERY ELEMENTS
        foreach ($posting_array as $key => $value) {
            if (substr($key, 0, 1) == 'M') {
                $option_array = explode("|", $key);
                $form_options[] = $value;
                array_push($member_conditions, array($option_array[1] => $option_array[2]));
            }
        }

        $query = $contacts
            ->leftJoin('members', 'members.id', '=', 'contacts.members_id');

        foreach($member_conditions as $key => $value)
            for($i=0 ; $i<sizeof($member_conditions); $i++ )
            {
                foreach($member_conditions[$i] as $key => $value){
                    $query->orWhere("members.".$key, '=', $value);
                }
            }

        if(sizeof($member_conditions) > 0) {

            $member_attendees = $query->select(
                'contacts.members_id as entity_id',
                'members.member_type as option_type',
                'contacts.fullname',
                'contacts.email'
            )->orderBy('contacts.fullname', 'ASC')->get();

            $num_members = $member_attendees->count();
            //dump("members", $num_members);
        } else {
            $member_attendees = [];
        }

        $stakeholder_conditions = array();

        //BUILD THE WHERE QUERY ELEMENTS
        foreach ($posting_array as $key => $value) {
            if (substr($key, 0, 1) == 'S') {
                $option_array = explode("|", $key);
                $form_options[] = $value;
                array_push($stakeholder_conditions, array($option_array[1] => $option_array[2]));
            }
        }

        $query = $contacts
            ->leftJoin('stakeholders', 'stakeholders.id', '=', 'contacts.stakeholders_id');

        foreach($stakeholder_conditions as $key => $value)
            for($i=0 ; $i<sizeof($stakeholder_conditions); $i++ )
            {
                foreach($stakeholder_conditions[$i] as $key => $value){
                    $query->orWhere("stakeholders.".$key, '=', $value);
                }
            }

        if(sizeof($stakeholder_conditions) > 0) {

            $stakeholder_attendees = $query->select(
                'contacts.stakeholders_id as entity_id',
                'stakeholders.member_type as option_type',
                'contacts.fullname',
                'contacts.email'
            )->orderBy('contacts.fullname', 'ASC')->get();

            $num_stakeholders = $stakeholder_attendees->count();
            //dump("sthldrs", $num_stakeholders);

        } else {
            $stakeholder_attendees = [];
        }

        $collection = collect();

        if(sizeof($member_attendees) > 0) {

            foreach ($member_attendees as $members) {
                $collection->push($members);
            }
        }

        if(sizeof($stakeholder_attendees) > 0) {

            foreach ($stakeholder_attendees as $stakeholders) {
                $collection->push($stakeholders);
            }

        }

        //$num_collection = $collection->count();
        //dump("collection", $num_collection);

        if(sizeof($collection) > 0) {

            //$currentPage = 1;
            $perPage = 30;

            $arr = $collection->toArray();
            $offset = ($currentPage * $perPage) - $perPage;
            $arr_splice = array_slice($arr, $offset, $perPage, true);
            //$paginator = new Paginator($arr_splice, count($arr), $perPage, $currentPage);
            //$paginator->setPath('event.display.attendees');
            $paginator = new Paginator($arr_splice, count($arr), $perPage, $currentPage, [
                'path' => Paginator::resolveCurrentPath()
            ]);

        } else {
            $paginator = 0;
        }

        $attendee_options = DB::table('event_attendee_options')->get();

        //SAVE THE EVENT ATTENDEE (FORM) OPTIONS
        $this->events->where('id', $id)
            ->update([
            'event_attendance_options' => json_encode($form_options)
        ]);

        //$this->flash->addMessage('info', 'Event details were successfully updated!');

        return $this->view->render($response, 'events/event.attendees.twig', [
            'display_type' => 'N',
            'event_id' => $id,
            'event_attendee_options' => $form_options,
            'options' => $attendee_options,
            'attendees' => $paginator,
            'js_script' => 'events'
        ]);
    }

    public function fetchSchedule(Request $request, Response $response, Twig $view, Flash $flash, Schedule $schedule)
    {
        $params = $request->getQueryParams();
        $start = $params['start'];
        $start_date = strtotime($start);
        $start_date = date('d-m-Y',$start_date);
        $end = $params['end'];
        $end_date = strtotime($end);
        $end_date = date('d-m-Y',$end_date);

        $scheduled = $schedule
            ->where('start_date', '>=', Carbon::createFromFormat('d-m-Y H:i:s', $start_date . "00:00:00"))
            ->where('start_date', '<=', Carbon::createFromFormat('d-m-Y H:i:s', $end_date . "23:59:59"))
            ->orderBy('start_date', 'asc')
            ->get();

        //dump($scheduled);
        //die();

        $schedule_array = array();

        foreach ($scheduled as $item) {
            $item_start_date = strtotime($item->start_date);
            $new_start_date = date('d-m-Y', $item_start_date);
            $item_start_time = date("H:i:s", strtotime($item->start_time));
            $compile_start_date = Carbon::createFromFormat('d-m-Y H:i:s', $new_start_date . $item_start_time);
            $use_start_date = $compile_start_date->toIso8601String();
            $compile_end_date = Carbon::parse($compile_start_date)->addHours($item->hours)->addMinutes($item->mins);
            $use_end_date = $compile_end_date->toIso8601String();

            array_push($schedule_array, array(
                "id" => $item->id,
                "resourceId" => $item->resource,
                "start" => $use_start_date,
                "end" => $use_end_date,
                "title" => $item->title,
                "description" => $item->schedule_description,
                "address" => 'home',
                "task" => "to do",
                "place" => 'whereever'
            ));

        }

        //dump($schedule_array);
        //die();

        return $response->withJson($schedule_array);

    }

    public function addSchedule(Request $request, Response $response, Schedule $schedule)
    {
        $time = ($request->getParam('hours')) * 60 + $request->getParam('mins');
        $_SESSION['duration'] = $time;

        $validation = $this->validator->validate($request, ScheduleForm::rules());

        if ($validation->fails()) {
            $return_array = array(
                'errors' => $_SESSION['errors']
            );

            return json_encode($return_array);

        } else {

            $start_date = date("Y-m-d H:i:s", strtotime($request->getParam('start_date')));

            if(empty($request->getParam('hours'))){
                $hours = 0;
            } else {
                $hours = $request->getParam('hours');
            }

            if(empty($request->getParam('mins'))){
                $mins = 0;
            } else {
                $mins = $request->getParam('mins');
            }

            $schedule
                ->firstorcreate([
                    'title' => $request->getParam('event_title'),
                    'schedule_description' => $request->getParam('event_description'),
                    'start_date' => $start_date,
                    'start_time' => $request->getParam('start_time'),
                    'hours' => $hours,
                    'mins' => $mins,
                    'resource' => $request->getParam('resource'),
                ]);

            //$this->flash->addMessage('info', 'Event Schedule details were successfully updated!');

            return 'ok';

        }

    }

    public function editSchedule($id, Request $request, Response $response, Schedule $schedule)
    {
        $time = ($request->getParam('hours')) * 60 + $request->getParam('mins');
        $_SESSION['duration'] = $time;

        $validation = $this->validator->validate($request, ScheduleForm::rules());

        if ($validation->fails()) {
            $return_array = array(
                'errors' => $_SESSION['errors']
            );

            return json_encode($return_array);

        } else {

            $start_date = date("Y-m-d H:i:s", strtotime($request->getParam('start_date')));

            if(empty($request->getParam('hours'))){
                $hours = 0;
            } else {
                $hours = $request->getParam('hours');
            }

            if(empty($request->getParam('mins'))){
                $mins = 0;
            } else {
                $mins = $request->getParam('mins');
            }

            $schedule->where('id', $id)
                ->update([
                    'title' => $request->getParam('event_title'),
                    'schedule_description' => $request->getParam('event_description'),
                    'start_date' => $start_date,
                    'start_time' => $request->getParam('start_time'),
                    'hours' => $hours,
                    'mins' => $mins,
                    'resource' => $request->getParam('resource'),
                ]);

            //$this->flash->addMessage('info', 'Event Schedule details were successfully updated!');

            return 'ok';

        }

    }

    public function planning($id, Request $request, Response $response)
    {
        return $this->view->render($response, 'events/event.planning.twig', [
            'js_script' => 'events',
        ]);
    }

}