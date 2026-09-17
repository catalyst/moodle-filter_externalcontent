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

namespace filter_externalcontent;

/**
 * Pass-through text filter for external content highlighting.
 *
 * Moodle requires every filter plugin to implement a text_filter class so it can
 * be installed, discovered, and managed (enabled or disabled) in site administration.
 *
 * Rather than using the traditional text-filtering approach of parsing and mutating
 * rendered HTML strings, this plugin uses a pure CSS solution:
 *  - This filter returns $text untouched to avoid regex overhead and HTML mutation.
 *  - Highlighting rules are compiled on the server and injected once per page load via
 *    filter_externalcontent_before_standard_top_of_body_html() in lib.php.
 *  - That hook checks the 'filter/externalcontent:view' capability before outputting
 *    a <style> tag containing CSS attribute selectors targeting matching href/src attributes.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {
    #[\Override]
    public function filter($text, array $options = []): string {
        return (string) $text;
    }
}
