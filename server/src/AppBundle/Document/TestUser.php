<?php

namespace GPS\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * A test user used during testing.
 *
 * @MongoDB\Document(collection="users")
 */
class TestUser extends User
{
    
}
