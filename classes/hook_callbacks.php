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

use core\hook\output\before_standard_top_of_body_html_generation;

/**
 * Hook callbacks for filter_externalcontent.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Inject generated CSS rules for every configured highlight once near
     * the top of every page's <body>.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(
        before_standard_top_of_body_html_generation $hook
    ): void {
        $systemcontext = \context_system::instance();
        if (!has_capability('filter/externalcontent:view', $systemcontext)) {
            return;
        }

        $manager = new records_manager();

        $css = '';
        foreach ($manager->get_enabled() as $record) {
            $css .= highlight_renderer::build_css_for_record($record);
        }

        if ($css === '') {
            return;
        }

        $hook->add_html(\html_writer::tag('style', $css, ['id' => 'filter-externalcontent-anchor-css']));
    }
}
