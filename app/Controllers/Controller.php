<?php

namespace App\Controllers;

class Controller

{

    protected $container;

    public function __construct($container)
    {

        $this->container = $container;


    }

    //date_default_timezone_set('Australia/West');

    public function __get($property)
    {

        //var_dump($property);

        if($this->container->{$property}) {

            return $this->container->{$property};

        }

    }


}