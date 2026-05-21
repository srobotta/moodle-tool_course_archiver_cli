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

namespace tool_course_archiver;

/**
 * Class that mocks the course archiving in the tool_course_archiver\category class.
 */
class testable_category extends category {
    /** @var array<int, int> Tracks which course IDs were archived. */
    private array $archivedcourses = [];

    /**
     * Overwrite the archive method to track which courses would be archived
     * without actually executing the course backup and delete scripts.
     *
     * @return void
     */
    public function archive(): void {
        if (!$this->options->getNonInteractive() && !$this->getConfirmation()) {
            return;
        }
        foreach (\array_keys($this->courses) as $courseid) {
            $this->archivedcourses[] = $courseid;
        }
    }

    /**
     * Get the list of archived course IDs.
     *
     * @return array<int, int>
     */
    public function getArchivedCourses(): array {
        return $this->archivedcourses;
    }

    /**
     * Get the courses that were loaded for this category.
     *
     * @return array
     */
    public function getCourses(): array {
        return $this->courses;
    }
}
