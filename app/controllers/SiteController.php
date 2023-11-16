<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace Voltage\app\controllers;

use celionatti\Voltage\Controller;


/**
 * ==============================================
 * ==================           =================
 * Site Controller Class
 * ==================           =================
 * ==============================================
 */

class SiteController extends Controller
{
    public function index()
    {
        $data = [
            'title' => '<strong>Hello, World!</strong>', // Will be escaped if autoescape is true
            'content' => 'This is a more advanced view template engine using .volt syntax.',
            'amount' => 12345.67,
        ];
        
        $this->view->render("welcome", $data);
    }
}