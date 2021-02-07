<?php

namespace App\Doctrine\Listener;

use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordEncodingListener
{

    protected UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function prePersist(User $user)
    {
        if (!$user->getPlainPassword()) {
            throw new LogicException("You can't persist a User without setting a plain password !");
        }

        $hash = $this->encoder->encodePassword($user, $user->getPlainPassword());

        $user->setPassword($hash)
            ->setPlainPassword(null);
    }
}
