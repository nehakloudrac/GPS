<?php

/**
 * This is just a convenience file for bootstraping symfony for use with `phsysh`
 */

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/bootstrap.php.cache';
Debug::enable();
require_once __DIR__.'/AppKernel.php';

/**
 * Run a request in a new environment.
 *
 * @param  [type] $method HTTP method to use
 * @param  [type] $path   Application path to test
 * @param  [type] $data   Content body to send
 * @param  [type] $return Whether or not to return the response instance rather than sending it
 * @return [type]         null|Response
 */
function route($method, $path, $data = null, $return = false) {
    $kernel = new_kernel();

    $request = Request::create(
        $path,
        $method,
        [],     //params
        [],     //cookies
        [],     //files
        [],     //server
        $data
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    if ($return) {
        return $response;
    }

    return $response->getContent();
}

/**
 * Create and return a new, already booted, AppKernel.
 *
 * @return AppKernel
 */
function new_kernel($env = 'dev') {
    $kernel = new AppKernel($env, true);
    $kernel->loadClassCache();
    $kernel->boot();

    return $kernel;
}
