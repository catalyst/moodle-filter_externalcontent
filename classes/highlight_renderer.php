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
     * padding-right:4px is a cosmetic tweak specific to anchors: since the
     * label is placed inline right before the link text, there is already
     * natural spacing on the left, but the outline would otherwise sit
     * flush against the last character of the link text on the right. This
     * is not needed for media elements (see build_media_outline_style()).
     *
     * @param string $backgroundcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_outline_style(string $backgroundcolour): string {
        return sprintf('outline:2px solid %s;padding-right:4px;', $backgroundcolour);
    }

    /**
     * Build the outline style merged into a highlighted media element's own
     * style (image, iframe, embed, video). Unlike build_outline_style()
     * (used for anchors), this deliberately omits padding-right:4px, which
     * exists only to give some breathing room before wrapped link text and
     * is not relevant to a media element's own box.
     *
     * @param string $backgroundcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_media_outline_style(string $backgroundcolour): string {
        return sprintf('outline:2px solid %s;', $backgroundcolour);
    }

    /**
     * Build the wrapper style used around a highlighted media element
     * (image, iframe, embed, video) that is laid out normally (i.e. not
     * relying on being absolutely positioned by an ancestor). The wrapper
     * establishes a positioning context so the label can be overlaid on top
     * of the element (see build_overlay_label_style()).
     *
     * @return string
     */
    public static function build_media_wrapper_style(): string {
        return 'display:block;position:relative;line-height:0;';
    }

    /**
     * Build the label markup for a highlighted media element (image,
     * iframe, embed, video), overlaid on its top-left corner, or an empty
     * string if the label text is empty.
     *
     * @param string $label the label text.
     * @param string $backgroundcolour a valid CSS hex colour.
     * @param string $textcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_overlay_label_html(string $label, string $backgroundcolour, string $textcolour): string {
        $label = trim($label);
        if ($label === '') {
            return '';
        }

        return \html_writer::tag('span', s($label), [
                'class' => 'filter-externalcontent-label filter-externalcontent-label-overlay',
                'style' => self::build_overlay_label_style($backgroundcolour, $textcolour),
        ]);
    }

    /**
     * Build the CSS style applied to an overlaid media label span itself,
     * without the wrapping <span> tag (see build_overlay_label_html()).
     *
     * @param string $backgroundcolour a valid CSS hex colour.
     * @param string $textcolour a valid CSS hex colour.
     * @return string
     */
    public static function build_overlay_label_style(string $backgroundcolour, string $textcolour): string {
        return sprintf(
            'position:absolute;top:0;left:0;z-index:1;font-size:0.75em;line-height:1.4;' .
            'background-color:%s;color:%s;padding: 1px 4px 1px 2px;',
            $backgroundcolour,
            $textcolour
        );
    }
}
