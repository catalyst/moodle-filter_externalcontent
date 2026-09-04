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
 * Strings for component 'filter_externalcontent', language 'en'.
 *
 * @package   filter_externalcontent
 * @author    Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright 2026 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['add_breadcrumb'] = 'Add highlight';
$string['add_heading'] = 'Add highlight';
$string['add_highlight'] = 'Add a new highlight';
$string['delete_breadcrumb'] = 'Delete highlight';
$string['delete_confirm'] = 'Are you sure you want to delete the highlight "{$a}"?';
$string['delete_heading'] = 'Delete highlight';
$string['edit_breadcrumb'] = 'Edit highlight';
$string['edit_heading'] = 'Edit highlight';
$string['enabled'] = 'Enabled';
$string['error:invalidcolour'] = 'Enter a valid hex colour, e.g. #f0ad4e or #fff.';
$string['filtername'] = 'External content highlighter';
$string['manage_heading'] = 'Manage external content highlights';
$string['name'] = 'Name';
$string['no_highlights'] = 'No highlights configured yet.';
$string['not_found'] = 'Highlight not found';
$string['pluginname'] = 'External content highlighter';
$string['preview_heading'] = 'Preview';
$string['preview_samplelabel'] = 'External';
$string['preview_samplelink'] = 'Example external link';
$string['privacy:metadata'] = 'The External content highlighter filter plugin does not store any personal data.';
$string['settings:backgroundcolour'] = 'Label background colour';
$string['settings:backgroundcolour_desc'] = 'The background colour of the label appended after matching links.';
$string['settings:backgroundcolour_help'] = 'The background colour of the label appended after matching links. ' .
    'Enter a hex colour, e.g. #f0ad4e or #fff.';
$string['settings:domains'] = 'Domains to highlight';
$string['settings:domains_desc'] = 'A list of domains (one per line) that should be visually flagged as external ' .
    'content when linked to in Moodle text. Prefix an entry with "*." to also match all of its subdomains, ' .
    'e.g. *.example.com.';
$string['settings:domains_help'] = 'A list of domains (one per line) that should be visually flagged as external ' .
    'content when linked to in Moodle text. Prefix an entry with "*." to also match all of its subdomains, ' .
    'e.g. *.example.com.';
$string['settings:label'] = 'Label text';
$string['settings:label_desc'] = 'The text displayed in the label prepended before matching links.';
$string['settings:textcolour'] = 'Label text colour';
$string['settings:textcolour_desc'] = 'The text colour of the label appended after matching links.';
$string['settings:textcolour_help'] = 'The text colour of the label appended after matching links. Enter a hex ' .
    'colour, e.g. #ffffff or #fff.';
