<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

enum DirectiveState: string
{
    case Draft = 'draft';

    case Published = 'published';

    case Archived = 'archived';
}
