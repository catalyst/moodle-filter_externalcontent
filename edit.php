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
 * Create or edit a single highlight.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use filter_externalcontent\local\form\edit;
use filter_externalcontent\records_manager;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

$id = optional_param('id', '', PARAM_ALPHANUM);

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$manageurl = new moodle_url('/admin/settings.php', ['section' => 'filtersettingexternalcontent']);
$editurl = new moodle_url('/filter/externalcontent/edit.php', ['id' => $id]);

$PAGE->set_context($context);
$PAGE->set_url($editurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('pluginname', 'filter_externalcontent'));
navigation_node::require_admin_tree();
$PAGE->navbar->add(get_string('manage_heading', 'filter_externalcontent'), $manageurl);

$manager = new records_manager();
$action = 'add';
$record = new stdClass();
$record->id = '';

if ($id !== '') {
    $record = $manager->get($id);
    if (empty($record)) {
        throw new moodle_exception('not_found', 'filter_externalcontent', $manageurl);
    }
    $action = 'edit';
}

$mform = new edit($editurl);
$mform->set_data($record);

if ($mform->is_cancelled()) {
    redirect($manageurl);
} else if ($data = $mform->get_data()) {
    $manager->save($data);
    redirect($manageurl);
}

$PAGE->navbar->add(get_string($action . '_breadcrumb', 'filter_externalcontent'));
$PAGE->set_title(get_string($action . '_heading', 'filter_externalcontent'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($action . '_heading', 'filter_externalcontent'));
$mform->display();
echo $OUTPUT->footer();
