<?php

/*
 * This file is part of the API Platform range header pagination Bundle.
 *
 * (c) Campings.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\AbstractPaginator;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryChecker;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\State\Pagination\Countable;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\State\Pagination\Pagination;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrineOrmPaginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @implements QueryResultCollectionExtensionInterface<static>
 */
final class PaginationExtension implements QueryResultCollectionExtensionInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private Pagination $pagination,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = [],
    ): void {
        [$offset, $limit] = $this->getPagination($queryBuilder, $operation, $context);

        $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    }

    private function getPagination(QueryBuilder $queryBuilder, ?Operation $operation, array $context): array
    {
        if ($this->pagination->isCountable($operation)) {
            $count = (new DoctrineOrmPaginator($queryBuilder))->count();
        }

        $offset = $this->pagination->getOffset($operation, $context);
        $limit = $this->pagination->getLimit($operation, $context);

        return [$offset, $limit, $count ?? null];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function supportsResult(string $resourceClass, Operation $operation = null, array $context = []): bool
    {
        return $this->pagination->rangeHeaderEnabled($operation, $context);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength")
     */
    public function getResult(
        QueryBuilder $queryBuilder,
        string $resourceClass = null,
        Operation $operation = null,
        array $context = [],
    ): iterable {
        $query = $queryBuilder->getQuery();

        // Only one alias, without joins, disable the DISTINCT on the COUNT
        if (1 === \count($queryBuilder->getAllAliases())) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $doctrineOrmPaginator = new DoctrineOrmPaginator(
            $query,
            $this->shouldDoctrinePaginatorFetchJoinCollection($queryBuilder, $operation, $context)
        );
        $doctrineOrmPaginator->setUseOutputWalkers(
            $this->shouldDoctrinePaginatorUseOutputWalkers($queryBuilder, $operation, $context)
        );

        if ($this->pagination->isCountable($operation)) {
            return new class($doctrineOrmPaginator, $queryBuilder) extends AbstractPaginator implements Countable {
                private QueryBuilder $queryBuilder;

                public function __construct(DoctrineOrmPaginator $paginator, QueryBuilder $queryBuilder)
                {
                    parent::__construct($paginator);
                    $this->queryBuilder = $queryBuilder;
                }

                public function totalItems(): ?int
                {
                    return (new DoctrineOrmPaginator($this->queryBuilder))->count();
                }

                public function start(): ?int
                {
                    return $this->firstResult;
                }

                public function end(): ?int
                {
                    return $this->firstResult + $this->maxResults;
                }
            };
        }

        return new class($doctrineOrmPaginator) extends AbstractPaginator implements Countable {
            public function totalItems(): ?int
            {
                return null;
            }

            public function start(): ?int
            {
                return $this->firstResult;
            }

            public function end(): ?int
            {
                return $this->firstResult + $this->maxResults;
            }
        };
    }

    /**
     * Determines the value of the $fetchJoinCollection argument passed to the Doctrine ORM Paginator.
     */
    private function shouldDoctrinePaginatorFetchJoinCollection(
        QueryBuilder $queryBuilder,
        Operation $operation = null,
        array $context = [],
    ): bool {
        $fetchJoinCollection = $operation?->getPaginationFetchJoinCollection();

        if (isset($context['operation_name']) && isset($fetchJoinCollection)) {
            return $fetchJoinCollection;
        }

        if (isset($context['graphql_operation_name']) && isset($fetchJoinCollection)) {
            return $fetchJoinCollection;
        }

        /*
         * "Cannot count query which selects two FROM components, cannot make distinction"
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/WhereInWalker.php#L81
         * @see https://github.com/doctrine/doctrine2/issues/2910
         */
        if (QueryChecker::hasRootEntityWithCompositeIdentifier($queryBuilder, $this->managerRegistry)) {
            return false;
        }

        if (QueryChecker::hasJoinedToManyAssociation($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        // disable $fetchJoinCollection by default (performance)
        return false;
    }

    /**
     * Determines whether the Doctrine ORM Paginator should use output walkers.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function shouldDoctrinePaginatorUseOutputWalkers(
        QueryBuilder $queryBuilder,
        Operation $operation = null,
        array $context = [],
    ): bool {
        $useOutputWalkers = $operation?->getPaginationUseOutputWalkers();

        if (isset($context['operation_name']) && isset($useOutputWalkers)) {
            return $useOutputWalkers;
        }

        if (isset($context['graphql_operation_name']) && isset($useOutputWalkers)) {
            return $useOutputWalkers;
        }

        /*
         * "Cannot count query that uses a HAVING clause. Use the output walkers for pagination"
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/CountWalker.php#L56
         */
        if (QueryChecker::hasHavingClause($queryBuilder)) {
            return true;
        }

        /*
         * "Cannot count query which selects two FROM components, cannot make distinction"
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/CountWalker.php#L64
         */
        if (QueryChecker::hasRootEntityWithCompositeIdentifier($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        /*
         * "Paginating an entity with foreign key as identifier only works when using the Output Walkers. Call Paginator#setUseOutputWalkers(true) before iterating the paginator."
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/LimitSubqueryWalker.php#L77
         */
        if (QueryChecker::hasRootEntityWithForeignKeyIdentifier($queryBuilder, $this->managerRegistry)) {
            return true;
        }

        /*
         * "Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use output walkers."
         *
         * @see https://github.com/doctrine/orm/blob/v2.6.3/lib/Doctrine/ORM/Tools/Pagination/LimitSubqueryWalker.php#L150
         */
        if (QueryChecker::hasMaxResults($queryBuilder) && QueryChecker::hasOrderByOnFetchJoinedToManyAssociation(
            $queryBuilder,
            $this->managerRegistry
        )) {
            return true;
        }

        // Disable output walkers by default (performance)
        return false;
    }
}
