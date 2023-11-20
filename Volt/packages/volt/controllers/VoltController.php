<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Packages\volt\controllers;

use celionatti\Voltage\PackageController;


/**
 * ==============================================
 * ==================           =================
 * Volt Package Controller Class
 * ==================           =================
 * ==============================================
 */

class VoltController extends PackageController
{
    public function volt()
    {
        $data = [
            'name' => 'celio natti',
        ];

        $this->view->render("volt/index", $data);
    }
}