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

namespace customfield_dynamic;

use backup;
use backup_controller;
use restore_controller;

/**
 * Tests for the dynamic custom field data: stored value, display, backup, restore and duplication.
 *
 * @package   customfield_dynamic
 * @copyright 2026 LAIS/UFRN
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \customfield_dynamic\data_controller
 */
final class data_controller_test extends \advanced_testcase {
    /** @var \core_customfield\field_controller Multiple selection field. */
    private $multi;

    /** @var \core_customfield\field_controller Single selection field. */
    private $single;

    /** @var \stdClass[] Course categories used as options. */
    private array $options = [];

    /**
     * Creates the categories used as options and two dynamic fields.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        foreach (['Alpha', 'Beta', 'Gamma'] as $name) {
            $this->options[] = $generator->create_category(['name' => $name]);
        }
        $sql = 'SELECT id, name AS data FROM {course_categories} ORDER BY id';
        $cfgenerator = $generator->get_plugin_generator('core_customfield');
        $category = $cfgenerator->create_category();
        $this->multi = $cfgenerator->create_field([
            'categoryid' => $category->get('id'), 'type' => 'dynamic', 'shortname' => 'multi',
            'configdata' => ['dynamicsql' => $sql, 'multiselect' => 1, 'autocomplete' => 0, 'defaultvalue' => ''],
        ]);
        $this->single = $cfgenerator->create_field([
            'categoryid' => $category->get('id'), 'type' => 'dynamic', 'shortname' => 'single',
            'configdata' => ['dynamicsql' => $sql, 'multiselect' => 0, 'autocomplete' => 0, 'defaultvalue' => ''],
        ]);
    }

    /**
     * Creates a course with values in both fields.
     *
     * @return \stdClass
     */
    private function create_course_with_values(): \stdClass {
        $course = $this->getDataGenerator()->create_course();
        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $cfgenerator->add_instance_data($this->multi, $course->id, [$this->options[2]->id, $this->options[0]->id]);
        $cfgenerator->add_instance_data($this->single, $course->id, $this->options[1]->id);
        return $course;
    }

    /**
     * Data controller of a field in a course.
     *
     * @param int $courseid
     * @param string $shortname
     * @return data_controller
     */
    private function get_data(int $courseid, string $shortname): data_controller {
        foreach (\core_course\customfield\course_handler::create()->get_instance_data($courseid, true) as $data) {
            if ($data->get_field()->get('shortname') === $shortname) {
                return $data;
            }
        }
        throw new \coding_exception('Field not found: ' . $shortname);
    }

    /**
     * Stored value of each dynamic field of a course, by shortname.
     *
     * @param int $courseid
     * @return array
     */
    private function stored_values(int $courseid): array {
        return [
            'multi' => $this->get_data($courseid, 'multi')->get_value(),
            'single' => $this->get_data($courseid, 'single')->get_value(),
        ];
    }

    /**
     * Expected stored values for create_course_with_values().
     *
     * @return array
     */
    private function expected_values(): array {
        return [
            'multi' => $this->options[2]->id . ',' . $this->options[0]->id,
            'single' => (string) $this->options[1]->id,
        ];
    }

    /**
     * get_value() returns the stored string; the list and the display come from it.
     */
    public function test_value_and_display(): void {
        $course = $this->create_course_with_values();
        $this->assertSame($this->expected_values(), $this->stored_values($course->id));

        $data = $this->get_data($course->id, 'multi');
        $this->assertSame([(string) $this->options[2]->id, (string) $this->options[0]->id], $data->get_values());
        $this->assertSame('Gamma, Alpha', $data->export_value());

        $empty = $this->get_data($this->getDataGenerator()->create_course()->id, 'multi');
        $this->assertSame('', $empty->get_value());
        $this->assertSame([], $empty->get_values());
        $this->assertNull($empty->export_value());
    }

    /**
     * split_values() ignores spaces and empty items.
     */
    public function test_split_values(): void {
        $this->assertSame(['4', '15', '16'], data_controller::split_values('4, 15,,16 '));
        $this->assertSame([], data_controller::split_values(''));
        $this->assertSame([], data_controller::split_values(null));
    }

    /**
     * The form receives a list for multiple selection and a string otherwise.
     */
    public function test_form_before_set_data(): void {
        $course = $this->create_course_with_values();
        $instance = (object) ['id' => $course->id];
        \core_course\customfield\course_handler::create()->instance_form_before_set_data($instance);
        $this->assertSame([(string) $this->options[2]->id, (string) $this->options[0]->id], $instance->customfield_multi);
        $this->assertSame((string) $this->options[1]->id, $instance->customfield_single);
    }

    /**
     * Course backup keeps the stored string and restore writes it back unchanged.
     */
    public function test_backup_and_restore(): void {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $course = $this->create_course_with_values();

        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->execute_plan();
        $results = $bc->get_results();
        $bc->destroy();

        $fp = get_file_packer('application/vnd.moodle.backup');
        $backupid = 'customfield_dynamic_test';
        $results['backup_destination']->extract_to_pathname($fp, make_backup_temp_directory($backupid));

        $newcourseid = \restore_dbops::create_new_course('Restored', 'restored', $course->category);
        $rc = new restore_controller(
            $backupid,
            $newcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_NEW_COURSE
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        $this->assertSame($this->expected_values(), $this->stored_values($newcourseid));
    }

    /**
     * Course duplication (backup and restore through the web service) keeps the values.
     */
    public function test_duplicate_course(): void {
        global $CFG;
        require_once($CFG->dirroot . '/course/externallib.php');

        $course = $this->create_course_with_values();
        $copy = \core_course_external::duplicate_course($course->id, 'Copy', 'copy', $course->category);

        $this->assertSame($this->expected_values(), $this->stored_values($copy['id']));
    }
}
