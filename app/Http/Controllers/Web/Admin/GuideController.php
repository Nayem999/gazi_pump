<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Support\SettingsNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The in-app manual: how the work flows, then the two guides.
 *
 * The content is the same markdown the repository ships in docs/, rendered
 * at request time. One copy of the documentation, so the page cannot drift
 * from the files a developer reads.
 *
 * Open to every signed-in user and gated by no permission. This is the one
 * page that explains why somebody sees what they see, so withholding it
 * from a user holding few permissions would withhold exactly the
 * explanation they need. Nothing here is sensitive: it describes how the
 * system works, never what is in it.
 */
class GuideController extends Controller
{
    /**
     * The guides, in the order they are shown.
     *
     * The key prefixes every heading id, because both files open with the
     * same heading levels and two identical ids would make the contents
     * list point at whichever came first.
     *
     * @var array<string, array{file: string, title: string, summary: string, icon: string}>
     */
    private const GUIDES = [
        'user' => [
            'file' => 'docs/USER_GUIDE.md',
            'title' => 'User Guide',
            'summary' => 'Working in the system day to day.',
            'icon' => 'ti-user',
        ],
        'admin' => [
            'file' => 'docs/ADMIN_GUIDE.md',
            'title' => 'Administrator Guide',
            'summary' => 'Configuring and running it.',
            'icon' => 'ti-settings',
        ],
    ];

    public function index(Request $request): View
    {
        $sections = [];

        foreach (self::GUIDES as $key => $guide) {
            $path = base_path($guide['file']);

            if (! is_file($path)) {
                // The page *is* the documentation; half of it missing is a
                // deployment that did not ship docs/, not a page worth
                // rendering with a hole in it.
                throw new NotFoundHttpException('The documentation has not been installed.');
            }

            [$html, $contents] = $this->render((string) file_get_contents($path), $key);

            $sections[] = [
                'key' => $key,
                'title' => $guide['title'],
                'summary' => $guide['summary'],
                'icon' => $guide['icon'],
                'html' => $html,
                'contents' => $contents,
            ];
        }

        return view('guide.index', [
            'sections' => $sections,
            // Lets the page tell this particular reader what they can
            // configure, rather than describing the product in general.
            'canConfigure' => SettingsNavigation::availableTo($request->user()),
            'settingsSections' => SettingsNavigation::for($request->user()),
        ]);
    }

    /**
     * Markdown to HTML, with an id on every heading and a contents list
     * built in the same pass.
     *
     * `html_input => strip` even though these files are ours: they are read
     * off disk at request time, and a page that renders whatever HTML
     * happens to be in a file is one bad deployment away from being an
     * injection point.
     *
     * @return array{0: string, 1: array<int, array{id: string, title: string}>}
     */
    private function render(string $markdown, string $prefix): array
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $contents = [];

        // The first h1 is the guide's own title, which the page already
        // shows in its header.
        $html = (string) preg_replace('/<h1>.*?<\/h1>/s', '', $html, 1);

        $html = (string) preg_replace_callback(
            '/<h([23])>(.*?)<\/h\1>/s',
            function (array $match) use ($prefix, &$contents): string {
                $text = trim(html_entity_decode(strip_tags($match[2])));
                $id = $prefix.'-'.Str::slug($text);

                // Only h2 reaches the contents list; h3 is detail within a
                // topic and would bury the topics themselves.
                if ($match[1] === '2') {
                    $contents[] = ['id' => $id, 'title' => $text];
                }

                return '<h'.$match[1].' id="'.e($id).'">'.$match[2].'</h'.$match[1].'>';
            },
            $html
        );

        // Relative links between the two files land on the same page here.
        $html = str_replace(
            ['href="ADMIN_GUIDE.md"', 'href="USER_GUIDE.md"'],
            ['href="#admin"', 'href="#user"'],
            $html
        );

        // The guides lean on tables, which are the first thing to push a
        // phone sideways. Each gets its own horizontal scroller - there is
        // no element in generated markdown to hang that on otherwise.
        $html = str_replace(
            ['<table>', '</table>'],
            ['<div class="guide-table-scroll"><table class="table table-sm">', '</table></div>'],
            $html
        );

        return [$html, $contents];
    }
}
