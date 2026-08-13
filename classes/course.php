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
 * Class for archive a course.
 *
 * @package     tool_course_archiver_cli
 * @copyright   2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    /** @var int The course ID. */
    protected int $id;
    /** @var options The options for the course archiver. */
    protected options $options;
    /** @var string|null The shortname of the course, used for confirmation message. */
    protected ?string $shortname = null;

    /**
     * Constructor for the course archiver.
     *
     * @param int $id The course ID.
     * @param options $options The options for the course archiver.
     */
    public function __construct(int $id, options $options) {
        $this->id = $id;
        $this->options = $options;
    }

    /**
     * Archive the course by backing it up and optionally deleting it.
     * If not confirmed yet, it will print out a confirmation message with the course name and ID.
     *
     * @return void
     */
    public function archive(): void {
        if (!$this->options->get_non_interactive() && !$this->get_confirmation()) {
            return;
        }
        if (!$this->options->get_quiet()) {
            echo get_string('backupcourse', 'tool_course_archiver_cli', [
                'name' => $this->get_course_shortname(),
                'id' => $this->id,
                'path' => $this->options->get_archive_path(),
            ]) . PHP_EOL;
        }
        $this->exec(
            'backup.php',
            "--courseid={$this->id} --destination={$this->options->get_archive_path()}",
            'backupdfailed'
        );
        if ($this->options->get_delete()) {
            if (!$this->options->get_quiet()) {
                echo get_string('deletecourse', 'tool_course_archiver_cli', [
                    'name' => $this->get_course_shortname(),
                    'id' => $this->id,
                ]) . PHP_EOL;
            }
            $this->exec(
                'delete_course.php',
                "--courseid={$this->id} --disablerecyclebin --non-interactive",
                'deletefailed'
            );
        }
    }

    /**
     * Execute backup or delete command.
     * @param string $script
     * @param string $args
     * @param string $err
     */
    protected function exec(string $script, string $args, string $err): void {
        global $CFG;

        $php = PHP_BINARY;
        $script = realpath("$CFG->dirroot/../admin/cli/$script");
        if (DIRECTORY_SEPARATOR !== '/') {
            $script = str_replace('/', DIRECTORY_SEPARATOR, $script);
        }
        $cmd = escapeshellcmd("$php $script $args");
        if ($this->options->get_quiet()) {
            $cmd .= ' > /dev/null 2>&1';
        }

        exec($cmd, $output, $returnvar);
        if ($returnvar !== 0) {
            throw new \moodle_exception($err, 'tool_course_archiver_cli', '', implode("\n", $output));
        }
    }

    /**
     * Get the shortname of the course for confirmation message.
     *
     * @return string The shortname of the course.
     * @throws \moodle_exception If the course is not found.
     */
    protected function get_course_shortname(): string {
        global $DB;
        if ($this->shortname === null) {
            $this->shortname = $DB->get_field('course', 'shortname', ['id' => $this->id]);
            if (!$this->shortname) {
                throw new \moodle_exception('coursenotfound', 'tool_course_archiver_cli', '', $this->id);
            }
        }
        return $this->shortname;
    }

    /**
     * Get the confirmation message for archiving the course, and ask for confirmation via CLI input.
     *
     * @return bool Whether the user confirmed the archiving.
     */
    public function get_confirmation(): bool {
        echo get_string(
            'confirmarchivecourse',
            'tool_course_archiver_cli',
            [
                'course' => $this->get_course_shortname(),
                'id' => $this->id,
            ]
        );
        echo PHP_EOL;
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
