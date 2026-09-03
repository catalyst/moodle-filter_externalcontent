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

namespace filter_externalcontent\local\form;

use filter_externalcontent\highlight_renderer;
use moodleform;

/**
 * Form used to create/edit a single highlight.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit extends moodleform {
    #[\Override]
    public function definition() {
        require_once(__DIR__ . '/colourpicker_element.php');
        \MoodleQuickForm::registerElementType(
            'filter_externalcontent_colourpicker',
            __DIR__ . '/colourpicker_element.php',
            'filter_externalcontent\local\form\MoodleQuickForm_filter_externalcontent_colourpicker'
        );

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_ALPHANUM);

        $mform->addElement('text', 'name', get_string('name', 'filter_externalcontent'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'filter_externalcontent'));
        $mform->setDefault('enabled', 1);

        $mform->addElement(
            'textarea',
            'domains',
            get_string('settings:domains', 'filter_externalcontent'),
            ['rows' => 4, 'cols' => 40, 'style' => 'max-width: 30em;']
        );
        $mform->setType('domains', PARAM_RAW);
        $mform->addHelpButton('domains', 'settings:domains', 'filter_externalcontent');
        $mform->addRule('domains', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'filter_externalcontent_colourpicker',
            'backgroundcolour',
            get_string('settings:backgroundcolour', 'filter_externalcontent')
        );
        $mform->setType('backgroundcolour', PARAM_TEXT);
        $mform->addHelpButton('backgroundcolour', 'settings:backgroundcolour', 'filter_externalcontent');
        $mform->setDefault('backgroundcolour', '#f0ad4e');

        $mform->addElement(
            'filter_externalcontent_colourpicker',
            'textcolour',
            get_string('settings:textcolour', 'filter_externalcontent')
        );
        $mform->setType('textcolour', PARAM_TEXT);
        $mform->addHelpButton('textcolour', 'settings:textcolour', 'filter_externalcontent');
        $mform->setDefault('textcolour', '#ffffff');

        $mform->addElement('text', 'label', get_string('settings:label', 'filter_externalcontent'));
        $mform->setType('label', PARAM_TEXT);
        $mform->setDefault('label', 'External');

        $this->add_action_buttons();

        // Placeholder preview row, aligned in the same column as the save
        // button above it. Its real content/colours are synced live by JS
        // (see edit.php) as soon as the page loads and whenever the label/
        // colour/indicator fields change, so the placeholder values used
        // here don't matter.
        $previewlabel = \html_writer::tag('span', get_string('preview_samplelabel', 'filter_externalcontent'), [
            'id' => 'filter-externalcontent-preview-label',
            'class' => 'filter-externalcontent-label',
            'style' => highlight_renderer::build_label_style('#f0ad4e', '#ffffff'),
        ]);
        $previewanchor = \html_writer::link('#', get_string('preview_samplelink', 'filter_externalcontent'), [
            'onclick' => 'return false;',
        ]);
        $previewwrap = \html_writer::tag('span', $previewlabel . $previewanchor, [
            'id' => 'filter-externalcontent-preview-wrap',
            'style' => highlight_renderer::build_outline_style('#f0ad4e'),
        ]);

        $mform->addElement('static', 'preview', get_string('preview_heading', 'filter_externalcontent'), $previewwrap);
    }

    /**
     * Server side validation.
     *
     * @param array $data
     * @param array $files
     * @return array errors keyed by element name.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $colourpattern = '/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/';

        if (!empty($data['backgroundcolour']) && !preg_match($colourpattern, trim($data['backgroundcolour']))) {
            $errors['backgroundcolour'] = get_string('error:invalidcolour', 'filter_externalcontent');
        }

        if (!empty($data['textcolour']) && !preg_match($colourpattern, trim($data['textcolour']))) {
            $errors['textcolour'] = get_string('error:invalidcolour', 'filter_externalcontent');
        }

        return $errors;
    }
}
