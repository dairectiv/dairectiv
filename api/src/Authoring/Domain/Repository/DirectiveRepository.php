<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\Directive;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;

interface DirectiveRepository
{
    public function save(Directive $directive): void;

    /**
     * @throws DirectiveNotFoundException
     */
    public function getDirectiveById(DirectiveId $id): Directive;

    public function findDirectiveById(DirectiveId $id): ?Directive;
}
