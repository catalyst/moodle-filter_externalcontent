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
 * Dedicated AJAX endpoint for the highlight row actions (toggle enabled
 * state, delete), called from amd/src/manage_highlights.js.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use filter_externalcontent\records_manager;

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = required_param('id', PARAM_ALPHANUM);
$action = required_param('action', PARAM_ALPHA);

$PAGE->set_url(new moodle_url('/filter/externalcontent/ajax.php', ['id' => $id, 'action' => $action]));
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

echo $OUTPUT->header(); // Send headers, using core_renderer_ajax (see AJAX_SCRIPT above).

$manager = new records_manager();
$manager->perform_action($id, $action);

echo json_encode(['success' => true]);
die();
