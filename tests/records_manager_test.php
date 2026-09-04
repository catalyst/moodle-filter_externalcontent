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

use advanced_testcase;

/**
 * Unit tests for the highlight records manager.
 *
 * @package    filter_externalcontent
 * @author     Guillaume Barat (guillaumebarat@catalyst-au.net)
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_externalcontent\records_manager
 */
final class records_manager_test extends advanced_testcase {
    /**
     * Build a highlight stdClass with sensible defaults.
     *
     * @param array $overrides
     * @return \stdClass
     */
    protected function make_record(array $overrides = []): \stdClass {
        return (object) array_merge([
            'enabled' => 1,
            'name' => 'Test highlight',
            'domains' => 'example.com',
            'label' => 'External',
            'textcolour' => '#ffffff',
            'backgroundcolour' => '#f0ad4e',
        ], $overrides);
    }

    /**
     * Test create record.
     */
    public function test_save_creates_a_new_record_with_generated_id(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $id = $manager->save($this->make_record());

        $this->assertNotEmpty($id);

        $stored = $manager->get($id);
        $this->assertSame('Test highlight', $stored->name);
    }

    /**
     * Test update record.
     */
    public function test_save_updates_an_existing_record(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $id = $manager->save($this->make_record());

        $record = $manager->get($id);
        $record->name = 'Updated name';
        $manager->save($record);

        $manager = new records_manager();
        $this->assertSame('Updated name', $manager->get($id)->name);
        $this->assertCount(1, $manager->get_all());
    }

    /**
     * Test record id not exist.
     */
    public function test_get_returns_null_for_unknown_id(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $this->assertNull($manager->get('doesnotexist'));
    }

    /**
     * Test return all records.
     */
    public function test_get_all_returns_every_record(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $manager->save($this->make_record(['name' => 'First']));
        $manager->save($this->make_record(['name' => 'Second']));

        $this->assertCount(2, $manager->get_all());
    }

    /**
     * Test return only enabled records.
     */
    public function test_get_enabled_only_returns_enabled_records(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $manager->save($this->make_record(['name' => 'Enabled', 'enabled' => 1]));
        $manager->save($this->make_record(['name' => 'Disabled', 'enabled' => 0]));

        $enabled = $manager->get_enabled();
        $this->assertCount(1, $enabled);
        $this->assertSame('Enabled', reset($enabled)->name);
    }

    /**
     * Test toggle enable state.
     */
    public function test_toggle_flips_the_enabled_state(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $id = $manager->save($this->make_record(['enabled' => 1]));

        $manager->toggle($id);
        $this->assertSame(0, (int) $manager->get($id)->enabled);

        $manager->toggle($id);
        $this->assertSame(1, (int) $manager->get($id)->enabled);
    }

    /**
     * Test delete record.
     */
    public function test_delete_removes_the_record(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $id = $manager->save($this->make_record());

        $manager->delete($id);

        $manager = new records_manager();
        $this->assertNull($manager->get($id));
        $this->assertCount(0, $manager->get_all());
    }

    /**
     * Test data persist.
     */
    public function test_data_persists_across_manager_instances(): void {
        $this->resetAfterTest();

        $manager = new records_manager();
        $id = $manager->save($this->make_record());

        $reloaded = new records_manager();
        $this->assertNotNull($reloaded->get($id));
    }
}
