<?php

declare(strict_types=1);

namespace qa;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function api\ecs;
use function api\lint;
use function api\phpstan;
use function api\rector;
use function api\test as api_test;
use function app\lint as app_lint;
use function app\test as app_test;
use function Castor\io;

#[AsTask(description: 'Runs all QA tasks (API + App)', aliases: ['qa'])]
function all(
    #[AsOption(shortcut: 'f', description: 'Apply fixes where possible')]
    bool $fix = false,
): int {
    io()->title('Running all QA tasks');

    $apiResult = api(fix: $fix);
    $appResult = app(fix: $fix);

    return max($apiResult, $appResult);
}

#[AsTask(description: 'Runs API QA tasks (lint + test + phpstan)')]
function api(
    #[AsOption(shortcut: 'f', description: 'Apply fixes where possible')]
    bool $fix = false,
): int {
    io()->title('Running API QA tasks');

    $results = [];

    // Linting (rector, ecs, container, yaml, schema)
    $results[] = lint(fix: $fix);
    if ($results[0] !== 0) {
        return $results[0];
    }

    // PHPStan
    $results[] = phpstan();
    if ($results[1] !== 0) {
        return $results[1];
    }

    // Tests
    $results[] = api_test();

    return max($results);
}

#[AsTask(description: 'Runs App QA tasks (lint + test)')]
function app(
    #[AsOption(shortcut: 'f', description: 'Apply fixes where possible')]
    bool $fix = false,
): int {
    io()->title('Running App QA tasks');

    $results = [];

    // Linting
    app_lint(fix: $fix);

    // Tests
    app_test();

    return 0;
}

// =============================================================================
// Legacy aliases for backward compatibility
// =============================================================================

#[AsTask(name: 'rector', namespace: '', description: 'Runs Rector (legacy alias)')]
function legacy_rector(
    #[AsOption(shortcut: 'f', description: 'Apply fixes')]
    bool $fix = false,
    #[AsOption(description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    return rector(fix: $fix, ci: $ci);
}

#[AsTask(name: 'ecs', namespace: '', description: 'Runs ECS (legacy alias)')]
function legacy_ecs(
    #[AsOption(shortcut: 'f', description: 'Apply fixes')]
    bool $fix = false,
    #[AsOption(description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    return ecs(fix: $fix, ci: $ci);
}

#[AsTask(name: 'phpstan', namespace: '', description: 'Runs PHPStan (legacy alias)')]
function legacy_phpstan(
    #[AsOption(description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    return phpstan(ci: $ci);
}

// =============================================================================
// OAS Linting (from DAI-169)
// =============================================================================

#[AsTask(name: 'lint', namespace: 'oas', description: 'Lints OpenAPI specification with Spectral')]
function oas_lint(
    #[AsOption(description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    io()->section('Running OpenAPI Spectral lint...');

    $format = $ci ? '--format github-actions' : '';

    return \Castor\exit_code(\sprintf(
        'docker run --rm -v %s:/tmp stoplight/spectral lint %s --ruleset /tmp/.spectral.yaml /tmp/oas/openapi.yaml',
        getcwd(),
        $format,
    ));
}
