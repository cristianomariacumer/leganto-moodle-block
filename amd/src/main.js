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
 * This module adds ajax display functions to the leganto block.
 *
 * @package    block_leganto
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(
[
    'jquery',
    'core/ajax',
    'core/notification',
    'core/templates'
],
function(
    $,
    ajax,
    notification,
    templates
) {
    /**
     * Get reading list from backend.
     *
     * @method getReadingLists
     * @param {int} course The course code
     * @return {array} Reading lists to be displayed
     */
    var getReadingLists = function(course) {
        var args = {};
        if (typeof course !== 'undefined') {
            args.course = course;
        }
        var request = {
            methodname: 'block_leganto_get_reading_lists',
            args: args
        };
        return ajax.call([request])[0];
    };

    /**
     * Render the block content.
     *
     * @method renderReadingLists
     * @param {array} readingLists containing array of returned reading list
     * @param {string} noListAvailable The string to display when no lists are available
     * @return {promise} Resolved with HTML and JS strings
     */
    var renderReadingLists = function(readingLists, noListAvailable) {
        if (readingLists.length > 0) {
            return templates.render('block_leganto/reading-lists', {
                reading_lists: readingLists
            });
        } else {
            return templates.render('block_leganto/no-list', {
                string: noListAvailable
            });
        }
    };

    /**
     * Get and show the reading list into the block.
     *
     * @param {object} root The root element for the block
     * @param {string} course The course code
     * @param {string} noListAvailable The string to display when no lists are available
     */
    var init = function(root, course, noListAvailable) {
        root = $(root);

        var readingListsContent = root.find('[data-region="reading-list-container"]');
        var readingListsDebug = root.find('[data-region="reading-list-debug"]');

        var readingListsPromise = getReadingLists(course);

        readingListsPromise.then(function(readingLists) {
            templates.replaceNodeContents(readingListsDebug, readingLists.debug, '');
            var contentPromise = renderReadingLists(readingLists.lists, noListAvailable);

            contentPromise.then(function(html, js) {
                return templates.replaceNodeContents(readingListsContent, html, js);
            }).catch(notification.exception);
            return readingListsPromise;
        }).catch(notification.exception);
    };

    return {
        init: init
    };
});
