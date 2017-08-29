<?php

namespace App\Controllers\Members;

use App\Controllers\Controller;
use App\Models\Directory;
use Respect\Validation\Validator as v;

class DirectoryController extends Controller

{

    public function index($request, $response, $args)

    {

        $id = $args['id'];

        $directory = Directory::where([
            ['member_id', '=', $id]
        ])->get()->first();

        $specialist_skills = $this->db->table('specialist_skills')->get();

        //dump($specialist_skills);

        //die();

        return $this->view->render($response, 'directory/directory.edit.twig', [

            'directory' => $directory,
            'skills' => json_decode($directory->skills),
            'skillset' => $specialist_skills,
            'js_script' => 'directory',
            'members_display_status' => $_SESSION['members_display_status'],
            'members_display_name' => $_SESSION['members_display_name']

        ]);

    }

    public function editDirectory($request, $response, $args)
    {
        $id = $args['id'];

        /*
        $validation = $this->validator->validate($request, [
            'business_name' => v::notEmpty()->setName('Business Name'),
            'company_name' => v::notEmpty()->setName('Company Name'),
            'business_abn' => v::notEmpty()->setName('ABN'),
            'business_acn' => v::notEmpty()->setName('ACN'),
            'business_arbn' => v::notEmpty()->setName('ARBN'),
            'checkboxes_licensed_plumber_num' =>v::notEmpty()->setName('Plumber Licence'),
            'checkboxes_other_num' =>v::notEmpty()->setName('Other Text'),
            'member_type' =>v::notEmpty()->NotSelected()->setName('Member Type'),
            'activity_type' =>v::notEmpty()->NotSelected()->setName('Activity Type'),
            'date_joined' => v::notEmpty()->setName('Date Joined'),
            'business_phone' => v::notEmpty()->setName('Business Phone'),
            'business_fax' => v::notEmpty()->setName('Business Fax'),
            'business_address' => v::notEmpty()->setName('Business Address'),
            'business_suburb' => v::notEmpty()->setName('Business Suburb'),
            'business_state' => v::notEmpty()->setName('Business State'),
            'business_postcode' => v::notEmpty()->setName('Business Post Code'),
            'mailing_address' => v::notEmpty()->setName('Mailing Address'),
            'mailing_suburb' => v::notEmpty()->setName('Mailing Suburb'),
            'mailing_state' => v::notEmpty()->setName('Mailing State'),
            'mailing_postcode' => v::notEmpty()->setName('Mailing Post Code'),

        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('members.edit', ['id' => $id]));
        }

        */

        $posting_array = $request->getParams();

        $skills = array();

        foreach ($posting_array as $key => $value) {

            if(substr($key,0,6) == 'skill_'){
                //array_push($skills, array('skill' => $value));
                $skills[] = $value;
            }

        }

        //dump($skills);

        //die();

        $post_skills = json_encode($skills);

        Directory::where('id', $request->getParam('id'))
            ->update([

                'trading_name' => $request->getParam('trading_name'),
                'skills' => $post_skills

            ]);

        $this->flash->addMessage('info', 'Member Directory details were successfully updated for '. $request->getParam('trading_name') . '!');

        return $response->withRedirect($this->router->pathFor('members.list', ['status' => $_SESSION['members_display_status'], 'name' => $_SESSION['members_display_name']]));

    }

    public function addDirectory($request, $response)
    {

        $posting_array = $request->getParams();

        dump($posting_array);

        die();

        //$_SESSION['stored_company_name'] = 'addingamember';

        /*
        $validation = $this->validator->validate($request, [
            'business_name' => v::notEmpty()->setName('Business Name'),
            'company_name' => v::notEmpty()->setName('Company Name'),
            'business_abn' => v::notEmpty()->setName('ABN'),
            'business_acn' => v::notEmpty()->setName('ACN'),
            'business_arbn' => v::notEmpty()->setName('ARBN'),
            'checkboxes_licensed_plumber_num' =>v::notEmpty()->setName('Plumber Licence'),
            'checkboxes_other_num' =>v::notEmpty()->setName('Other Text'),
            'member_type' =>v::notEmpty()->NotSelected()->setName('Member Type'),
            'activity_type' =>v::notEmpty()->NotSelected()->setName('Activity Type'),
            'date_joined' => v::notEmpty()->setName('Date Joined'),
            'business_phone' => v::notEmpty()->setName('Business Phone'),
            'business_fax' => v::notEmpty()->setName('Business Fax'),
            'business_address' => v::notEmpty()->setName('Business Address'),
            'business_suburb' => v::notEmpty()->setName('Business Suburb'),
            'business_state' => v::notEmpty()->setName('Business State'),
            'business_postcode' => v::notEmpty()->setName('Business Post Code'),
            'mailing_address' => v::notEmpty()->setName('Mailing Address'),
            'mailing_suburb' => v::notEmpty()->setName('Mailing Suburb'),
            'mailing_state' => v::notEmpty()->setName('Mailing State'),
            'mailing_postcode' => v::notEmpty()->setName('Mailing Post Code'),

        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('members.add'));
        }
        */

        //$guid = $this->GUID();
        //$row_version = $this->uniqidReal();

        /*
        $posting_array = $request->getParams();

        $licence_type = array();

        foreach ($posting_array as $key => $value) {

            if(substr($key,0,10) == 'checkboxes'){
                $licence_type[$key] = $value;
            }
        }

        $post_license_types = json_encode($licence_type);

        */

        /*
        $member = Member::create([
            'guid' => $guid,
            'business_name' => $request->getParam('business_name'),
            'company_name' => $request->getParam('company_name'),
            'business_abn' => $request->getParam('business_abn'),
            'business_acn' => $request->getParam('business_acn'),
            'business_arbn' => $request->getParam('business_arbn'),
            'business_type' => $request->getParam('business_type'),
            'member_type' => $request->getParam('member_type'),
            'member_status' => $request->getParam('member_status'),
            'date_joined' => $request->getParam('date_joined'),
            'date_resigned' => $request->getParam('date_resigned'),
            'licence_types' => $post_license_types,
            'activity_type' => $request->getParam('activity_type'),
            'business_phone' => $request->getParam('business_phone'),
            'business_fax' => $request->getParam('business_fax'),
            'business_address' => $request->getParam('business_address'),
            'business_suburb' => $request->getParam('business_suburb'),
            'business_state' => $request->getParam('business_state'),
            'business_postcode' => $request->getParam('business_postcode'),
            'mailing_address' => $request->getParam('mailing_address'),
            'mailing_suburb' => $request->getParam('mailing_suburb'),
            'mailing_state' => $request->getParam('mailing_state'),
            'mailing_postcode' => $request->getParam('mailing_postcode'),
            'row_version' => $row_version
        ]);

        $this->flash->addMessage('info', 'New Member has been added!');

        */

        //return $response->withRedirect($this->router->pathFor('home'));

        return $response->withRedirect($this->router->pathFor('members.list', ['status' => "all", 'name' => ""]));


    }


}
