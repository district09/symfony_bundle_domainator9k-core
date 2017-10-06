<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Security\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AuthorizationChecker
     */
    protected $authChecker;

    /**
     * Can't inject auth_checker directly due to circular reference.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAuthChecker()
    {
        if (!$this->authChecker) {
            $this->authChecker = $this->container->get('security.authorization_checker');
        }

        return $this->authChecker;
    }
}
