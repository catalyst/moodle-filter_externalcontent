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
 * Live preview for the highlight edit form: keeps the sample link in the
 * templates/highlight_preview.mustache markup in sync with the label and
 * colour fields of the surrounding form.
 *
 * @module     filter_externalcontent/highlight_preview
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const DEFAULT_BACKGROUND = '#f0ad4e';
const DEFAULT_TEXT = '#ffffff';
const COLOUR_PATTERN = /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/;

/**
 * Read a colour value from a field, falling back to a default if the field
 * is missing or its value is not a valid CSS hex colour.
 *
 * @param {HTMLInputElement|null} field
 * @param {String} fallback
 * @return {String}
 */
const readColour = (field, fallback) => {
    const value = field ? field.value.trim() : '';
    return COLOUR_PATTERN.test(value) ? value : fallback;
};

/**
 * Initialise the live preview, binding it to the relevant form fields.
 *
 * @param {Object} args
 * @param {String} args.wrapId id of the preview's outer wrapping element
 * @param {String} args.labelId id of the preview's label element
 * @param {String} args.labelFieldId id of the form's label text field
 * @param {String} args.backgroundFieldId id of the form's background colour field
 * @param {String} args.textFieldId id of the form's text colour field
 */
export const init = (args) => {
    const wrap = document.getElementById(args.wrapId);
    const label = document.getElementById(args.labelId);
    if (!wrap || !label) {
        return;
    }

    const labelField = document.getElementById(args.labelFieldId);
    const backgroundField = document.getElementById(args.backgroundFieldId);
    const textField = document.getElementById(args.textFieldId);

    const update = () => {
        const background = readColour(backgroundField, DEFAULT_BACKGROUND);
        const textColour = readColour(textField, DEFAULT_TEXT);
        const labelText = labelField ? labelField.value.trim() : '';

        wrap.setAttribute('style', `outline:2px solid ${background};padding-right:4px;`);

        if (labelText !== '') {
            label.textContent = labelText;
            label.setAttribute('style',
                `background-color:${background};color:${textColour};padding: 1px 4px 1px 2px;`);
        } else {
            label.setAttribute('style', 'display:none;');
        }
    };

    [labelField, backgroundField, textField].forEach((field) => {
        if (field) {
            field.addEventListener('input', update);
            field.addEventListener('change', update);
        }
    });

    // Add event listener for colour picker widget.
    document.addEventListener('click', (e) => {
        if (e.target.closest('.admin_colourpicker img.colourdialogue')) {
            window.setTimeout(update, 0);
        }
    });

    update();
};
