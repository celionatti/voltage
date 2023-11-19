<?php

declare(strict_types=1);

/**
 * Package Name: Volt Authentication
 * Description: Volt Authentication for basic login, signup, forgot password and recover password.
 * Version: 1.0.0
 * Author: Celio Natti
 * Author URI: www.nattination.com.ng
 * License: GPL2
 */


$args = array('celio', 'natti');
doAction('before-view', $args);
doAction('after-view', $args);