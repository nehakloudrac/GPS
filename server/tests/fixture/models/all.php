<?php

use League\FactoryMuffin\Faker\Facade as F;
use GPS\Popov\Facade as Factory;
use Values as V;
use Helpers as H;

mt_srand(1);
require_once(__DIR__.'/values.php');
require_once(__DIR__.'/helpers.php');
require_once(__DIR__.'/define_users.php');
require_once(__DIR__.'/define_candidate_profiles.php');
require_once(__DIR__.'/define_resource_links.php');
require_once(__DIR__.'/create_users.php');

function gps_create_fixtures()
{
    $fm = Factory::instance();
    
    // define fixture pools
    gps_define_user_fixtures($fm);
    gps_define_candidate_profile_fixtures($fm);
    gps_define_resource_link_fixtures($fm);

    // load some hard coded fixture definitions that are used in some tests
    gps_create_user_fixtures($fm);

    return $fm;
}

