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
    /** @var string default label background colour, used as a fallback when a highlight's colour is missing/invalid. */
    public const DEFAULT_BACKGROUND_COLOUR = '#f0ad4e';

    /** @var string default label text colour, used as a fallback when a highlight's colour is missing/invalid. */
    public const DEFAULT_TEXT_COLOUR = '#ffffff';

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
        return sprintf('background-color:%s;color:%s;padding:0 4px 0 2px;', $backgroundcolour, $textcolour);
    }

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
     * The rules match rendered elements by URL directly, so text_filter does
     * not need to mutate page HTML. Any visible element with href/src/data
     * containing a configured value is outlined.
     *
     * @param stdClass $record a raw highlight record (see records_manager).
     * @return string CSS, or '' if no domains are configured.
     */
    public static function build_css_for_record(stdClass $record): string {
        $selectors = self::build_selector_list_for_record($record);
        if (empty($selectors)) {
            return '';
        }

        $backgroundcolour = trim((string) ($record->backgroundcolour ?? '')) ?: self::DEFAULT_BACKGROUND_COLOUR;
        $textcolour = trim((string) ($record->textcolour ?? '')) ?: self::DEFAULT_TEXT_COLOUR;
        $selectorblock = implode(",\n", $selectors);

        $css = sprintf("%s {\n    outline:2px solid %s;\n    padding-right:4px;\n}\n", $selectorblock, $backgroundcolour);

        $label = trim((string) ($record->label ?? ''));
        if ($label !== '') {
            $beforeselectorblock = implode(",\n", array_map(static function (string $selector): string {
                return $selector . '::before';
            }, $selectors));
            $css .= sprintf(
                "%s {\n    content:\"%s\";\n    margin-right:4px;\n    %s\n    display:inline-block;\n}\n",
                $beforeselectorblock,
                self::escape_css_string($label),
                self::build_label_style($backgroundcolour, $textcolour)
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
     * Build runtime data used by JS to inject labels for replaced elements
     * (e.g. <img>), which cannot render ::before content reliably.
     *
     * @param stdClass $record
     * @return array<string, mixed>|null
     */
    public static function build_label_runtime_data(stdClass $record): ?array {
        $label = trim((string) ($record->label ?? ''));
        if ($label === '') {
            return null;
        }

        $domains = self::parse_domains((string) ($record->domains ?? ''));
        $values = array_values(array_unique(array_merge($domains['exact'], $domains['wildcard'])));
        if (empty($values)) {
            return null;
        }

        $backgroundcolour = trim((string) ($record->backgroundcolour ?? '')) ?: self::DEFAULT_BACKGROUND_COLOUR;
        $textcolour = trim((string) ($record->textcolour ?? '')) ?: self::DEFAULT_TEXT_COLOUR;

        return [
            'values' => $values,
            'label' => $label,
            'backgroundcolour' => $backgroundcolour,
            'textcolour' => $textcolour,
        ];
    }
}
