<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\ChangeSet;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Directive\Directive;
use Dairectiv\Authoring\Domain\Directive\DirectiveVersion;

/**
 * Represents a change to be applied to a Directive.
 *
 * A Change captures:
 * - The source version (version before applying the change)
 * - A snapshot of the directive's state before the change (for history/rollback)
 * - The delta (new values to apply)
 *
 * @template TDirective of Directive
 */
abstract class Change
{
    public private(set) DirectiveVersion $sourceVersion;

    public private(set) Chronos $appliedAt;

    private bool $snapshotCaptured = false;

    public function __construct(DirectiveVersion $sourceVersion)
    {
        $this->sourceVersion = $sourceVersion;
        $this->appliedAt = Chronos::now();
    }

    /**
     * Captures a snapshot of the directive's current state before applying changes.
     * This enables version history tracking and potential rollback.
     *
     * @param TDirective $directive
     */
    abstract public function captureSourceSnapshot(Directive $directive): void;

    /**
     * @internal Called by Directive::applyChanges() to capture snapshot once
     *
     * @param TDirective $directive
     */
    final public function captureSnapshot(Directive $directive): void
    {
        if ($this->snapshotCaptured) {
            return;
        }

        $this->captureSourceSnapshot($directive);
        $this->snapshotCaptured = true;
    }

    public function hasSnapshotCaptured(): bool
    {
        return $this->snapshotCaptured;
    }
}
