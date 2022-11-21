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
use ApiPlatform\Util\RequestParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RangeHeaderRequestListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['handle', EventPriorities::PRE_READ],
        ];
    }

    public function handle(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $range = $event->getRequest()->headers->get('range');

        if (null === $range || !preg_match('/([a-z]+)=(\d+)-(\d+)?/', $range, $rangeParts)) {
            return;
        }

        $rangeFilters = [
            'range_name' => $rangeParts[1],
            'range_start' => $rangeParts[2],
            'range_end' => $rangeParts[3] ?? null,
        ];

        $filters = $request->attributes->get('_api_filters');
        if (null === $filters) {
            $queryString = RequestParser::getQueryString($request);
            $filters = null !== $queryString ? RequestParser::parseRequestParams($queryString) : null;
        }

        $filters = array_merge($filters ?? [], $rangeFilters);
        $request->attributes->set('_api_filters', $filters);
        $request->attributes->set('_range_headers', $rangeFilters);
    }
}
