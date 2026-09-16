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

/**
 * Unit tests for the highlight_renderer's CSS-generation helpers.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_externalcontent\highlight_renderer
 */
final class highlight_renderer_test extends advanced_testcase {
    /**
     * Ensure exact and wildcard domains are split correctly.
     */
    public function test_parse_domains_splits_exact_and_wildcard(): void {
        $result = highlight_renderer::parse_domains("Example.com\n*.Other.com\n\n  \nthird.com");

        $this->assertSame(['example.com', 'third.com'], $result['exact']);
        $this->assertSame(['other.com'], $result['wildcard']);
    }

    /**
     * Ensure CSS string escaping handles slashes and quotes.
     */
    public function test_escape_css_string_escapes_backslash_and_quote(): void {
        $this->assertSame('foo\\\\bar\\"baz', highlight_renderer::escape_css_string('foo\\bar"baz'));
    }

    /**
     * Ensure selector-based CSS includes expected rules.
     */
    public function test_build_css_for_record_contains_url_selectors_and_outline(): void {
        $record = (object) [
            'id' => 'abc123',
            'domains' => "example.com\n*.other.com",
            'backgroundcolour' => '#111111',
            'textcolour' => '#222222',
            'label' => 'External',
        ];

        $css = highlight_renderer::build_css_for_record($record);

        $this->assertStringContainsString('[href*="example.com" i]', $css);
        $this->assertStringContainsString('[src*="example.com" i]', $css);
        $this->assertStringContainsString('[href*="other.com" i]', $css);
        $this->assertStringContainsString('[data*="other.com" i]', $css);
        $this->assertStringContainsString('outline:2px solid #111111;', $css);
        $this->assertStringContainsString('padding-right:4px;', $css);
        $this->assertStringContainsString('[href*="example.com" i]::before', $css);
        $this->assertStringContainsString('content:"External";', $css);
        $this->assertStringContainsString('background-color:#111111;color:#222222;', $css);
        $this->assertStringContainsString('margin-right:4px;', $css);
    }

    /**
     * Ensure no CSS is generated when domains are empty.
     */
    public function test_build_css_for_record_is_empty_without_domains(): void {
        $record = (object) [
            'id' => 'abc123',
            'domains' => '',
            'backgroundcolour' => '#111111',
            'textcolour' => '#222222',
            'label' => '',
        ];

        $this->assertSame('', highlight_renderer::build_css_for_record($record));
    }

    /**
     * Ensure no ::before label rules are generated without a label.
     */
    public function test_build_css_for_record_omits_before_rules_without_label(): void {
        $record = (object) [
            'id' => 'abc123',
            'domains' => 'example.com',
            'backgroundcolour' => '#111111',
            'textcolour' => '#222222',
            'label' => '',
        ];

        $css = highlight_renderer::build_css_for_record($record);

        $this->assertStringContainsString('outline:2px solid #111111;', $css);
        $this->assertStringNotContainsString('::before', $css);
    }

    /**
     * Ensure runtime data for media labels is generated as expected.
     */
    public function test_build_label_runtime_data_returns_label_values_and_styles(): void {
        $record = (object) [
            'domains' => "example.com\n*.other.com",
            'backgroundcolour' => '#111111',
            'textcolour' => '#222222',
            'label' => 'External',
        ];

        $data = highlight_renderer::build_label_runtime_data($record);

        $this->assertNotNull($data);
        $this->assertSame(['example.com', 'other.com'], $data['values']);
        $this->assertSame('External', $data['label']);
        $this->assertSame('#111111', $data['backgroundcolour']);
        $this->assertSame('#222222', $data['textcolour']);
    }
}
