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

use stdClass;

/**
 * Builds CSS for external-content highlighting.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class highlight_renderer {
    /** @var string default label background colour. */
    public const DEFAULT_BACKGROUND_COLOUR = '#f0ad4e';

    /** @var string default label text colour. */
    public const DEFAULT_TEXT_COLOUR = '#ffffff';

    /** @var int minimum label lane width (px). */
    private const MIN_LANE_WIDTH_PX = 60;

    /** @var int label lane height (px). */
    private const BADGE_HEIGHT_PX = 20;

    /**
     * Parse a highlight's raw newline-separated 'domains' setting into an
     * exact list and a wildcard list ('*.' prefix, stored without it).
     *
     * @param string $raw the raw 'domains' setting value, one domain per line.
     * @return array{exact: string[], wildcard: string[]}
     */
    public static function parse_domains(string $raw): array {
        $exact = [];
        $wildcard = [];

        foreach (preg_split('/[\r\n]+/', $raw) as $line) {
            $line = trim(strtolower($line));
            if ($line === '') {
                continue;
            }
            if (strpos($line, '*.') === 0) {
                $wildcard[] = substr($line, 2);
            } else {
                $exact[] = $line;
            }
        }

        return ['exact' => $exact, 'wildcard' => $wildcard];
    }

    /**
     * Escape a value for safe use inside a double-quoted CSS string, e.g.
     * a ::before content string.
     *
     * @param string $value
     * @return string
     */
    public static function escape_css_string(string $value): string {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    /**
     * Build selector-driven CSS for one highlight record.
     *
     * @param stdClass $record a raw highlight record (see records_manager).
     * @return string CSS, or '' if no domains are configured.
     */
    public static function build_css_for_record(stdClass $record): string {
        $selectors = self::build_selector_list_for_record($record);
        if (empty($selectors)) {
            return '';
        }

        $backgroundcolour = trim((string) ($record->backgroundcolour ?? ''))
            ?: self::DEFAULT_BACKGROUND_COLOUR;
        $textcolour = trim((string) ($record->textcolour ?? '')) ?: self::DEFAULT_TEXT_COLOUR;
        $selectorblock = implode(",\n", $selectors);

        $css = sprintf("%s {\n    outline:2px solid %s;\n}\n", $selectorblock, $backgroundcolour);

        $label = trim((string) ($record->label ?? ''));
        if ($label !== '') {
            $lanewidth = self::measure_label_lane_width($label);
            $svguri = self::build_svg_data_uri($label, $backgroundcolour, $textcolour, $lanewidth);
            $css .= sprintf(
                "%s {\n    border:0 solid %s;\n    border-left:%dpx solid transparent;\n" .
                "    background-image:url(\"%s\"), linear-gradient(%s, %s);\n" .
                "    background-repeat:no-repeat, no-repeat;\n" .
                "    background-position:left center, left top;\n" .
                "    background-size:%dpx %dpx, %dpx 100%%;\n" .
                "    background-origin:border-box, border-box;\n}\n",
                $selectorblock,
                $backgroundcolour,
                $lanewidth,
                $svguri,
                $backgroundcolour,
                $backgroundcolour,
                $lanewidth,
                self::BADGE_HEIGHT_PX,
                $lanewidth
            );
        }

        return $css;
    }

    /**
     * Build all URL-match selectors for one record.
     *
     * @param stdClass $record
     * @return string[]
     */
    protected static function build_selector_list_for_record(stdClass $record): array {
        $domains = self::parse_domains((string) ($record->domains ?? ''));
        $values = array_unique(array_merge($domains['exact'], $domains['wildcard']));

        $selectors = [];
        foreach ($values as $value) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            $needle = self::escape_css_string($value);
            $selectors[] = ':not(link):not(base):not(meta)[href*="' . $needle . '" i]';
            $selectors[] = ':not(script):not(link):not(base):not(meta)[src*="' . $needle . '" i]';
            $selectors[] = ':not(link):not(base):not(meta)[data*="' . $needle . '" i]';
        }

        return array_values(array_unique($selectors));
    }

    /**
     * Compute lane width from label length.
     *
     * @param string $label
     * @return int
     */
    protected static function measure_label_lane_width(string $label): int {
        $width = (int) ceil(max(0, \core_text::strlen($label)) * 9.5);
        return max(self::MIN_LANE_WIDTH_PX, $width);
    }

    /**
     * Build an SVG data URI used as a label lane.
     *
     * @param string $label
     * @param string $backgroundcolour
     * @param string $textcolour
     * @param int $width
     * @return string
     */
    protected static function build_svg_data_uri(
        string $label,
        string $backgroundcolour,
        string $textcolour,
        int $width
    ): string {
        $fontfamily = 'Roboto, Helvetica Neue, Arial, sans-serif';
        $textx = (int) floor(($width - 2) / 2);
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d">' .
                '<rect x="0" y="0" width="%d" height="%d" fill="%s"/>' .
                '<text x="%d" y="15" text-anchor="middle" font-family="%s" ' .
                'font-size="16px" fill="%s" style="text-rendering:auto;">%s</text>' .
            '</svg>',
            $width,
            self::BADGE_HEIGHT_PX,
            $width,
            self::BADGE_HEIGHT_PX,
            $backgroundcolour,
            $textx,
            $fontfamily,
            $textcolour,
            s($label)
        );

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }
}
