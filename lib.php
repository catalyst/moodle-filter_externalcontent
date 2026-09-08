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

/**
 * Library functions for filter_externalcontent.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
use filter_externalcontent\local\table\highlights_table;
use filter_externalcontent\records_manager;

/**
 * Fragment to load the highlight table.
 *
 * @param array $args must contain 'context', added automatically by the
 *                     core_get_fragment web service.
 * @return string
 */
function filter_externalcontent_output_fragment_highlights_table(array $args): string {
    $context = $args['context'] ?? context_system::instance();
    require_capability('moodle/site:config', $context);

    $manager = new records_manager();
    $table = new highlights_table('filter_externalcontent_settings_highlights');

    ob_start();
    $table->display_records($manager->get_all());
    return ob_get_clean();
}
