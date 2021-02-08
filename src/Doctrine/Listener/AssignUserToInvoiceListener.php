<?php

namespace App\Doctrine\Listener;

use App\Entity\Invoice;
use Symfony\Component\Security\Core\Security;

class AssignUserToInvoiceListener
{
    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Invoice $invoice)
    {
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $invoice->setUser($user);
    }
}
