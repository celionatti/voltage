<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Packages\auth\controllers;

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
            'name' => 'celio natti',
        ];

        $this->view->render("auth/login", $data);
    }

    public function register()
    {
        dump("Login Package File Route", false);
    }
}