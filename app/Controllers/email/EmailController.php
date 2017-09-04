<?php

namespace App\Controllers\Email;

use Slim\Router;
use Slim\Views\Twig;
use Slim\Flash\Messages as Flash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \DrewM\MailChimp\MailChimp;
use Illuminate\Database\Capsule\Manager as DB;

class EmailController
{
    protected $router;
    protected $view;
    protected $flash;
    protected $mailchimp;

    public function __construct(Router $router, Twig $view, Flash $flash, Mailchimp $mailchimp )
    {
        $this->router = $router;
        $this->view = $view;
        $this->flash = $flash;
        $this->mailchimp = $mailchimp;
    }

    public function index(Request $request, Response $response)
    {
        $emailLists = $this->mailchimp->get('lists');

        return $this->view->render($response, 'emails/email.lists.index.twig', [
            'lists' => $emailLists,
            'js_script' => 'email_lists'
        ]);
    }

    public function newList(Request $request, Response $response)
    {
        return $this->view->render($response, 'emails/email.update.list.twig', [
            'mode' => 'add',
            'js_script' => 'email_lists'
        ]);
    }

    public function addList(Request $request, Response $response)
    {
        $list_name = $request->getParam('list_name');

        $list_data = [
            'name' => $request->getParam('list_name'),
            'contact' => [
                "company" => getenv('ENTITY_NAME'),
                "address1" => getenv('ENTITY_ADDRESS_1'),
                "address2" => "",
                "city" => getenv('ENTITY_SUBURB'),
                "state" => getenv('ENTITY_STATE'),
                "zip" => getenv('ENTITY_POSTCODE'),
                "country" => "AU",
                "phone" => getenv('ENTITY_PHONE')
            ],
            "permission_reminder" => "Because you are a member",
            'campaign_defaults' => [
                "from_name" => "events",
                "from_email" => "events@mpgawa.com.au",
                "subject" => "TEST EVENT",
                "language" => "en"
            ],
            'email_type_option' => false,
        ];

        //dump($list_data);

        $result = $this->mailchimp->post("lists", $list_data);

        dump($result);

    }

    public function addMembers($id, Request $request, Response $response)
    {
        $list_members_data = [
            "email_address" => 'allan.hyde@tektools.com.au',
            "status" => "subscribed",
            "merge_fields" => [
                "FNAME" => "Allan",
                "LNAME" => "Hyde"
            ]
        ];

        $result = $this->mailchimp->post("lists/$id/members", $list_members_data);

        dump($result);
    }

    public function getList($id, $name, Request $request, Response $response)
    {
        $list = $this->mailchimp->get("lists/$id/members?count=100");

        return $this->view->render($response, 'emails/email.list.members.twig', [
            'name' => $name,
            'list' => $list,
            'js_script' => 'email_lists'
        ]);
    }

    public function getCampaigns(Request $request, Response $response)
    {
        $campaigns = $this->mailchimp->get("campaigns");

        return $this->view->render($response, 'emails/email.campaigns.twig', [
            'campaigns' => $campaigns,
            'js_script' => 'email_lists'
        ]);
    }

    public function getTemplates(Request $request, Response $response)
    {
        $templates = $this->mailchimp->get("templates/161982");

        return $this->view->render($response, 'emails/email.templates.twig', [
            'templates' => $templates,
            'js_script' => 'email_lists'
        ]);
    }

    public function getCampaignContent($id, Request $request, Response $response)
    {
        $content = $this->mailchimp->get("campaigns/$id/content");

        return $this->view->render($response, 'emails/email.campaign.content.twig', [
            'content' => $content,
            'js_script' => 'email_lists'
        ]);
    }

}