<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive;

use Dairectiv\Authoring\Domain\Directive\Exception\DirectiveNotFoundException;

interface DirectiveRepository
{
    public function save(Directive $directive): void;

    /**
     * @throws DirectiveNotFoundException
     */
    public function getDirectiveById(DirectiveId $id): Directive;

    public function findDirectiveById(DirectiveId $id): ?Directive;
}
