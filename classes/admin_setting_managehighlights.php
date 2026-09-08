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

use admin_setting_description;
use core\exception\moodle_exception;
use filter_externalcontent\local\table\highlights_table;
use moodle_url;
use stdClass;

/**
 * Admin setting that renders the highlights list (and an "Add" button)
 * directly on the filter's settings page.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_managehighlights extends admin_setting_description {
    /**
     * Add button and table for managing highlights.
     *
     * @param $data
     * @param $query
     * @return string
     * @throws moodle_exception
     */
    #[\Override]
    public function output_html($data, $query = ''): string {
        global $OUTPUT;

        $context = new stdClass();
        $context->title = $this->visiblename;
        $context->description = $this->description . $this->render_manage_ui();

        return $OUTPUT->render_from_template('core_admin/setting_description', $context);
    }

    /**
     * Render the "Add a new highlight" button and the highlights table.
     *
     * @return string
     */
    protected function render_manage_ui(): string {
        global $PAGE;

        $manager = new records_manager();
        $context = \context_system::instance();

        $addurl = new moodle_url('/filter/externalcontent/edit.php');
        $html = \html_writer::link($addurl, get_string('add_highlight', 'filter_externalcontent'), [
            'class' => 'btn btn-primary mb-3',
            'data-action' => 'add-highlight',
        ]);

        $table = new highlights_table('filter_externalcontent_settings_highlights');

        ob_start();
        $table->display_records($manager->get_all());
        $tablehtml = ob_get_clean();

        $html .= \html_writer::div($tablehtml, '', ['id' => 'filter-externalcontent-highlights-table']);

        $PAGE->requires->js_call_amd('filter_externalcontent/manage_highlights', 'init', [$context->id]);

        return $html;
    }
}
