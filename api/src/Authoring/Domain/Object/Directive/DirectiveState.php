<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive;

enum DirectiveState: string
{
    case Draft = 'draft';

    case Published = 'published';

    case Archived = 'archived';
}
