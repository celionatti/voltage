<?php

declare(strict_types=1);

use celionatti\Voltage\Voltage;
use celionatti\Packages\auth\controllers\VoltAuthController;

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

 /** @var Voltage $voltage */

$voltage->router->get("/auth/login", [VoltAuthController::class,"login"]);