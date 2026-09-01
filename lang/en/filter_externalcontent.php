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

$string['filtername'] = 'External content highlighter';
$string['pluginname'] = 'External content highlighter';
$string['privacy:metadata'] = 'The External content highlighter filter plugin does not store any personal data.';

$string['settings:backgroundcolour'] = 'Label background colour';
$string['settings:backgroundcolour_desc'] = 'The background colour of the label appended after matching links.';
$string['settings:domains'] = 'Domains to highlight';
$string['settings:domains_desc'] = 'A list of domains (one per line) that should be visually flagged as external ' .
    'content when linked to in Moodle text. Prefix an entry with "*." to also match all of its subdomains, ' .
    'e.g. *.example.com.';
$string['settings:label'] = 'Label text';
$string['settings:label_desc'] = 'The text displayed in the label appended after matching links.';
$string['settings:showindicator'] = 'Show label';
$string['settings:showindicator_desc'] = 'Whether to append the label next to matching links.';
$string['settings:textcolour'] = 'Label text colour';
$string['settings:textcolour_desc'] = 'The text colour of the label appended after matching links.';
