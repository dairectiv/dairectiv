<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Tests;

use Dairectiv\PHPStan\Rules\RepositoryMethodRule;
use PHPStan\Rules\Rule as TRule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<RepositoryMethodRule>
 */
#[Group('tools')]
#[Group('phpstan')]
final class RepositoryMethodRuleTest extends RuleTestCase
{
    protected function getRule(): TRule
    {
        return new RepositoryMethodRule(
            $this->createReflectionProvider(),
        );
    }

    public function testItShouldPassForValidRepository(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/RepositoryMethodRule/ValidRepository/Domain/Repository/EntityRepository.php'],
            [],
        );
    }

    public function testItShouldReportErrorWhenGetMethodReturnsNullable(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/RepositoryMethodRule/InvalidGetNullable/Domain/Repository/EntityRepository.php'],
            [
                ['Repository method "getEntityById" must return a non-nullable type.', 12],
            ],
        );
    }

    public function testItShouldReportErrorWhenGetMethodHasNoThrowsAnnotation(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/RepositoryMethodRule/InvalidGetNoThrows/Domain/Repository/EntityRepository.php'],
            [
                ['Repository method "getEntityById" must have a @throws annotation with an exception extending EntityNotFoundException.', 9],
            ],
        );
    }

    public function testItShouldReportErrorWhenGetMethodThrowsWrongException(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/RepositoryMethodRule/InvalidGetWrongException/Domain/Repository/EntityRepository.php'],
            [
                ['Repository method "getEntityById" @throws annotation must reference an exception extending EntityNotFoundException, got "Dairectiv\PHPStan\Fixtures\RepositoryMethodRule\InvalidGetWrongException\Domain\Repository\WrongException".', 12],
            ],
        );
    }

    public function testItShouldReportErrorWhenFindMethodDoesNotReturnNullable(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/RepositoryMethodRule/InvalidFindNotNullable/Domain/Repository/EntityRepository.php'],
            [
                ['Repository method "findEntityById" must return a nullable type.', 9],
            ],
        );
    }
}
