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

/**
 * Class for archive all courses within a category.
 *
 * @package     tool_course_archiver_cli
 * @copyright   2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category {
    /** @var int The category ID. */
    protected int $id;

    /** @var bool Whether to include courses in subcategories. */
    protected bool $recursive;

    /** @var options The options for the category archiver. */
    protected options $options;

    /** @var array The list of pending categories to process when loading course ids. */
    protected array $pending = [];

    /** @var array The list of courses to archive. */
    protected array $courses = [];

    /**
     * Constructor for the category archiver.
     *
     * @param int $id The category ID.
     * @param options $options The options for the category archiver.
     * @param bool $recursive Whether to include courses in subcategories when a category is specified.
     */
    public function __construct(int $id, options $options, bool $recursive) {
        $this->id = $id;
        $this->options = $options;
        $this->recursive = $recursive;
        $this->pending[] = $id;
        $this->load_courses();
    }

    /**
     * Load courses from the category and optionally its subcategories.
     *
     * @return void
     */
    protected function load_courses(): void {
        while (!empty($this->pending)) {
            $id = \array_shift($this->pending);
            $courses = \get_courses($id, 'c.id', 'c.id, c.shortname, c.fullname');
            foreach ($courses as $course) {
                if ($course->id == SITEID) {
                    continue;
                }
                $this->courses[$course->id] = $course->fullname . ' (' . $course->shortname . ')';
            }
            if ($this->recursive) {
                $categories = \core_course_category::get($id)->get_children();
                foreach ($categories as $category) {
                    $this->pending[] = $category->id;
                }
            }
        }
    }

    /**
     * Archive the list of courses.
     *
     * @return void
     */
    public function archive(): void {
        if (!$this->options->get_non_interactive() && !$this->get_confirmation()) {
            return;
        }
        $courseoptions = clone($this->options);
        $courseoptions->set_non_interactive(true);
        foreach (\array_keys($this->courses) as $courseid) {
            $archiver = new course(id: $courseid, options: $courseoptions);
            $archiver->archive();
        }
    }

    /**
     * Get the confirmation message for archiving the category and its courses,
     * and ask for confirmation via CLI input.
     *
     * @return bool Whether the user confirmed the archiving.
     */
    public function get_confirmation(): bool {
        global $DB;
        $categoryname = $DB->get_record('course_categories', ['id' => $this->id], 'name')->name;
        $text = get_string('confirmarchivecategory', 'tool_course_archiver_cli', $categoryname);
        foreach ($this->courses as $courseid => $coursename) {
            $text .= "\n - {$coursename} (ID: {$courseid})";
        }
        echo $text . PHP_EOL;
        $yes = strtolower(substr(get_string('yes'), 0, 1));
        $no = strtolower(substr(get_string('no'), 0, 1));
        $input = cli_input(
            get_string('confirmcontinue', 'tool_course_archiver_cli') . ' (' . $yes . '/' . strtoupper($no) . ')',
            $no,
            [$yes, strtoupper($yes), $no, strtoupper($no)]
        );
        return (strtolower($input) === $yes);
    }
}
