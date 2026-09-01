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
    /**
     * Validate a configured colour value, falling back to a default if it is
     * not a valid CSS hex colour.
     *
     * @param mixed $value
     * @param string $default
     * @return string
     */
    public static function sanitise_colour($value, string $default): string {
        $value = trim((string) $value);
        if ($value !== '' && preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value)) {
            return $value;
        }

        return $default;
    }

    /**
     * Build the label markup for a highlight or an empty string if the
     * indicator is disabled or the label text is empty.
     *
     * @param string $label the label text.
     * @param string $backgroundcolour a valid CSS hex colour.
     * @param string $textcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_label_html(string $label, string $backgroundcolour, string $textcolour): string {
        $label = trim($label);
        if ($label === '') {
            return '';
        }

        return \html_writer::tag('span', s($label), [
            'class' => 'filter-externalcontent-label',
            'style' => self::build_label_style($backgroundcolour, $textcolour),
        ]);
    }

    /**
     * Build the CSS style applied to the label span itself, without the
     * wrapping <span> tag, so callers that need to attach extra attributes
     * (e.g. an id for JS to update live, such as the edit form preview) can
     * build their own markup while still sharing the same colour styling.
     *
     * @param string $backgroundcolour a valid CSS hex colour.
     * @param string $textcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_label_style(string $backgroundcolour, string $textcolour): string {
        return sprintf('background-color:%s;color:%s;padding: 1px 4px 1px 2px;', $backgroundcolour, $textcolour);
    }

    /**
     * Build the outline style applied to the wrapping span of a highlighted
     * link.
     *
     * @param string $backgroundcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_outline_style(string $backgroundcolour): string {
        return sprintf('outline:2px solid %s;padding-right:4px;', $backgroundcolour);
    }

    /**
     * Render a sample anchor wrapped exactly like text_filter would wrap a
     * real matching link, from raw (unsanitised) values such as those coming
     * straight out of a moodleform's submitted/default data.
     *
     * @param string $label the label text.
     * @param string $backgroundcolour a raw (possibly invalid) CSS colour.
     * @param string $textcolour a raw (possibly invalid) CSS colour.
     * @param string $linktext the sample link text to display.
     * @param string $href the sample link href.
     * @return string
     */
    public static function render_preview(
        string $label,
        string $backgroundcolour,
        string $textcolour,
        string $linktext,
        string $href = '#'
    ): string {
        $backgroundcolour = self::sanitise_colour($backgroundcolour, '#f0ad4e');
        $textcolour = self::sanitise_colour($textcolour, '#ffffff');

        $labelhtml = self::build_label_html($label, $backgroundcolour, $textcolour);
        $outlinestyle = self::build_outline_style($backgroundcolour);

        $anchor = \html_writer::link($href, s($linktext));

        return \html_writer::tag('span', $labelhtml . $anchor, ['style' => $outlinestyle]);
    }
}
