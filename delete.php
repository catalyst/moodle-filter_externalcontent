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
 * Delete a highlight, with confirmation.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use filter_externalcontent\records_manager;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = required_param('id', PARAM_ALPHANUM);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$manageurl = new moodle_url('/admin/settings.php', ['section' => 'filtersettingexternalcontent']);
$deleteurl = new moodle_url('/filter/externalcontent/delete.php', ['id' => $id]);

$PAGE->set_context($context);
$PAGE->set_url($deleteurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading(get_string('pluginname', 'filter_externalcontent'));
$PAGE->set_title(get_string('delete_heading', 'filter_externalcontent'));
navigation_node::require_admin_tree();
$PAGE->navbar->add(get_string('manage_heading', 'filter_externalcontent'), $manageurl);

$manager = new records_manager();
$record = $manager->get($id);

if (empty($record)) {
    throw new moodle_exception('not_found', 'filter_externalcontent', $manageurl);
}

if ($confirm !== md5($id)) {
    $deleteurl = new moodle_url('/filter/externalcontent/delete.php', [
        'id' => $id,
        'confirm' => md5($id),
        'sesskey' => sesskey(),
    ]);

    $PAGE->navbar->add(get_string('delete_breadcrumb', 'filter_externalcontent'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('delete_heading', 'filter_externalcontent'));
    echo $OUTPUT->confirm(get_string('delete_confirm', 'filter_externalcontent', s($record->name)), $deleteurl, $manageurl);
    echo $OUTPUT->footer();
} else if (data_submitted() && confirm_sesskey()) {
    $manager->delete($id);
    redirect($manageurl);
}
