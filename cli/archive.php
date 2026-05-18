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

/**
 * Export custom language strings to zip files.
 *
 * @package    tool_course_archiver
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_brickfield\local\areas\mod_choice\option;

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/clilib.php");

$usage = <<<EOF
"Backup courses to a target folder and optionally delete the course after backup.

Options:
-c, --category          Category ID to archive all courses inside that category.
-d, --delete            Whether to delete the course after backup, default: false.
-h, --help              Print out this help message.
--non-interactive       Suppress all interactive prompts, implies --quiet.
-q, --quiet             Suppress confirmation for non interactive mode.
-r, --recursive         Whether to include courses in subcategories when a category is specified, default: false.
-t, --target            Target directory to store the backup files, default: $CFG->dataroot/course_archiver
-x, --course            Archive a specific course by its ID.

Examples:
Archive a single course:
\$ sudo -u www-data /usr/bin/php public/admin/tool/course_archiver/cli/archive.php -x=34

Export all course within a single category:
\$ sudo -u www-data /usr/bin/php public/admin/tool/course_archiver/cli/archive.php -c=12

Export all course within a category and its subcategories, and delete the courses after backup:
\$ sudo -u www-data /usr/bin/php public/admin/tool/course_archiver/cli/archive.php -c=12 -r -d

EOF;

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    [
        'target' => false,
        'help' => false,
        'delete' => false,
        'recursive' => false,
        'category' => 0,
        'course' => 0,
        'quiet' => false,
        'non-interactive' => false,
    ],
    [
        'h' => 'help',
        'c' => 'category',
        'd' => 'delete',
        'q' => 'quiet',
        'r' => 'recursive',
        't' => 'target',
        'x' => 'course'
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_error($usage, 0);
}

$optionsObj = new \tool_course_archiver\options();
// Target dir set by option or default.
if ($options['target']) {
    $optionsObj->setArchivePath(rtrim($options['target'], '/') . '/');
}
// Ensure target directory exists.
if (!check_dir_exists($optionsObj->getArchivePath())) {
    cli_error(get_string('targetnotfound', 'tool_course_archiver', ['target' => $optionsObj->getArchivePath()]));
}

if (empty($options['category']) && empty($options['course'])) {
    cli_error(get_string('missingcategoryorcourse', 'tool_course_archiver'));
}
if (!empty($options['category']) && !empty($options['course'])) {
    cli_error(get_string('onlycategoryorcourse', 'tool_course_archiver'));
}
// Other options about delete, interactive and output.
if ($options['delete']) {
    $optionsObj->setDelete(true);
}
if ($options['non-interactive']) {
    $optionsObj->setNonInteractive(true);
}
if ($options['quiet']) {
    $optionsObj->setQuiet(true);
}

if ($options['category']) {
    $archiver = new \tool_course_archiver\category(
        id: (int)$options['category'],
        options: $optionsObj,
        recursive: $options['recursive']
    );
} else {
    $archiver = new \tool_course_archiver\course(
        id: (int)$options['course'],
        options: $optionsObj
    );
}

$archiver->archive();
