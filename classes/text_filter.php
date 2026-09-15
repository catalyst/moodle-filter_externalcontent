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

use cache;
use cache_store;
use stdClass;

/**
 * Filter that visually highlights links pointing to domains configured in
 * one or more "highlights" (see records_manager / settings.php).
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {
    /** @var null|cache request-scoped cache holding the parsed highlights. */
    protected ?cache $cache = null;

    #[\Override]
    public function filter($text, array $options = []): string {
        // Quick check to avoid unnecessary work.
        if (empty($text) || !$this->may_contain_supported_element($text)) {
            return $text;
        }

        $highlights = $this->get_highlights();
        if (empty($highlights)) {
            return $text;
        }

        $text = $this->filter_anchors($text, $highlights);
        $text = $this->filter_media($text, $highlights);

        return $text;
    }

    /**
     * Tags whose url may be on a nested <source src="..."> child instead of
     * their own 'src' attribute, e.g. <video><source src="..."></video>.
     * Handled as a special case by filter_media_with_nested_source(); the
     * whole block is highlighted, never the inner <source> alone (which has
     * no visual box of its own to outline).
     *
     * @var string[]
     */
    protected const NESTED_SOURCE_TAGS = ['video', 'audio'];

    /**
     * Tags that must never be wrapped even though they may carry a 'src'
     * attribute: <script>/<link>/<base>/<meta> never point at a visible
     * resource we could sensibly outline.
     *
     * @var string[]
     */
    protected const NONVISUAL_SRC_TAGS = ['script', 'link', 'base', 'meta', 'source', 'track'];

    /**
     * Void (self-closing, no closing tag) HTML elements. Used to decide,
     * when scanning generically for any element with its own 'src'
     * attribute, whether to bother looking for a matching closing tag.
     *
     * @var string[]
     */
    protected const VOID_TAGS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link',
        'meta', 'param', 'source', 'track', 'wbr',
    ];

    /**
     * Reducing the quantity of text to run the regex on.
     *
     * @param string $text
     * @return bool
     */
    protected function may_contain_supported_element(string $text): bool {
        return stripos($text, '<a ') !== false || stripos($text, 'src=') !== false;
    }

    /**
     * Highlight matching anchor (<a href="...">) elements.
     *
     * @param string $text
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return string
     */
    protected function filter_anchors(string $text, array $highlights): string {
        // Match the whole anchor element (opening tag, content, closing tag) so
        // that the label can be included inside the same wrapping box.
        $pattern = '/<a\s+[^>]*href\s*=\s*["\']([^"\']+)["\'][^>]*>.*?<\/a>/is';

        return preg_replace_callback($pattern, function ($matches) use ($highlights) {
            return $this->process_link($matches[0], $matches[1], $highlights);
        }, $text);
    }

    /**
     * Highlight matching embedded media elements.
     *
     * Rather than a fixed whitelist of tag names, any element carrying its
     * own 'src' attribute is treated as a candidate media element (see
     * filter_generic_src_elements()). This means elements this filter was
     * never explicitly written for - <audio>, or a vendor/custom tag such
     * as a video-player polyfill's own element - are picked up automatically
     * as long as they expose a plain 'src' attribute, with no code change
     * needed to support them.
     *
     * @param string $text
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return string
     */
    protected function filter_media(string $text, array $highlights): string {
        $text = $this->filter_media_with_nested_source($text, $highlights);

        return $this->filter_generic_src_elements($text, $highlights);
    }

    /**
     * Highlight <video>/<audio> elements, whose url may be on their own
     * 'src' attribute or on a nested <source src="..."> child.
     *
     * @param string $text
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return string
     */
    protected function filter_media_with_nested_source(string $text, array $highlights): string {
        $pattern = '/<(' . implode('|', self::NESTED_SOURCE_TAGS) . ')\b[^>]*>.*?<\/\1>/is';

        return preg_replace_callback($pattern, function ($matches) use ($highlights) {
            $opentag = $this->extract_opening_tag($matches[0]);
            $url = $this->extract_attribute($opentag, 'src');
            if (empty($url) && preg_match('/<source\b[^>]*>/i', $matches[0], $sourcematch)) {
                $url = $this->extract_attribute($sourcematch[0], 'src');
            }
            return $this->process_media($matches[0], $opentag, $url ?? '', $highlights);
        }, $text);
    }

    /**
     * Highlight any element (other than <a>, and <video>/<audio> already
     * handled by filter_media_with_nested_source()) that carries its own
     * 'src' attribute, unless it is in the non-visual denylist.
     *
     * @param string $text
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return string
     */
    protected function filter_generic_src_elements(string $text, array $highlights): string {
        $pattern = '/<([a-zA-Z][a-zA-Z0-9-]*)\b[^>]*\bsrc\s*=\s*["\']([^"\']*)["\'][^>]*\/?>/is';

        if (!preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return $text;
        }

        $result = '';
        $cursor = 0;

        foreach ($matches[0] as $index => $openmatch) {
            [$opentag, $openoffset] = $openmatch;

            if ($openoffset < $cursor) {
                // Nested inside an element already consumed above (e.g. a
                // <source> inside a <video>, or a src-bearing tag inside an
                // already-highlighted element's fallback content); it was
                // already carried over verbatim as part of that element.
                continue;
            }

            $tagname = strtolower($matches[1][$index][0]);
            $url = $matches[2][$index][0];
            $openend = $openoffset + strlen($opentag);

            if (in_array($tagname, self::NONVISUAL_SRC_TAGS, true) || in_array($tagname, self::NESTED_SOURCE_TAGS, true)) {
                // Either not a visible/highlightable resource, or already
                // handled by filter_media_with_nested_source().
                $result .= substr($text, $cursor, $openend - $cursor);
                $cursor = $openend;
                continue;
            }

            $element = $opentag;
            $elementend = $openend;

            $isvoid = in_array($tagname, self::VOID_TAGS, true) || str_ends_with(rtrim($opentag, '>'), '/');
            if (!$isvoid) {
                $closepattern = '/<\/' . preg_quote($tagname, '/') . '\s*>/i';
                if (preg_match($closepattern, $text, $closematch, PREG_OFFSET_CAPTURE, $openend)) {
                    $elementend = $closematch[0][1] + strlen($closematch[0][0]);
                    $element = substr($text, $openoffset, $elementend - $openoffset);
                }
            }

            $result .= substr($text, $cursor, $openoffset - $cursor);
            $result .= $this->process_media($element, $opentag, $url, $highlights);
            $cursor = $elementend;
        }

        $result .= substr($text, $cursor);

        return $result;
    }

    /**
     * Extract the value of an attribute from a single HTML opening tag.
     *
     * @param string $tag e.g. '<img src="https://example.com/x.png" alt="">'
     * @param string $name attribute name, e.g. 'src'.
     * @return string|null the attribute value, or null if not present.
     */
    protected function extract_attribute(string $tag, string $name): ?string {
        if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*["\']([^"\']*)["\']/i', $tag, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract just the opening tag (e.g. '<iframe src="...">') from a full
     * matched element block (e.g. '<iframe src="...">fallback</iframe>').
     *
     * @param string $element
     * @return string
     */
    protected function extract_opening_tag(string $element): string {
        $pos = strpos($element, '>');

        return $pos === false ? $element : substr($element, 0, $pos + 1);
    }

    /**
     * Fetch, parse and cache the enabled highlights for the current request.
     *
     * @return stdClass[] the parsed highlights.
     */
    protected function get_highlights(): array {
        if ($this->cache === null) {
            $this->cache = cache::make_from_params(cache_store::MODE_REQUEST, 'filter', 'externalcontent');
        }

        $highlights = $this->cache->get('highlights');
        if ($highlights !== false) {
            return $highlights;
        }

        $manager = new records_manager();

        $highlights = [];
        foreach ($manager->get_enabled() as $record) {
            $highlights[] = $this->parse_highlight($record);
        }

        $this->cache->set('highlights', $highlights);

        return $highlights;
    }

    /**
     * Parse a single highlight record into a ready-to-use structure.
     *
     * @param stdClass $record the raw highlight record (see records_manager).
     * @return stdClass the parsed highlight.
     */
    protected function parse_highlight(stdClass $record): stdClass {
        $parsed = new stdClass();
        $parsed->exactdomains = [];
        $parsed->wildcarddomains = [];

        foreach (preg_split('/[\r\n]+/', (string) ($record->domains ?? '')) as $line) {
            $line = trim(strtolower($line));
            if ($line === '') {
                continue;
            }
            if (strpos($line, '*.') === 0) {
                $parsed->wildcarddomains[] = substr($line, 2);
            } else {
                $parsed->exactdomains[$line] = true;
            }
        }

        $backgroundcolour = highlight_renderer::sanitise_colour($record->backgroundcolour ?? '', '#f0ad4e');
        $textcolour = highlight_renderer::sanitise_colour($record->textcolour ?? '', '#ffffff');

        // Pre-build the outline style used to wrap every matching link.
        $parsed->outlinestyle = highlight_renderer::build_outline_style($backgroundcolour);

        // Pre-build the label markup once; it is identical for every matching
        // link, only the anchor itself changes.
        $parsed->labelhtml = highlight_renderer::build_label_html(
            (string) ($record->label ?? ''),
            $backgroundcolour,
            $textcolour
        );

        // Pre-build the equivalents used to decorate matching media elements
        // (images, iframes, embeds, videos), which use a label overlaid on
        // top of the element instead of one placed inline before it (see
        // process_media()). The wrapper itself carries no visible styling -
        // the outline is merged directly into the matched element's own
        // style instead, so it always hugs whatever box that specific
        // element renders at.
        $parsed->mediawrapperstyle = highlight_renderer::build_media_wrapper_style();
        $parsed->mediaoutlinestyle = highlight_renderer::build_media_outline_style($backgroundcolour);
        $parsed->medialabelhtml = highlight_renderer::build_overlay_label_html(
            (string) ($record->label ?? ''),
            $backgroundcolour,
            $textcolour
        );

        return $parsed;
    }

    /**
     * Check whether the host of a URL matches any configured highlight, and
     * if so, wrap the matched anchor element (together with its label)
     * inside a single highlighted span.
     *
     * @param string $anchor the full anchor element matched (open tag, content, close tag).
     * @param string $href the href attribute value.
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return string the (possibly decorated) markup.
     */
    protected function process_link(string $anchor, string $href, array $highlights): string {
        $highlight = $this->find_matching_highlight($href, $highlights);
        if ($highlight === null) {
            return $anchor;
        }

        return \html_writer::tag('span', $highlight->labelhtml . $anchor, ['style' => $highlight->outlinestyle]);
    }

    /**
     * Check whether the url of a media element (image, iframe, embed,
     * video) matches any configured highlight, and if so, decorate the
     * matched element with an overlaid label.
     *
     * @param string $element the full element matched (e.g. the whole <img ...> tag,
     *                        or the whole <iframe ...>...</iframe> block).
     * @param string $opentag the element's opening tag only, already extracted by the caller
     *                        (which needs it anyway to read the 'src' attribute), so it isn't
     *                        re-extracted here from $element.
     * @param string $url the element's own url (src attribute).
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return string the (possibly decorated) markup.
     */
    protected function process_media(string $element, string $opentag, string $url, array $highlights): string {
        $highlight = $this->find_matching_highlight($url, $highlights);
        if ($highlight === null) {
            return $element;
        }

        $style = $this->extract_attribute($opentag, 'style') ?? '';

        if (preg_match('/position\s*:\s*(absolute|fixed)/i', $style)) {
            return $this->wrap_absolutely_positioned_media($element, $opentag, $style, $highlight);
        }

        $rest = substr($element, strlen($opentag));
        $decoratedopentag = $this->merge_style_into_tag($opentag, $highlight->mediaoutlinestyle);
        $decoratedelement = $decoratedopentag . $rest;

        return \html_writer::tag(
            'span',
            $highlight->medialabelhtml . $decoratedelement,
            ['style' => $highlight->mediawrapperstyle]
        );
    }

    /**
     * Merge an additional CSS declaration into a tag's existing style
     * attribute, or add a new style attribute if it doesn't have one yet.
     *
     * @param string $tag a single opening HTML tag, e.g. '<img src="..." style="border:1px;">'.
     * @param string $style the CSS declaration(s) to merge in, e.g. 'outline:2px solid red;'.
     * @return string the tag with the style merged in.
     */
    protected function merge_style_into_tag(string $tag, string $style): string {
        foreach (['"', "'"] as $quote) {
            $pattern = '/\sstyle\s*=\s*' . $quote . '([^' . $quote . ']*)' . $quote . '/i';
            if (preg_match($pattern, $tag, $matches)) {
                $existing = rtrim($matches[1]);
                if ($existing !== '' && !str_ends_with($existing, ';')) {
                    $existing .= ';';
                }
                return preg_replace($pattern, ' style=' . $quote . $existing . $style . $quote, $tag, 1);
            }
        }

        // No existing style attribute: insert one just before the tag's
        // closing '>' (or '/>' for self-closing/void elements).
        return preg_replace('/(\/)?>$/', ' style="' . $style . '"$1>', $tag, 1);
    }

    /**
     * Decorate a media element that is itself already absolutely/fixed
     * positioned by its own style (see process_media()), without disturbing
     * the surrounding responsive layout: the wrapper takes over the
     * element's own position/size, and the element becomes a plain static
     * filler inside it.
     *
     * @param string $element the full element matched.
     * @param string $opentag the element's opening tag only.
     * @param string $style the element's own (non-empty) style attribute value.
     * @param stdClass $highlight the matched highlight.
     * @return string
     */
    protected function wrap_absolutely_positioned_media(
        string $element,
        string $opentag,
        string $style,
        stdClass $highlight
    ): string {
        $rest = substr($element, strlen($opentag));

        $wrapperstyle = rtrim($style);
        if ($wrapperstyle !== '' && !str_ends_with($wrapperstyle, ';')) {
            $wrapperstyle .= ';';
        }
        $wrapperstyle .= $highlight->mediaoutlinestyle;

        $innerstyle = preg_replace('/position\s*:\s*(absolute|fixed)/i', 'position:static', $style);
        $innertag = $this->set_style_on_tag($opentag, $innerstyle);

        return \html_writer::tag('span', $highlight->medialabelhtml . $innertag . $rest, ['style' => $wrapperstyle]);
    }

    /**
     * Replace a tag's existing style attribute value with a new one
     * outright (as opposed to appending to it).
     *
     * @param string $tag a single opening HTML tag that is known to already have a style attribute.
     * @param string $newstyle the replacement CSS to set.
     * @return string the tag with its style attribute replaced.
     */
    protected function set_style_on_tag(string $tag, string $newstyle): string {
        foreach (['"', "'"] as $quote) {
            $pattern = '/\sstyle\s*=\s*' . $quote . '[^' . $quote . ']*' . $quote . '/i';
            if (preg_match($pattern, $tag)) {
                return preg_replace($pattern, ' style=' . $quote . $newstyle . $quote, $tag, 1);
            }
        }

        // Shouldn't normally happen (caller only calls this when a style
        // attribute is already known to exist), but fall back to adding one.
        return preg_replace('/(\/)?>$/', ' style="' . $newstyle . '"$1>', $tag, 1);
    }

    /**
     * Find the first highlight whose configured domains match the host of
     * the given url.
     *
     * @param string $url
     * @param stdClass[] $highlights the parsed highlights (see get_highlights()).
     * @return stdClass|null the matching highlight, or null if none match.
     */
    protected function find_matching_highlight(string $url, array $highlights): ?stdClass {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return null;
        }

        foreach ($highlights as $highlight) {
            if ($this->host_matches_any($host, $highlight)) {
                return $highlight;
            }
        }

        return null;
    }

    /**
     * Determine whether a host matches any domain configured in a highlight.
     * Exact domains are looked up in O(1); wildcard domains (prefixed with
     * '*.' in the settings, stored without the prefix) match the domain
     * itself and any of its subdomains.
     *
     * @param string $host
     * @param stdClass $highlight the parsed highlight (see parse_highlight()).
     * @return bool
     */
    protected function host_matches_any(string $host, stdClass $highlight): bool {
        if (isset($highlight->exactdomains[$host])) {
            return true;
        }

        foreach ($highlight->wildcarddomains as $base) {
            if ($host === $base || str_ends_with($host, '.' . $base)) {
                return true;
            }
        }

        return false;
    }
}
