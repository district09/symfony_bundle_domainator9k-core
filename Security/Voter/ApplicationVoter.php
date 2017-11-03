<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Security\Voter;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ApplicationVoter extends AbstractVoter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const PROVISION = 'provision';
    const EDIT_RIGHTS = 'edit_rights';

    protected function supports($attribute, $class)
    {
        return in_array($attribute, array(
            self::VIEW,
            self::EDIT,
            self::PROVISION,
            self::EDIT_RIGHTS,
        )) && (is_subclass_of($class, Application::class) || $class instanceof Application);
    }

    /**
     * @param string         $attribute
     * @param Application    $application
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $application, TokenInterface $token)
    {
        if (!$token->isAuthenticated()) {
            return false;
        }

        $user = $token->getUser();

        if ($this->getAuthChecker()->isGranted(['ROLE_ADMIN'], $user)) {
            return true;
        }

        return $application->hasUser($user) || $this->getAuthChecker()->isGranted($application->getRoles(true), $user);
    }
}
