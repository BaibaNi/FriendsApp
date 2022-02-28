<?php

namespace App\Controllers;

use App\View;

class MainController
{

    public function index(): View
    {
        return new View('Main/index');
    }

}