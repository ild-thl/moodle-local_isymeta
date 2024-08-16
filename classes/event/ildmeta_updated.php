<?php
namespace local_ildmeta\event;

defined('MOODLE_INTERNAL') || die();

/**
 * ildmeta_updated
 *
 * Class for event to be triggered when metadata for a course is updated.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int noindex: Status describing wether the course should be indexed or not.
 *      - string uuid: Unique identifier for the course.
 * }
 *
 * @package    local_ildmeta
 * @since      Moodle 4.0
 * @copyright  2023 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ildmeta_updated extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u'; // 'c' for create, 'r' for read, 'u' for update, 'd' for delete
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'ildmeta';
    }

    public static function get_name() {
        return get_string('eventildmetaupdated', 'local_ildmeta');
    }

    public function get_description() {
        return "The metadata for course ID {$this->objectid} has been updated.";
    }
}