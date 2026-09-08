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
 * Builds the label/outline markup for a highlight.
 *
 * This is shared by text_filter (which decorates real links found in page
 * content) and edit.php (which uses it to render a live "what will this
 * look like" preview on the highlight edit form), so both places always stay
 * visually in sync.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class highlight_renderer {
    /** @var string default label background colour, used as a fallback when a highlight's colour is missing/invalid. */
    const DEFAULT_BACKGROUND_COLOUR = '#f0ad4e';

    /** @var string default label text colour, used as a fallback when a highlight's colour is missing/invalid. */
    const DEFAULT_TEXT_COLOUR = '#ffffff';
}
