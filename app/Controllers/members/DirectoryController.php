<?php

namespace App\Controllers\Members;

use Slim\Router;
use Slim\Views\Twig;
use App\Models\Members;
use App\Models\Directory;
use App\Models\SpecialistSkills;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DirectoryController
{

    public function get($id, Request $request, Response $response, Twig $view, Members $members, Directory $directory, SpecialistSkills $specialist_skills)
    {

        $specialist_skills = $specialist_skills->get();
        $directory = $directory->where('member_id', $id)->get()->first();

        return $view->render($response, 'directory/directory.edit.twig', [

            'directory' => $directory,
            'skills' => json_decode($directory->skills),
            'skillset' => $specialist_skills,
            'js_script' => 'directory'
            //'members_display_status' => $_SESSION['members_display_status'],
            //'members_display_name' => $_SESSION['members_display_name']

        ]);

    }

}