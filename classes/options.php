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
 * Class for handling options for course archiving.
 *
 * @package     tool_course_archiver_cli
 * @copyright   2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class options {
    /** @var bool Whether to delete the course after backup. */
    private bool $delete;

    /** @var string The target directory to store the backup files. */
    private string $target;

    /** @var bool Whether to suppress all interactive prompts. */
    private bool $nonInteractive;

    /** @var bool Whether to suppress confirmation for non-interactive mode. */
    private bool $quiet;

    /**
     * Constructor for the options class.
     * Initializes the options with default values.
     */
    public function __construct() {
        global $CFG;
        $this->delete = false;
        $this->nonInteractive = false;
        $this->quiet = false;
        $this->target = $CFG->dataroot . '/course_archiver/';
    }

    /**
     * Get the delete option.
     *
     * @return bool
     */
    public function getDelete(): bool {
        return $this->delete;
    }

    /**
     * Get the archive path option.
     *
     * @return string
     */
    public function getArchivePath(): string {
        return $this->target;
    }

    /**
     * Get the non-interactive option.
     *
     * @return bool
     */
    public function getNonInteractive(): bool {
        return $this->nonInteractive;
    }

    /**
     * Get the quiet option.
     *
     * @return bool
     */
    public function getQuiet(): bool {
        return $this->quiet;
    }

    /**
     * Set the delete option.
     *
     * @param bool $delete Whether to delete the course after backup.
     * @return self
     */
    public function setDelete(bool $delete): self {
        $this->delete = $delete;
        return $this;
    }

    /**
     * Set the non-interactive option.
     *
     * @param bool $nonInteractive Whether to suppress all interactive prompts.
     * @return self
     */
    public function setNonInteractive(bool $nonInteractive): self {
        $this->nonInteractive = $nonInteractive;
        return $this;
    }

    /**
     * Set the quiet option.
     *
     * @param bool $quiet Whether to suppress confirmation for non-interactive mode.
     * @return self
     */
    public function setQuiet(bool $quiet): self {
        $this->quiet = $quiet;
        return $this;
    }

    /**
     * Set the archive path option.
     *
     * @param string $target The target directory to store the backup files.
     * @return self
     */
    public function setArchivePath(string $target): self {
        $this->target = $target;
        return $this;
    }
}
