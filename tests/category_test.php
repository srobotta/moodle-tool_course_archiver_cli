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

namespace tool_course_archiver_cli;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'testable_category.php';
use core_course_category;

/**
 * Unit tests for category archiver behaviour.
 *
 * @package    tool_course_archiver_cli
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class category_test extends \advanced_testcase {
    protected core_course_category $category;
    protected \stdClass $course1;
    protected \stdClass $course2;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);

        // Create a category and courses in it.
        $this->category = $this->getDataGenerator()->create_category(['name' => 'Test Category']);
        $this->course1 = $this->getDataGenerator()->create_course([
            'category' => $this->category->id,
            'shortname' => 'TEST_COURSE_1'
        ]);
        $this->course2 = $this->getDataGenerator()->create_course([
            'category' => $this->category->id,
            'shortname' => 'TEST_COURSE_2'
        ]);
        $this->setAdminUser();
    }

    public function test_archive_category_noninteractive_loads_all_courses(): void {
        $options = new options();
        $options->setNonInteractive(true)->setQuiet(true);

        $testablecategory = new testable_category($this->category->id, $options, false);
        $testablecategory->archive();

        $archivedcourses = $testablecategory->getArchivedCourses();
        $this->assertCount(2, $archivedcourses);
        $this->assertContains((int)$this->course1->id, $archivedcourses);
        $this->assertContains((int)$this->course2->id, $archivedcourses);
    }

    public function test_archive_category_recursive_loads_subcategory_courses(): void {
        // Create a subcategory with a course.
        $subcategory = $this->getDataGenerator()->create_category([
            'parent' => $this->category->id,
            'name' => 'Subcategory'
        ]);
        $course3 = $this->getDataGenerator()->create_course([
            'category' => $subcategory->id,
            'shortname' => 'TEST_COURSE_3'
        ]);

        $options = new options();
        $options->setNonInteractive(true)->setQuiet(true);

        $testablecategory = new testable_category($this->category->id, $options, true);
        $testablecategory->archive();

        $archivedcourses = $testablecategory->getArchivedCourses();
        $this->assertCount(3, $archivedcourses);
        $this->assertContains((int)$this->course1->id, $archivedcourses);
        $this->assertContains((int)$this->course2->id, $archivedcourses);
        $this->assertContains((int)$course3->id, $archivedcourses);
    }

    public function test_archive_category_non_recursive_ignores_subcategories(): void {
        // Create a subcategory with a course.
        $subcategory = $this->getDataGenerator()->create_category([
            'parent' => $this->category->id,
            'name' => 'Subcategory'
        ]);
        $this->getDataGenerator()->create_course([
            'category' => $subcategory->id,
            'shortname' => 'TEST_COURSE_3'
        ]);

        $options = new options();
        $options->setNonInteractive(true)->setQuiet(true);

        $testablecategory = new testable_category($this->category->id, $options, false);
        $testablecategory->archive();

        $archivedcourses = $testablecategory->getArchivedCourses();
        $this->assertCount(2, $archivedcourses);
        $this->assertContains((int)$this->course1->id, $archivedcourses);
        $this->assertContains((int)$this->course2->id, $archivedcourses);
    }
}
