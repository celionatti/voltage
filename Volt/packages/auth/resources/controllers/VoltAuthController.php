<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Packages\auth\resources\controllers;

use celionatti\Voltage\PackageController;


/**
 * ==============================================
 * ==================           =================
 * Site Controller Class
 * ==================           =================
 * ==============================================
 */

class VoltAuthController extends PackageController
{
    public function login()
    {
        $data = [
            'title' => '<strong>Hello, World!</strong>', // Will be escaped if autoescape is true
            'content' => 'This is a more advanced view template engine using .volt syntax.',
            'amount' => 12345.67,
            'name' => 'celio natti',
        ];

        $this->view->render("welcome", $data);
    }

    public function register()
    {
        dump("Login Package File Route", false);
    }
}