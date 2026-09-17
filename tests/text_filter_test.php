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
 * Unit tests for the externalcontent text filter.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_externalcontent\text_filter
 */
final class text_filter_test extends advanced_testcase {
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
     * Ensure plain text passes through unchanged.
     */
    public function test_filter_keeps_plain_text_unchanged(): void {
        $this->assertSame('plain text', $this->filter('plain text'));
    }

    /**
     * Ensure HTML anchors pass through unchanged.
     */
    public function test_filter_keeps_matching_anchor_unchanged(): void {
        $html = '<a href="https://example.com/page">link</a>';
        $this->assertSame($html, $this->filter($html));
    }

    /**
     * Ensure media tags pass through unchanged.
     */
    public function test_filter_keeps_matching_media_unchanged(): void {
        $html = '<iframe src="https://example.com/embed"></iframe>';
        $this->assertSame($html, $this->filter($html));
    }
}
