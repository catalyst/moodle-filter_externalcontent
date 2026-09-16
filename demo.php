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
 * Standalone demo page showing SVG lane highlighting with live preview.
 *
 * Designed as a clean reference implementation based on the highlight edit form.
 *
 * @package    filter_externalcontent
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$manageurl = new moodle_url('/admin/settings.php', ['section' => 'filtersettingexternalcontent']);
$demourl = new moodle_url('/filter/externalcontent/demo.php');

$PAGE->set_context($context);
$PAGE->set_url($demourl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'filter_externalcontent') . ': SVG Highlight Demo');
$PAGE->set_heading(get_string('pluginname', 'filter_externalcontent'));
navigation_node::require_admin_tree();
$PAGE->navbar->add(get_string('manage_heading', 'filter_externalcontent'), $manageurl);
$PAGE->navbar->add('SVG Highlight Demo', $demourl);

echo $OUTPUT->header();
?>

<div class="container-fluid" style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>SVG Highlighting Demo</h2>
        <a href="<?php echo $manageurl->out(); ?>" class="btn btn-secondary">&larr; Back to Settings</a>
    </div>

    <!-- Highlight Settings Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <strong>Highlight Settings</strong>
        </div>
        <div class="card-body">
            <div class="form-group row mb-3">
                <label for="demo_label" class="col-sm-3 col-form-label">Label text</label>
                <div class="col-sm-6">
                    <input type="text" id="demo_label" class="form-control" value="External">
                </div>
            </div>

            <div class="form-group row mb-3">
                <label for="demo_bg" class="col-sm-3 col-form-label">Background colour</label>
                <div class="col-sm-6 d-flex align-items-center">
                    <input type="text" id="demo_bg" class="form-control" value="#f0ad4e" style="max-width: 140px;">
                    <input type="color" id="demo_bg_picker" value="#f0ad4e" class="form-control form-control-color ms-2 ml-2" style="width: 48px; padding: 2px;">
                </div>
            </div>

            <div class="form-group row mb-3">
                <label for="demo_fg" class="col-sm-3 col-form-label">Text colour</label>
                <div class="col-sm-6 d-flex align-items-center">
                    <input type="text" id="demo_fg" class="form-control" value="#ffffff" style="max-width: 140px;">
                    <input type="color" id="demo_fg_picker" value="#ffffff" class="form-control form-control-color ms-2 ml-2" style="width: 48px; padding: 2px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <strong>Live Preview</strong>
        </div>
        <div class="card-body">
            <!-- 1. Text Link -->
            <div class="mb-4">
                <label>Anchor link</label>
                <div>
                    <a href="https://example.com/getting-started" onclick="return false;">
                        https://example.com/getting-started
                    </a>
                </div>
            </div>

            <!-- 2. Image -->
            <div class="mb-4">
                <label>Image</label>
                <div>
                    <img src="https://commons.wikimedia.org/w/load.php?modules=skins.vector.icons&image=language&format=original&lang=en&skin=vector-2022&version=iy978"
                         alt="Example Image"
                         style="max-width: 320px; display: block;">
                </div>
            </div>

            <!-- 3. Video -->
            <div class="mb-4">
                <label>Video</label>
                <div>
                    <video controls preload="none"
                           src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4"
                           style="max-width: 320px; display: block;">
                    </video>
                </div>
            </div>

            <!-- 4. Iframe -->
            <div class="mb-3">
                <label>iFrame</label>
                <div>
                    <iframe src="https://www.youtube.com/embed/M7lc1UVf-VE"
                            title="Example Frame"
                            style="width: 320px; height: 180px; display: block;">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<style id="demo_generated_style"></style>

<script>
(() => {
    const FONT_FAMILY = 'Roboto, Helvetica Neue, Arial, sans-serif';
    const FONT_SIZE_PX = 16;
    const BADGE_HEIGHT_PX = 20;
    const MIN_LANE_WIDTH_PX = 60;

    const labelInput = document.getElementById('demo_label');
    const bgInput = document.getElementById('demo_bg');
    const bgPicker = document.getElementById('demo_bg_picker');
    const fgInput = document.getElementById('demo_fg');
    const fgPicker = document.getElementById('demo_fg_picker');
    const styleElement = document.getElementById('demo_generated_style');

    // Sync color pickers with text inputs.
    bgPicker.addEventListener('input', () => { bgInput.value = bgPicker.value; update(); });
    bgInput.addEventListener('input', () => { bgPicker.value = bgInput.value; update(); });
    fgPicker.addEventListener('input', () => { fgInput.value = fgPicker.value; update(); });
    fgInput.addEventListener('input', () => { fgPicker.value = fgInput.value; update(); });
    labelInput.addEventListener('input', update);

    // Real URL attribute selectors matching the sample elements directly.
    const SELECTORS = [
        ':not(link):not(base):not(meta)[href*="example.com" i]',
        ':not(script):not(link):not(base):not(meta)[src*="wikimedia.org" i]',
        ':not(script):not(link):not(base):not(meta)[src*="mozilla.net" i]',
        ':not(script):not(link):not(base):not(meta)[src*="youtube.com" i]'
    ].join(',\n');

    function measureTextWidth(text) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return MIN_LANE_WIDTH_PX;
        }
        ctx.font = `600 ${FONT_SIZE_PX}px ${FONT_FAMILY}`;
        const measured = Math.ceil(ctx.measureText(text).width * 1.1);
        return Math.max(MIN_LANE_WIDTH_PX, measured);
    }

    function buildSvgDataUri(label, bg, fg, width) {
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${BADGE_HEIGHT_PX}">` +
            `<rect x="0" y="0" width="${width}" height="${BADGE_HEIGHT_PX}" fill="${bg}"/>` +
            `<text x="${(width - 2) / 2}" y="15" text-anchor="middle" font-family="${FONT_FAMILY}" font-size="${FONT_SIZE_PX}px" fill="${fg}" style="text-rendering:auto;">${label}</text>` +
            `</svg>`;
        return `data:image/svg+xml,${encodeURIComponent(svg)}`;
    }

    function update() {
        const label = (labelInput.value || '').trim() || 'External';
        const bg = bgInput.value.trim() || '#f0ad4e';
        const fg = fgInput.value.trim() || '#ffffff';

        const laneWidth = measureTextWidth(label);
        const svgUri = buildSvgDataUri(label, bg, fg, laneWidth);

        const css = [
            SELECTORS + ' {',
            `    border: 0px solid ${bg};`,
            `    outline: 2px solid ${bg};`,
            `    border-left: ${laneWidth}px solid transparent;`,
            '    background-image:',
            `        url("${svgUri}"),`,
            `        linear-gradient(${bg}, ${bg});`,
            '    background-repeat: no-repeat, no-repeat;',
            '    background-position: left center, left top;',
            `    background-size: ${laneWidth}px ${BADGE_HEIGHT_PX}px, ${laneWidth}px 100%;`,
            '    background-origin: border-box, border-box;',
            '}',
        ].join('\n');

        styleElement.textContent = css;
    }

    update();
})();
</script>

<?php
echo $OUTPUT->footer();
