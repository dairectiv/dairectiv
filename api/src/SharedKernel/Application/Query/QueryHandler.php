<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Application\Query;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('messenger.message_handler')]
interface QueryHandler
{
}
