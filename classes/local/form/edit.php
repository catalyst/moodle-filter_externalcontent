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

use context;
use context_system;
use core_form\dynamic_form;
use filter_externalcontent\highlight_renderer;
use filter_externalcontent\records_manager;
use moodle_url;
use stdClass;

/**
 * Form used to create/edit a single highlight, rendered inside a modal via
 * core_form/modalform (see amd/src/manage_highlights.js).
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit extends dynamic_form {
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

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'filter_externalcontent'));
        $mform->setDefault('enabled', 1);

        $mform->addElement('text', 'name', get_string('name', 'filter_externalcontent'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'domains',
            get_string('settings:domains', 'filter_externalcontent'),
            ['rows' => 4, 'cols' => 40, 'style' => 'max-width: 30em;']
        );
        $mform->setType('domains', PARAM_RAW);
        $mform->addHelpButton('domains', 'settings:domains', 'filter_externalcontent');
        $mform->addRule('domains', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'label', get_string('settings:label', 'filter_externalcontent'));
        $mform->setType('label', PARAM_TEXT);
        $mform->setDefault('label', 'External');

        $mform->addElement(
            'filter_externalcontent_colourpicker',
            'textcolour',
            get_string('settings:textcolour', 'filter_externalcontent')
        );
        $mform->setType('textcolour', PARAM_TEXT);
        $mform->addHelpButton('textcolour', 'settings:textcolour', 'filter_externalcontent');
        $mform->setDefault('textcolour', highlight_renderer::DEFAULT_TEXT_COLOUR);

        $mform->addElement(
            'filter_externalcontent_colourpicker',
            'backgroundcolour',
            get_string('settings:backgroundcolour', 'filter_externalcontent')
        );
        $mform->setType('backgroundcolour', PARAM_TEXT);
        $mform->addHelpButton('backgroundcolour', 'settings:backgroundcolour', 'filter_externalcontent');
        $mform->setDefault('backgroundcolour', highlight_renderer::DEFAULT_BACKGROUND_COLOUR);

        $mform->addElement('static', 'preview', get_string('preview_heading', 'filter_externalcontent'), '');

        if ($this->_ajaxformdata === null) {
            $this->add_action_buttons();
        }
    }

    #[\Override]
    public function render() {
        $this->_form->getElement('preview')->setText($this->render_preview());

        return parent::render();
    }

    /**
     * Build the live preview markup.
     *
     * @return string
     */
    protected function render_preview(): string {
        global $OUTPUT;

        return $OUTPUT->render_from_template('filter_externalcontent/highlight_preview', [
            'defaultbackground' => highlight_renderer::DEFAULT_BACKGROUND_COLOUR,
            'defaulttext' => highlight_renderer::DEFAULT_TEXT_COLOUR,
        ]);
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

    /**
     * Returns context where this form is used.
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Check if current user has access to this form, otherwise throws exception.
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('moodle/site:config', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX.
     *
     * @return stdClass the saved record, so the caller JS can refresh the table.
     */
    public function process_dynamic_submission(): stdClass {
        $data = $this->get_data();

        $manager = new records_manager();
        $data->id = $manager->save($data);

        return $data;
    }

    /**
     * Load in existing data as form defaults.
     */
    public function set_data_for_dynamic_submission(): void {
        $id = $this->optional_param('id', '', PARAM_ALPHANUM);

        $record = new stdClass();
        $record->id = '';

        if ($id !== '') {
            $manager = new records_manager();
            $record = $manager->get($id);
            if (empty($record)) {
                throw new \moodle_exception('not_found', 'filter_externalcontent');
            }
        }

        $this->set_data($record);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX.
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/admin/settings.php', ['section' => 'filtersettingexternalcontent']);
    }
}
