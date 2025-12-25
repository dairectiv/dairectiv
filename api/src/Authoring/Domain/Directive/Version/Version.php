<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Directive\Version;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Directive\Directive;

final class Version
{
    public private(set) VersionId $id;

    public private(set) VersionNumber $number;

    public private(set) Directive $directive;

    public private(set) VersionSnapshot $snapshot;

    public private(set) Chronos $createdAt;

    public function __construct()
    {
        $this->createdAt = Chronos::now();
    }

    public static function initialize(Directive $directive): self
    {
        $snapshot = new self();

        $snapshot->number = VersionNumber::initial();
        $snapshot->id = VersionId::create($directive, $snapshot->number);
        $snapshot->directive = $directive;
        $snapshot->snapshot = $directive->getCurrentSnapshot();

        return $snapshot;
    }

    public function increment(): self
    {
        $newVersion = new self();

        $newVersion->number = $this->number->increment();
        $newVersion->id = VersionId::create($this->directive, $newVersion->number);
        $newVersion->directive = $this->directive;
        $newVersion->snapshot = $this->directive->getCurrentSnapshot();

        return $newVersion;
    }
}
