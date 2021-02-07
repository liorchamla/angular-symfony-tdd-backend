<?php

namespace App\EventSubscriber\JWT;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class JWTCustomizationSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'pushUserExtraDataInsideToken',
            'lexik_jwt_authentication.on_authentication_failure' => 'customizeFailureResponse'
        ];
    }

    public function customizeFailureResponse(AuthenticationFailureEvent $e)
    {
        $e->setResponse(new JsonResponse([
            'message' => "Impossible de se connecter avec ces informations"
        ], 401));
    }

    public function pushUserExtraDataInsideToken(JWTCreatedEvent $e)
    {
        $payload = $e->getData();

        /** @var User */
        $user = $e->getUser();

        $payload['fullName'] = $user->getFullName();
        $payload['email'] = $user->getEmail();

        $e->setData($payload);
    }
}
