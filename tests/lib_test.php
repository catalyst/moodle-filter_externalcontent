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
 * Unit tests for filter_externalcontent's lib.php callbacks.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::filter_externalcontent_before_standard_top_of_body_html
 */
final class lib_test extends advanced_testcase {
    /**
     * Ensure lib.php (holding the callback under test) is loaded.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../lib.php');
        $this->setAdminUser();
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
            'backgroundcolour' => highlight_renderer::DEFAULT_BACKGROUND_COLOUR,
            'textcolour' => highlight_renderer::DEFAULT_TEXT_COLOUR,
            'label' => 'External',
        ], $overrides);

        $manager = new records_manager();

        return $manager->save($record);
    }

    /**
     * Ensure no style block is rendered when nothing is configured.
     */
    public function test_no_highlights_produces_no_style_block(): void {
        $this->resetAfterTest();

        $this->assertSame('', filter_externalcontent_before_standard_top_of_body_html());
    }

    /**
     * Ensure disabled highlights do not emit CSS.
     */
    public function test_disabled_highlight_is_excluded(): void {
        $this->resetAfterTest();
        $this->create_highlight(['enabled' => 0]);

        $this->assertSame('', filter_externalcontent_before_standard_top_of_body_html());
    }

    /**
     * Ensure wildcard domain highlights are represented in emitted CSS.
     */
    public function test_wildcard_domain_highlight_emits_style_block(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => '*.example.com']);

        $html = filter_externalcontent_before_standard_top_of_body_html();

        $this->assertStringContainsString('[href*="example.com" i]', $html);
        $this->assertStringContainsString('[src*="example.com" i]', $html);
    }

    /**
     * Ensure enabled exact-domain highlights emit complete style output.
     */
    public function test_enabled_exact_domain_highlight_emits_style_block(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $html = filter_externalcontent_before_standard_top_of_body_html();

        $this->assertStringContainsString('<style id="filter-externalcontent-anchor-css">', $html);
        $this->assertStringContainsString('[href*="example.com" i]', $html);
        $this->assertStringContainsString('outline:2px solid #f0ad4e;', $html);
        $this->assertStringContainsString('border-left:', $html);
        $this->assertStringContainsString('background-image:url("data:image/svg+xml,', $html);
    }

    /**
     * Ensure CSS is emitted for every enabled highlight.
     */
    public function test_multiple_highlights_are_all_included(): void {
        $this->resetAfterTest();
        $this->create_highlight(['domains' => 'example.com', 'label' => 'First']);
        $this->create_highlight(['domains' => 'other.com', 'label' => 'Second']);

        $html = filter_externalcontent_before_standard_top_of_body_html();

        $this->assertStringContainsString('[href*="example.com" i]', $html);
        $this->assertStringContainsString('[href*="other.com" i]', $html);
    }

    /**
     * Ensure users without the view capability do not get output.
     */
    public function test_user_without_view_capability_gets_no_output(): void {
        $this->resetAfterTest();
        $this->create_highlight();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertSame('', filter_externalcontent_before_standard_top_of_body_html());
    }

    /**
     * Ensure users with the view capability get output.
     */
    public function test_user_with_view_capability_gets_output(): void {
        $this->resetAfterTest();
        $this->create_highlight();

        $user = $this->getDataGenerator()->create_user();
        $roleid = create_role(
            'Externalcontent viewer',
            'externalcontentviewer',
            'Can view external content highlights'
        );
        assign_capability(
            'filter/externalcontent:view',
            CAP_ALLOW,
            $roleid,
            \context_system::instance()->id,
            true
        );
        role_assign($roleid, $user->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();
        $this->setUser($user);

        $html = filter_externalcontent_before_standard_top_of_body_html();
        $this->assertStringContainsString('<style id="filter-externalcontent-anchor-css">', $html);
    }
}
