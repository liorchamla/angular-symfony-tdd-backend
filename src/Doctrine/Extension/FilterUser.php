<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Invoice;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class FilterUser  implements QueryItemExtensionInterface
{

    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    /**
     * Makes sure that invoices visibles through API are owned by the authenticated user
     *
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param array<int,string> $identifiers
     * @param string|null $operationName
     * @param array<string,string> $context
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?string $operationName = null,
        array $context = []
    ): void {
        if ($resourceClass !== User::class) {
            return;
        }

        /** @var User|null */
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$alias.id = :id")
            ->setParameter('id', $user->getId());
    }
}
