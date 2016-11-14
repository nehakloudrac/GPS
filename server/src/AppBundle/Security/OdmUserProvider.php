<?php

namespace GPS\AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use GPS\AppBundle\Document;

/**
 * Using a custom user provider because of case sensitivity in mongo...
 * all checks for a user in the security component based on email
 * should be done case-insensitive.
 */
class OdmUserProvider implements UserProviderInterface
{
    private $em;
    private $userClass;
    private $userRepo;
    
    public function __construct($em, $userClass, $userRepo)
    {
        $this->em = $em;
        $this->userClass = $userClass;
        $this->userRepo = $userRepo;
    }
    
    public function loadUserByUsername($username)
    {
        // TODO: refactor to use a case insensitive regex query; that will guard
        // against relying on other parts of the app to do the correct transformation
        // to the data
        
        // we use emails as usernames, and force them
        // to lowercase because mongo is case-sensitive
        $username = strtolower(trim($username));
        
        $user = $this->em->getRepository($this->userRepo)->findOneBy(['email' => $username]);

        // load user from repo
        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException(sprintf('Email "%s" was not found.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Document\User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === $this->userClass;
    }
}
