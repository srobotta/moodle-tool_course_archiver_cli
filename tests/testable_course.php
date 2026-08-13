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
 * Class that mocks the exec call of the tool_course_archiver_cli\course class.
 *
 * @package    tool_course_archiver_cli
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_course extends course {
    /** @var array<int, array{0:string,1:string,2:string}> */
    private array $execcalls = [];

    /**
     * Overwrite the exec function that actually returns the cli call to the
     * course backup and delete script.
     *
     * @param string $script
     * @param string $args
     * @param string $err
     */
    protected function exec(string $script, string $args, string $err): void {
        $this->execcalls[] = [$script, $args, $err];
    }

    /**
     * Get the "executed calls" as an array.
     * @return array<int, array{0:string,1:string,2:string}>
     */
    public function get_exec_calls(): array {
        return $this->execcalls;
    }
}
