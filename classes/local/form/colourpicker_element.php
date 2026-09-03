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
 * A moodleform element that uses the same colour picker widget as
 * admin_setting_configcolourpicker
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_externalcontent\local\form;
use html_writer;
use MoodleQuickForm_editor;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/editor.php');

/**
 * Colour picker moodleform element.
 *
 * @copyright  2026 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_filter_externalcontent_colourpicker extends MoodleQuickForm_editor {
    /**
     * Sets the value of the form element.
     *
     * @param string $value
     */
    public function setvalue($value) {
        $this->updateAttributes(['value' => $value]);
    }

    /**
     * Gets the value of the form element.
     *
     * @return string
     */
    public function getvalue() {
        return $this->getAttribute('value');
    }

    /**
     * Returns the html string to display this element, wrapping a plain text
     * input with the markup the colour picker JS widget expects.
     *
     * @return string
     */
    public function tohtml() {
        global $PAGE, $OUTPUT;

        $id = $this->getAttribute('id');
        $PAGE->requires->js_init_call('M.util.init_colour_picker', [$id, null]);

        // Bootstrap's .form-control forces width:100%, which overrides the size
        // attribute below and stretches the input across the whole form row.
        // Cap it with an inline max-width so it stays a compact colour field.
        $content = html_writer::start_tag('div', ['class' => 'form-colourpicker defaultsnext',
                'style' => 'max-width: 10em;']);
        $content .= html_writer::tag('div', $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', [
                'class' => 'loadingicon',
        ]), ['class' => 'admin_colourpicker clearfix']);
        $content .= html_writer::empty_tag('input', [
                'type' => 'text',
                'id' => $id,
                'name' => $this->getName(),
                'value' => $this->getValue(),
                'size' => 12,
                'class' => 'form-control text-ltr',
                'style' => 'max-width: 10em;',
        ]);
        $content .= html_writer::end_tag('div');

        return $content;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a mustache template.
     *
     * @param \renderer_base $output
     * @return \stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        $context = $this->export_for_template_base($output);
        $context['html'] = $this->toHtml();

        return $context;
    }
}
