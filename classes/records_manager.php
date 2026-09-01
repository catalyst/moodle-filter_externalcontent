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
 * Highlight records manager.
 *
 * Highlights are stored as a single serialised array under the plugin's own
 * config (config_plugins table).
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class records_manager {
    /** @var string name of the config value the highlights are stored under. */
    const CONFIG_NAME = 'highlights';

    /** @var stdClass[] highlight records, keyed by id. */
    private $data;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->load_data();
    }

    /**
     * Load the highlight records from config.
     */
    private function load_data(): void {
        $raw = get_config('filter_externalcontent', self::CONFIG_NAME);
        $this->data = $raw ? (array) unserialize($raw) : [];
    }

    /**
     * Persist the highlight records to config.
     */
    private function save_data(): void {
        set_config(self::CONFIG_NAME, serialize($this->data), 'filter_externalcontent');
    }

    /**
     * Return a single highlight record.
     *
     * @param string $id
     * @return stdClass|null
     */
    public function get(string $id): ?stdClass {
        if (empty($this->data[$id])) {
            return null;
        }

        return $this->data[$id];
    }

    /**
     * Return all highlight records.
     *
     * @return stdClass[] keyed by id.
     */
    public function get_all(): array {
        return $this->data;
    }

    /**
     * Return only the enabled highlight records.
     *
     * @return stdClass[] keyed by id.
     */
    public function get_enabled(): array {
        return array_filter($this->data, function (stdClass $record) {
            return !empty($record->enabled);
        });
    }

    /**
     * Create or update a highlight record.
     *
     * @param stdClass $record
     * @return string the record id.
     */
    public function save(stdClass $record): string {
        if (empty($record->id)) {
            do {
                $record->id = uniqid();
            } while (isset($this->data[$record->id]));
        }

        $this->data[$record->id] = $record;
        $this->save_data();

        return $record->id;
    }

    /**
     * Delete a highlight record.
     *
     * @param string $id
     */
    public function delete(string $id): void {
        if (isset($this->data[$id])) {
            unset($this->data[$id]);
            $this->save_data();
        }
    }

    /**
     * Toggle the enabled state of a highlight record.
     *
     * @param string $id
     */
    public function toggle(string $id): void {
        if (isset($this->data[$id])) {
            $this->data[$id]->enabled = empty($this->data[$id]->enabled) ? 1 : 0;
            $this->save_data();
        }
    }
}
