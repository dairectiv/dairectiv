<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Application\Command;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('messenger.message_handler')]
interface CommandHandler
{
}
