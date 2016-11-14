<?php

use Symfony\Component\Debug\Debug;

require __DIR__.'/../app/bootstrap.php.cache';

Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';


// Force clean reset of mongo fixtures at the beginning
// of the test run... this allows subsequent tests to safely reset
// fixtures from cached collections, which dramatically speeds everything up
call_user_func(function() {
    echo "\nResetting MongoDB collections by generating fixtures... ";
    $kernel = new AppKernel('test', true);
    $kernel->boot();
    $kernel
        ->getContainer()
        ->get('gps.fixtures')
        ->resetMongo(false)
    ;
    echo "done.\n\n";
});
