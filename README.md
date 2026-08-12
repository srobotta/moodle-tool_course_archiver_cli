# Plugin Course Archiver CLI

This plugin provides a simple cli script that allows to trigger course archives
manually on the command line of the Moodle server.
There is also a non interactive mode for automatisation.

The plugin only works with category and course ids. If you need a more granular
distingtion what to archive, the plugin
[Course Life Cycle (moodle-tool_lifecycle)](https://github.com/learnweb/moodle-tool_lifecycle)
is recommended for use.

## Usage

On your moodle server in your moodle root directory run:

```
php public/admin/tool/course_archiver/cli/archive.php -x=<COURSEID> -d
```

This creates a backup of the course and then deletes it from your Moodle.
Course backups are written into `moodle-data/course_archiver/`.

You may also archive courses within a category and their sub categories:

```
php public/admin/tool/course_archiver/cli/archive.php -c=<CATEGORYID> -d -r
```

Before the action is executed, a confirmation dialogue is shown that lists
all affected courses. The confirmation dialogue can be skipped with the
command line argument `--non-interactive`. This is useful for automatec
archives via cron.

## Options

The following options can be set:

* `-x` or `--course` with the course ID.
* `-c` or `--category` with the category ID.
* `-d` or `--delete` when set, delete the course after archiving it,
  default is no delete.
* `-r` or `--recursive` when set, traverse the sub categories of a category,
  default is not to traverse into subcategories. This has no effect when a
  course ID is used.
* `-q` or `--quiet` no output at the console.
* `-t` or `--target`  to define another directory where the course backup files
  are written to. Default is `moodle-data/course_archiver/`.
* `--non-interactive` do not ask for confirmation at the console.
