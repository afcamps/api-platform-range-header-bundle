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

use Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\Kernel;

$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'project_dir' => __DIR__.'/..',
];

require_once dirname(__DIR__).'/../../vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
