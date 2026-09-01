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
 * Admin settings for the externalcontent filter.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // List of domains that should be visually flagged as external content when
    // they appear in a link. One domain per line. Supports a leading '*.' wildcard
    // to match all subdomains, e.g. '*.example.com'.
    $settings->add(new admin_setting_configtextarea(
        'filter_externalcontent/domains',
        get_string('settings:domains', 'filter_externalcontent'),
        get_string('settings:domains_desc', 'filter_externalcontent'),
        '',
        PARAM_RAW
    ));

    // Background colour used for the label appended after matching links.
    $settings->add(new admin_setting_configcolourpicker(
        'filter_externalcontent/backgroundcolour',
        get_string('settings:backgroundcolour', 'filter_externalcontent'),
        get_string('settings:backgroundcolour_desc', 'filter_externalcontent'),
        '#f0ad4e'
    ));

    // Text colour used for the label appended after matching links.
    $settings->add(new admin_setting_configcolourpicker(
        'filter_externalcontent/textcolour',
        get_string('settings:textcolour', 'filter_externalcontent'),
        get_string('settings:textcolour_desc', 'filter_externalcontent'),
        '#ffffff'
    ));

    // Text displayed in the label appended after matching links.
    $settings->add(new admin_setting_configtext(
        'filter_externalcontent/label',
        get_string('settings:label', 'filter_externalcontent'),
        get_string('settings:label_desc', 'filter_externalcontent'),
        'External',
        PARAM_TEXT
    ));

    // Whether to append the label after matching links.
    $settings->add(new admin_setting_configcheckbox(
        'filter_externalcontent/showindicator',
        get_string('settings:showindicator', 'filter_externalcontent'),
        get_string('settings:showindicator_desc', 'filter_externalcontent'),
        1
    ));
}
