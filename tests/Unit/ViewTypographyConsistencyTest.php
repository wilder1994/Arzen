<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ViewTypographyConsistencyTest extends TestCase
{
    public function test_headings_use_the_type_scale_instead_of_tailwind_sizes(): void
    {
        $offenders = [];
        foreach ($this->views() as $path => $source) {
            preg_match_all('/<h[1-4]\b[^>]*class="([^"]*)"/', $source, $matches);
            foreach ($matches[1] as $classes) {
                if (preg_match('/(^|\s)(text-(xs|sm|base|lg|xl|2xl|3xl)|font-(medium|semibold|bold))(\s|$)/', $classes)) {
                    $offenders[] = $path.': '.$classes;
                }
            }
        }

        $this->assertSame([], $offenders, "Use sj-type-* or a component title class for headings:\n".implode("\n", $offenders));
    }

    public function test_form_controls_use_the_shared_field_style(): void
    {
        $offenders = [];
        foreach ($this->views() as $path => $source) {
            preg_match_all('/<(input|select|textarea)\b[^>]*>/s', $source, $matches);
            foreach ($matches[0] as $tag) {
                if (preg_match('/type="(hidden|checkbox|radio|file|range|color)"/', $tag)) {
                    continue;
                }
                if (preg_match('/class="[^"]*\bborder-(gray|slate)-300\b/', $tag)) {
                    $offenders[] = $path.': '.trim(preg_replace('/\s+/', ' ', $tag));
                }
            }
        }

        $this->assertSame([], $offenders, "Use sj-ui-field__control (or x-text-input) for form fields:\n".implode("\n", $offenders));
    }

    /**
     * @return array<string, string>
     */
    private function views(): array
    {
        $root = dirname(__DIR__, 2).'/resources/views';
        $views = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (! str_ends_with($path, '.blade.php') || str_contains($path, DIRECTORY_SEPARATOR.'emails'.DIRECTORY_SEPARATOR)
                || str_contains($path, DIRECTORY_SEPARATOR.'pagination'.DIRECTORY_SEPARATOR)) {
                continue;
            }
            $views[substr($path, strlen($root) + 1)] = file_get_contents($path);
        }

        return $views;
    }
}
