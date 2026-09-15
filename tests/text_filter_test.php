<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace filter_externalcontent;

use advanced_testcase;
use stdClass;

/**
 * Unit tests for the externalcontent filter.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_externalcontent\text_filter
 */
final class text_filter_test extends advanced_testcase {
    /**
     * Default highlight background colour used throughout these tests
     * (rather than repeating the literal hex value in every assertion)
     * so that changing highlight_renderer::DEFAULT_BACKGROUND_COLOUR
     * doesn't require updating every test.
     */
    private const BGCOLOUR = highlight_renderer::DEFAULT_BACKGROUND_COLOUR;

    /**
     * Default highlight text colour used throughout these tests, see
     * self::BGCOLOUR.
     */
    private const TEXTCOLOUR = highlight_renderer::DEFAULT_TEXT_COLOUR;

    /**
     * Helper to run the filter over a piece of text.
     *
     * @param string $text
     * @return string
     */
    protected function filter(string $text): string {
        $filter = new text_filter(\context_system::instance(), []);
        return $filter->filter($text);
    }

    /**
     * Helper to create a highlight record via the records manager.
     *
     * @param array $overrides properties to override the defaults with.
     * @return string the id of the created highlight.
     */
    protected function create_highlight(array $overrides = []): string {
        $record = (object) array_merge([
            'name' => 'Test highlight',
            'enabled' => 1,
            'domains' => 'example.com',
            'backgroundcolour' => self::BGCOLOUR,
            'textcolour' => self::TEXTCOLOUR,
            'label' => 'External',
        ], $overrides);

        $manager = new records_manager();

        return $manager->save($record);
    }

    public function test_no_highlights_configured_leaves_text_unchanged(): void {
        $this->resetAfterTest();

        $html = '<a href="https://example.com/page">link</a>';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_disabled_highlight_is_ignored(): void {
        $this->resetAfterTest();
        $this->create_highlight(['enabled' => 0]);

        $html = '<a href="https://example.com/page">link</a>';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_matching_domain_wraps_anchor_with_outline(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<a href="https://example.com/page">link</a>';
        $result = $this->filter($html);
        $expectedlabel = '<span class="filter-externalcontent-label" ' .
                'style="background-color:' . self::BGCOLOUR . ';color:' .
                self::TEXTCOLOUR . ';padding: 1px 4px 1px 2px;">External</span>';
        $expected = '<span style="outline:2px solid ' . self::BGCOLOUR .
                ';padding-right:4px;">' . $expectedlabel . $html . '</span>';
        $this->assertSame($expected, $result);
    }

    public function test_original_anchor_style_is_untouched(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<a href="https://example.com/page" style="color:blue;">link</a>';
        $result = $this->filter($html);

        // The anchor's own style attribute must be preserved as-is, since the
        // outline is applied via a wrapping span rather than merged into it.
        $this->assertStringContainsString('style="color:blue;"', $result);
    }

    public function test_matching_domain_gets_label_and_anchor_in_same_box(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<a href="https://example.com/page">link</a>';
        $result = $this->filter($html);

        $expectedlabel = '<span class="filter-externalcontent-label" ' .
            'style="background-color:' . self::BGCOLOUR . ';color:' .
                self::TEXTCOLOUR .
                ';padding: 1px 4px 1px 2px;">External</span>';

        // The label and the anchor must both be inside the same outlined wrapper span.
        $expected = '<span style="outline:2px solid ' . self::BGCOLOUR .
                ';padding-right:4px;">' . $expectedlabel . $html . '</span>';
        $this->assertSame($expected, $result);
    }

    public function test_label_not_shown_when_label_text_empty(): void {
        $this->resetAfterTest();
        $this->create_highlight(['label' => '']);

        $html = '<a href="https://example.com/page">link</a>';
        $result = $this->filter($html);

        $this->assertStringNotContainsString('filter-externalcontent-label', $result);
    }

    public function test_non_matching_domain_is_untouched(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => 'other.com']);

        $html = '<a href="https://example.com/page">link</a>';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_wildcard_domain_matches_subdomains(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => '*.example.com']);

        $html = '<a href="https://sub.example.com/page">link</a>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label', $result);
    }

    public function test_invalid_colour_falls_back_to_default(): void {
        $this->resetAfterTest();
        $this->create_highlight(['backgroundcolour' => 'not-a-colour']);

        $html = '<a href="https://example.com/page">link</a>';
        $result = $this->filter($html);

        $this->assertStringContainsString('background-color:' . self::BGCOLOUR, $result);
        $this->assertStringContainsString('outline:2px solid ' . self::BGCOLOUR, $result);
    }

    public function test_multiple_highlights_are_all_applied(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => 'example.com', 'label' => 'First', 'backgroundcolour' => '#111111']);
        $this->create_highlight(['domains' => 'other.com', 'label' => 'Second', 'backgroundcolour' => '#222222']);

        $first = $this->filter('<a href="https://example.com/page">link</a>');
        $second = $this->filter('<a href="https://other.com/page">link</a>');

        $this->assertStringContainsString('First', $first);
        $this->assertStringContainsString('#111111', $first);
        $this->assertStringContainsString('Second', $second);
        $this->assertStringContainsString('#222222', $second);
    }

    public function test_settings_are_cached_for_the_request(): void {
        $this->resetAfterTest();
        $id = $this->create_highlight();

        $html = '<a href="https://example.com/page">link</a>';
        $filterinstance = new text_filter(\context_system::instance(), []);
        $first = $filterinstance->filter($html);

        // Disabling the highlight without resetting the request cache must
        // not affect a filter instance that already parsed the highlights once.
        $manager = new records_manager();
        $manager->toggle($id);
        $second = $filterinstance->filter($html);

        $this->assertSame($first, $second);
        $this->assertStringContainsString('outline:2px solid', $second);
    }

    public function test_matching_image_is_wrapped_with_overlay_label(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<img src="https://example.com/pic.png" alt="pic">';
        $result = $this->filter($html);

        // The wrapper only hosts the overlay label and carries no visible
        // styling of its own; the outline is merged directly into the img's
        // own style so it hugs the image's actual rendered box rather than
        // stretching to the wrapper's full width.
        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('display:block;position:relative;line-height:0;', $result);
        $this->assertStringContainsString('src="https://example.com/pic.png"', $result);
        $this->assertStringContainsString('alt="pic"', $result);
        $this->assertStringContainsString('style="outline:2px solid ' . self::BGCOLOUR . ';"', $result);
        $this->assertStringNotContainsString('width:fit-content', $result);
        $this->assertStringNotContainsString('padding-right', $result);
    }

    public function test_non_matching_image_is_untouched(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => 'other.com']);

        $html = '<img src="https://example.com/pic.png" alt="pic">';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_matching_embed_is_wrapped_with_overlay_label(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<embed src="https://example.com/thing.swf">';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('src="https://example.com/thing.swf"', $result);
        $this->assertStringContainsString('outline:2px solid ' . self::BGCOLOUR, $result);
    }

    public function test_matching_iframe_is_wrapped_with_overlay_label(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<iframe src="https://example.com/embed" width="200" height="100"></iframe>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('src="https://example.com/embed"', $result);
        $this->assertStringContainsString('width="200"', $result);
        $this->assertStringContainsString('outline:2px solid ' . self::BGCOLOUR, $result);
        $this->assertStringContainsString('</iframe>', $result);
    }

    public function test_non_matching_iframe_is_untouched(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => 'other.com']);

        $html = '<iframe src="https://example.com/embed"></iframe>';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_matching_video_with_direct_src_is_wrapped(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<video src="https://example.com/movie.mp4" controls></video>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('src="https://example.com/movie.mp4"', $result);
        $this->assertStringContainsString('outline:2px solid ' . self::BGCOLOUR, $result);
    }

    public function test_percentage_width_video_outline_is_merged_into_video_not_wrapper(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        // A responsive video/iframe commonly sizes itself with a literal
        // percentage width, e.g. as part of a theme's "fluid" embed
        // styling. The wrapper must stay plain/unstyled (display:block with
        // no width override, safe for a percentage-sized child), while the
        // outline is merged into the video's own existing style so it still
        // hugs the video's actual rendered box.
        $html = '<video src="https://example.com/movie.mp4" style="width:100%;height:auto;"></video>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('display:block;position:relative;line-height:0;">', $result);
        $this->assertStringContainsString(
            '<video src="https://example.com/movie.mp4" style="width:100%;height:auto;outline:2px solid ' . self::BGCOLOUR,
            $result
        );
        $this->assertStringNotContainsString('width:fit-content', $result);
    }

    public function test_matching_video_with_nested_source_is_wrapped(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<video controls><source src="https://example.com/movie.mp4" type="video/mp4"></video>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('<source src="https://example.com/movie.mp4" type="video/mp4">', $result);
        $this->assertStringContainsString('<video controls style="outline:2px solid ' . self::BGCOLOUR, $result);
    }

    public function test_responsive_absolutely_positioned_video_keeps_its_own_box(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        // Simulates the common responsive-embed pattern where the element
        // is absolutely positioned relative to an ancestor wrapper.
        $html = '<video src="https://example.com/movie.mp4" ' .
            'style="position:absolute;top:0;left:0;width:100%;height:100%;"></video>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString(
            '<span style="position:absolute;top:0;left:0;width:100%;height:100%;outline:2px solid ' . self::BGCOLOUR,
            $result
        );
        $this->assertStringContainsString(
            '<video src="https://example.com/movie.mp4" ' .
            'style="position:static;top:0;left:0;width:100%;height:100%;"></video>',
            $result
        );
    }

    public function test_non_matching_video_is_untouched(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => 'other.com']);

        $html = '<video controls><source src="https://example.com/movie.mp4" type="video/mp4"></video>';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_matching_audio_with_nested_source_is_wrapped(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        // Tag <audio> is handled the same way as <video>: its url can be on a
        // nested <source src="..."> rather than its own 'src' attribute.
        $html = '<audio controls><source src="https://example.com/track.mp3" type="audio/mpeg"></audio>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('<source src="https://example.com/track.mp3" type="audio/mpeg">', $result);
        $this->assertStringContainsString('<audio controls style="outline:2px solid ' . self::BGCOLOUR, $result);
    }

    public function test_matching_custom_tag_with_src_is_wrapped(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        // Any element carrying its own 'src' attribute is treated as a
        // candidate media element, not just a fixed whitelist of tag names -
        // this picks up vendor/custom elements (e.g. a video-player
        // polyfill's own tag) with no code change needed.
        $html = '<ogvjs src="https://example.com/movie.ogv"></ogvjs>';
        $result = $this->filter($html);

        $this->assertStringContainsString('filter-externalcontent-label-overlay', $result);
        $this->assertStringContainsString('src="https://example.com/movie.ogv"', $result);
        $this->assertStringContainsString('style="outline:2px solid ' . self::BGCOLOUR, $result);
    }

    public function test_matching_script_src_is_never_wrapped(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        // Tag <script src="..."> must never be touched even though it matches a
        // highlighted domain: it is never a visible resource, and moving it
        // relative to where the HTML parser expects it could break the page.
        $html = '<script src="https://example.com/widget.js"></script>';
        $this->assertSame($html, $this->filter($html));
    }

    public function test_media_label_not_shown_when_label_text_empty(): void {
        $this->resetAfterTest();
        $this->create_highlight(['label' => '']);

        $html = '<img src="https://example.com/pic.png" alt="pic">';
        $result = $this->filter($html);

        $this->assertStringNotContainsString('filter-externalcontent-label', $result);
    }

    public function test_anchor_and_media_can_both_be_highlighted_in_the_same_text(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = '<a href="https://example.com/page">link</a> and ' .
            '<img src="https://example.com/pic.png" alt="pic">';
        $result = $this->filter($html);

        $this->assertStringContainsString('outline:2px solid ' . self::BGCOLOUR, $result);
        $this->assertStringContainsString('src="https://example.com/pic.png"', $result);
    }
}
