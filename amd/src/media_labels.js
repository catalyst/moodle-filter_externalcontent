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
 * Adds labels to replaced media elements that cannot reliably render ::before.
 *
 * @module     filter_externalcontent/media_labels
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const MEDIA_SELECTOR = 'img,iframe,video,audio,object,embed';

/**
 * Extract lowercased candidate URL attribute values from an element.
 *
 * @param {HTMLElement} element
 * @returns {String[]}
 */
const getCandidates = (element) => ['src', 'data', 'href']
    .map((attribute) => element.getAttribute(attribute))
    .filter((value) => !!value)
    .map((value) => value.toLowerCase());

/**
 * Find the first matching runtime rule for a set of candidates.
 *
 * @param {Array} rules
 * @param {String[]} candidates
 * @returns {Object|null}
 */
const findMatch = (rules, candidates) => {
    let matched = null;
    rules.some((rule) => {
        const values = Array.isArray(rule.values) ? rule.values : [];
        return values.some((value) => {
            const needle = String(value || '').toLowerCase();
            if (needle === '') {
                return false;
            }
            return candidates.some((candidate) => {
                if (candidate.includes(needle)) {
                    matched = rule;
                    return true;
                }
                return false;
            });
        });
    });

    return matched;
};

/**
 * Wrap an element with a labelled container.
 *
 * @param {HTMLElement} element
 * @param {Object} match
 */
const applyLabel = (element, match) => {
    if (!element.parentNode) {
        return;
    }

    const wrapper = document.createElement('span');
    wrapper.className = 'filter-externalcontent-labelled-resource';
    wrapper.style.setProperty('--filter-externalcontent-colour', String(match.backgroundcolour || ''));
    wrapper.style.setProperty('--filter-externalcontent-textcolour', String(match.textcolour || ''));

    const label = document.createElement('span');
    label.className = 'filter-externalcontent-resource-label';
    label.textContent = String(match.label || '');

    element.parentNode.insertBefore(wrapper, element);
    wrapper.appendChild(label);
    wrapper.appendChild(element);
    element.classList.add('filter-externalcontent-no-outline');
    element.dataset.filterExternalcontentLabelApplied = '1';
};

/**
 * Initialise media label injection for configured rules.
 *
 * @param {Array} rules
 */
export const init = (rules) => {
    if (!Array.isArray(rules) || rules.length === 0) {
        return;
    }

    document.querySelectorAll(MEDIA_SELECTOR).forEach((element) => {
        if (element.dataset.filterExternalcontentLabelApplied === '1') {
            return;
        }

        const candidates = getCandidates(element);
        if (candidates.length === 0) {
            return;
        }

        const match = findMatch(rules, candidates);
        if (!match || !match.label) {
            return;
        }

        applyLabel(element, match);
    });
};
