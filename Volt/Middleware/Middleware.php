<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\Middleware;

/**
 * ==============================================
 * ==================           =================
 * Middleware Class
 * ==================           =================
 * ==============================================
 */

 abstract class Middleware
{
    abstract public function execute();
}