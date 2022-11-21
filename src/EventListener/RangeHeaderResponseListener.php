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

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\State\Pagination\Countable;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\State\Pagination\Pagination;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RangeHeaderResponseListener implements EventSubscriberInterface
{
    public function __construct(private Pagination $pagination)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['handle', EventPriorities::PRE_RESPOND],
        ];
    }

    public function handle(ResponseEvent $event): void
    {
        $operation = $event->getRequest()->attributes->get('_api_normalization_context')['operation'] ?? null;
        $isEnabled = $this->pagination->rangeHeaderEnabled($operation);

        if (!$isEnabled) {
            return;
        }

        $rangeName = $this->pagination->getRangeName($operation);
        $event->getResponse()->headers->set('Accept-ranges', $rangeName);

        $paginator = $event->getRequest()->attributes->get('data');

        if (!$paginator instanceof Countable) {
            return;
        }

        $totalItems = $paginator->totalItems();
        $offset = $paginator->start();
        $limit = $paginator->end();

        if (null === $offset) {
            return;
        }

        if (null !== $totalItems && $offset >= $totalItems || $limit > $totalItems) {
            $event->getResponse()->setContent(null);
            $event->getResponse()->setStatusCode(Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
            $event->stopPropagation();

            return;
        }

        $totalItems = $totalItems ?? '*';
        $event->getResponse()->headers->set('Content-Range', "{$rangeName} {$offset}-{$limit}/{$totalItems}");
        $response = $event->getResponse()->setStatusCode(Response::HTTP_PARTIAL_CONTENT);

        $event->setResponse($response);
    }
}
