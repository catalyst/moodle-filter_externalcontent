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

namespace filter_externalcontent\table;

use flexible_table;
use html_writer;
use moodle_url;
use pix_icon;
use stdClass;

/**
 * Table listing the configured highlights.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class highlights_table extends flexible_table {
    /**
     * Constructor.
     *
     * @param string $uniqueid
     */
    public function __construct(string $uniqueid = 'filter_externalcontent_highlights') {
        global $PAGE;

        parent::__construct($uniqueid);

        $this->define_baseurl($PAGE->url);
        $this->set_attribute('class', 'generaltable admintable');

        $this->define_columns(['name', 'domains', 'label', 'actions']);
        $this->define_headers([
            get_string('name', 'filter_externalcontent'),
            get_string('settings:domains', 'filter_externalcontent'),
            get_string('settings:label', 'filter_externalcontent'),
            get_string('actions'),
        ]);

        $this->setup();
    }

    /**
     * Display column name.
     *
     * @param stdClass $record
     * @return string
     */
    public function col_name(stdClass $record): string {
        return s($record->name);
    }

    /**
     * Display column domains.
     *
     * @param stdClass $record
     * @return string
     */
    public function col_domains(stdClass $record): string {
        return html_writer::tag('code', s($record->domains));
    }

    /**
     * Display column label.
     *
     * @param stdClass $record
     * @return string
     */
    public function col_label(stdClass $record): string {
        return s($record->label);
    }

    /**
     * Display column action.
     *
     * @param stdClass $record
     * @return string
     */
    public function col_actions(stdClass $record): string {
        global $OUTPUT;

        $buttons = [];

        $action = !empty($record->enabled) ? 'hide' : 'show';
        $title = !empty($record->enabled) ? get_string('disable') : get_string('enable');
        $buttons[] = $OUTPUT->action_icon(
            new moodle_url('/filter/externalcontent/status.php', ['id' => $record->id, 'sesskey' => sesskey()]),
            new pix_icon('t/' . $action, $title)
        );

        $buttons[] = $OUTPUT->action_icon(
            new moodle_url('/filter/externalcontent/edit.php', ['id' => $record->id]),
            new pix_icon('t/edit', get_string('edit'))
        );

        $buttons[] = $OUTPUT->action_icon(
            new moodle_url('/filter/externalcontent/delete.php', ['id' => $record->id]),
            new pix_icon('t/delete', get_string('delete'))
        );

        return html_writer::tag('nobr', implode(' ', $buttons));
    }

    /**
     * Render the given highlight records.
     *
     * @param stdClass[] $records
     */
    public function display_records(array $records): void {
        foreach ($records as $record) {
            $class = empty($record->enabled) ? 'dimmed_text' : '';
            $this->add_data_keyed($this->format_row($record), $class);
        }

        $this->finish_output();
    }

    /**
     * Display no results message.
     */
    public function print_nothing_to_display() {
        echo html_writer::div(get_string('no_highlights', 'filter_externalcontent'));
    }
}
