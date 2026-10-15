<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Tests;

use Deprecated;
use ReflectionMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Eightfold\HtmlBuilder\Forms\EmailInput;
use Eightfold\HtmlBuilder\Forms\Fieldset;
use Eightfold\HtmlBuilder\Forms\Password;
use Eightfold\HtmlBuilder\Forms\Select;
use Eightfold\HtmlBuilder\Forms\TextArea;
use Eightfold\HtmlBuilder\Forms\TextInput;

final class ContainerPropsTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function components(): iterable
    {
        foreach (['text', 'email', 'password', 'textarea', 'fieldset', 'dropdown', 'multiple', 'radio', 'checkbox'] as $mode) {
            yield $mode => [$mode];
        }
    }

    #[Test]
    #[DataProvider('components')]
    public function deprecated_alias_preserves_fluent_return_and_rendered_output(string $mode): void
    {
        $component = match ($mode) {
            'text' => TextInput::create('Field', 'field'),
            'email' => EmailInput::create('Field', 'field'),
            'password' => Password::create('Field', 'field'),
            'textarea' => TextArea::create('Field', 'field'),
            'fieldset' => Fieldset::create('Field', 'field'),
            default => Select::create('Field', 'field', ['a' => 'A'])->$mode()
        };
        $properties = ['class container', 'data-example preserved'];
        $modern = clone $component;
        parent::assertSame($modern, $modern->containerProps(...$properties));
        $attribute = new ReflectionMethod($component, 'wrapperProps')
            ->getAttributes(Deprecated::class)[0]->newInstance();
        parent::assertSame('Use containerProps() instead.', $attribute->message);
        parent::assertSame('5.0', $attribute->since);

        $deprecations = [];
        set_error_handler(static function (int $severity, string $message) use (&$deprecations): bool {
            if ($severity !== E_USER_DEPRECATED) {
                return false;
            }
            $deprecations[] = $message;
            return true;
        });
        try {
            $result = $component->wrapperProps(...$properties);
        } finally {
            restore_error_handler();
        }

        parent::assertSame($component, $result, 'The deprecated alias must return containerProps() results.');
        parent::assertSame((string) $modern, (string) $component, 'Renaming must not change markup.');
        parent::assertCount(1, $deprecations, 'The native attribute must emit one deprecation per call.');
        parent::assertStringContainsString('Use containerProps() instead.', $deprecations[0]);
    }
}
