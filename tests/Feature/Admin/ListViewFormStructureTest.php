<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

/**
 * Guards the one structural rule every list view has to follow: a row's
 * own form must never sit inside the bulk-action form.
 *
 * This is a real bug that shipped, and its symptom was odd enough to be
 * worth writing down. HTML forbids nested forms, and the parser's recovery
 * is not "ignore the inner form" — it discards each nested `<form>` START
 * tag, but the first nested `</form>` END tag then closes the OUTER form.
 * So on a list where the bulk form wrapped the table:
 *
 *   - row 1's delete button ended up owned by the bulk form, submitting to
 *     bulk-destroy with every row's spoofed _method input, giving a 404;
 *   - every later row got a real form of its own and worked fine.
 *
 * "Only the first row is broken" is therefore the signature of this bug,
 * and it is invisible in the Blade source, which looks perfectly nested.
 *
 * The fix is the HTML5 `form="bulkForm"` attribute: the bulk form is empty
 * and self-closed, and the checkboxes and submit button join it by id from
 * anywhere in the document. This test scans the templates rather than
 * rendering them, so it covers every list view at once - including ones
 * added later, which is the point.
 */
class ListViewFormStructureTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function indexViews(): array
    {
        return glob(resource_path('views/*/index.blade.php')) ?: [];
    }

    public function test_there_are_list_views_to_check(): void
    {
        // Guards the guard: a glob that silently matches nothing would make
        // every assertion below vacuously pass.
        $this->assertGreaterThan(20, count($this->indexViews()));
    }

    public function test_no_list_view_nests_a_row_form_inside_the_bulk_form(): void
    {
        $offenders = [];

        foreach ($this->indexViews() as $path) {
            $lines = explode("\n", (string) file_get_contents($path));
            $depth = 0;
            $open = false;
            $nested = 0;

            foreach ($lines as $line) {
                if (! $open) {
                    if (str_contains($line, 'id="bulkForm"')) {
                        $open = true;
                        $depth = 1;
                    }

                    continue;
                }

                // Blade comments legitimately mention <form> while
                // explaining this very rule, so they are not markup.
                $line = preg_replace('/\{\{--.*?--\}\}/s', '', $line) ?? $line;

                $opens = preg_match_all('/<form\b/', $line);
                $closes = preg_match_all('/<\/form>/', $line);

                $nested += $opens;
                $depth += $opens - $closes;

                if ($depth <= 0) {
                    break;
                }
            }

            if ($open && $nested > 0) {
                $offenders[] = basename(dirname($path)).' ('.$nested.' nested)';
            }
        }

        $this->assertSame([], $offenders, implode("\n", [
            'These list views nest a row form inside the bulk-action form:',
            '  '.implode("\n  ", $offenders),
            '',
            'HTML forbids this, and the first row\'s </form> closes the bulk form,',
            'so row 1\'s delete button posts to bulk-destroy and 404s while every',
            'other row works. Close the bulk form right after @csrf and bind the',
            'checkboxes and submit button with form="bulkForm" instead.',
        ]));
    }

    public function test_every_bulk_form_checkbox_and_button_is_bound_by_id(): void
    {
        $offenders = [];

        foreach ($this->indexViews() as $path) {
            $source = (string) file_get_contents($path);

            if (! str_contains($source, 'id="bulkForm"')) {
                continue;
            }

            $module = basename(dirname($path));

            // Blade expressions are stripped BEFORE any tag matching: an
            // arrow in `{{ $depot->id }}` contains a literal ">", so a
            // naive [^>]* attribute match ends mid-tag and reports a
            // perfectly bound checkbox as unbound. That false positive is
            // exactly what this line exists to prevent.
            $markup = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}/s', 'X', $source) ?? $source;

            // Every row checkbox must name the form it belongs to, since it
            // is no longer inside it.
            preg_match_all('/<input type="checkbox" name="ids\[\]"[^>]*>/', $markup, $boxes);

            foreach ($boxes[0] as $box) {
                if (! str_contains($box, 'form="bulkForm"')) {
                    $offenders[] = "{$module}: a row checkbox has no form=\"bulkForm\"";
                }
            }

            if (str_contains($markup, 'Delete Selected')
                && ! preg_match('/<button type="submit" form="bulkForm"[^>]*>\s*<i class="ti ti-trash me-1"><\/i>Delete Selected/', $markup)) {
                $offenders[] = "{$module}: the Delete Selected button has no form=\"bulkForm\"";
            }
        }

        $this->assertSame([], $offenders, "Bulk-action controls not bound to their form:\n  ".implode("\n  ", $offenders));
    }

    public function test_the_bulk_form_is_closed_immediately_and_wraps_nothing(): void
    {
        $offenders = [];

        foreach ($this->indexViews() as $path) {
            $source = (string) file_get_contents($path);

            if (! str_contains($source, 'id="bulkForm"')) {
                continue;
            }

            // The open tag, an @csrf, and the close - nothing else. Anything
            // between them is content the form would wrap again.
            $markup = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}/s', 'X', $source) ?? $source;

            if (! preg_match('/<form id="bulkForm"[^>]*>\s*@csrf\s*<\/form>/', $markup)) {
                $offenders[] = basename(dirname($path));
            }
        }

        $this->assertSame([], $offenders, implode("\n", [
            'These bulk forms wrap content instead of being empty:',
            '  '.implode(', ', $offenders),
            'Expected exactly: <form id="bulkForm" ...> @csrf </form>',
        ]));
    }
}
