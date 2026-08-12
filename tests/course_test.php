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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'testable_course.php';

/**
 * Unit tests for course archiver delete behaviour.
 *
 * @package    tool_course_archiver_cli
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_test extends \advanced_testcase {
    protected \stdClass $course;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->course = $this->getDataGenerator()->create_course(['shortname' => 'ARCHIVE_TEST_COURSE']);
    }

    public function test_archive_with_delete_noninteractive_quiet_archivepath_command(): void {
        global $CFG;

        $options = new options();
        $options->setDelete(true)
                ->setNonInteractive(true)
                ->setQuiet(true)
                ->setArchivePath($CFG->dataroot . '/course_archiver_test/');

        $testablecourse = new testable_course($this->course->id, $options);
        $testablecourse->archive();

        $this->assertSame(
            [
                ['backup.php', "--courseid={$this->course->id} --destination={$options->getArchivePath()}", 'backupdfailed'],
                ['delete_course.php', "--courseid={$this->course->id} --disablerecyclebin --non-interactive", 'deletefailed'],
            ],
            $testablecourse->getExecCalls()
        );
    }

    public function test_archive_with_noninteractive_quiet_archivepath_command(): void {
        global $CFG;

        $options = new options();
        $options->setNonInteractive(true)->setQuiet(true);

        $testablecourse = new testable_course($this->course->id, $options);
        $testablecourse->archive();

        $this->assertSame(
            [
                ['backup.php', "--courseid={$this->course->id} --destination={$CFG->dataroot}/course_archiver/", 'backupdfailed'],
            ],
            $testablecourse->getExecCalls()
        );
    }

}
