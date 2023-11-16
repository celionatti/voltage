<?php

declare(strict_types=1);


use celionatti\Voltage\Voltage;

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

 /** @var Voltage $voltage */

$voltage->router->get("/auth", function () {
    echo "Auth Package - Hello World";
});