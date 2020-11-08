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
 * External API.
 *
 * @package    block_leganto
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_leganto;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/blocks/moodleblock.class.php");
require_once("$CFG->dirroot/blocks/leganto/block_leganto.php");

use block_leganto\external\reading_list_exporter;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_system;
use cache;

/**
 * External API class.
 *
 * @package    block_leganto
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_reading_lists_parameters() {
        return new external_function_parameters(
                array(
                        'course' => new external_value(PARAM_RAW, 'Course code', VALUE_DEFAULT, '')
                )
        );
    }

    /**
     * Get last accessed items by the logged user (activities or resources).
     *
     * @param  string $course The course code
     * @return array Reading lists and debug message
     */
    public static function get_reading_lists($course = '') {
        global $PAGE;

        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(self::get_reading_lists_parameters(),
            array(
                'course' => $course,
            )
        );

        \core\session\manager::write_close();

        $blockleganto = new \block_leganto();

        // Use cache if the setting is on.
        if (get_config('leganto', 'useCache')) {
            $readinglistcache = cache::make('block_leganto', 'readinglist');
            $cache = $readinglistcache->get($course);
            if ($cache) {
                // The cache exists, lets check if it has expired and needs to be refreshed.
                $expirytime = get_config('leganto', 'cacheExpiration');
                $cacheexpiry = $cache['datetime'] + ($expirytime * 60);
                if (time() < $cacheexpiry) {
                    $lists = $cache['readinglists'];
                } else {
                    $lists = $blockleganto->getReadingLists($course);
                    $cache = array(
                        'datetime' => time(),
                        'readinglists' => $lists
                    );
                    $readinglistcache->set($course, $cache);
                }
            } else {
                $lists = $blockleganto->getReadingLists($course);
                $cache = array(
                    'datetime' => time(),
                    'readinglists' => $lists
                );
                $readinglistcache->set($course, $cache);
            }
        } else {
            $lists = $blockleganto->getReadingLists($course);
        }

        $debug = isset($blockleganto->debugmsg) ? $blockleganto->debugmsg : '';

        if (empty($lists)) {
            return array('debug' => $debug, 'lists' => array());
        }

        $renderer = $PAGE->get_renderer('core');
        $readinglists = array_map(function($list) use ($renderer) {
            $exporter = new reading_list_exporter($list);
            return $exporter->export($renderer);
        }, $lists);
 
        return array('debug' => $debug, 'lists' => $readinglists);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_reading_lists_returns() {
        return new external_single_structure(array(
            'debug' => new external_value(PARAM_RAW, 'The debug message script to be executed'),
            'lists' => new external_multiple_structure(reading_list_exporter::get_read_structure(),
                'The reading lists for a course')
        ));
    }
}
