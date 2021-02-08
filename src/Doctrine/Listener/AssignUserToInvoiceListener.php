<?php

namespace App\Doctrine\Listener;

use App\Entity\Invoice;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class AssignUserToInvoiceListener
{
    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Invoice $invoice): void
    {
        /** @var User|null */
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $invoice->setUser($user);
    }
}
