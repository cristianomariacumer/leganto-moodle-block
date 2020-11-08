<?php
// Copyright (c) Talis Education Limited, 2013
// Released under the LGPL Licence - http://www.gnu.org/licenses/lgpl.html. Anyone is free to change or redistribute this code.

$string['pluginname'] = 'Leganto';
$string['aspirelists'] = 'Resource Lists';
$string['leganto'] = 'Resource Lists';
// $string['multiple_course_msg'] = 'Multiple courses found';
$string['no_resource_lists_msg'] = 'No reading lists found';
$string['cachedef_readinglist'] = 'Cache for the leganto reading list';

$string['leganto:addinstance'] = 'Add a new Leganto block';
$string['leganto:myaddinstance'] = 'Add a new Leganto block to Dashboard';

//Configuration
$string['config_almaApiUrl'] = 'Alma API URL';
$string['config_almaApiUrlDesc'] = 'For example: https://api-eu.hosted.exlibrisgroup.com';

$string['config_apiKey'] = 'API key';
$string['config_apiKeyDesc'] = 'Your Alma API key';
// $string['config_almaCourseCodeRegex'] = 'Alma Course Code Regex';
$string['config_listPermalink'] = 'List permalink';
$string['config_blockTitle'] = 'Block title';
$string['config_blockTitleDesc'] = 'The title of the block as it appears to users in Moodle';
$string['config_blockTitleDefault'] = 'Reading Lists';
$string['config_noListAvailable'] = 'Message to display when no list available';
$string['config_noListAvailableDesc'] = 'The text of the message to display when there are no lists available';
$string['config_ltiProfile'] = 'Normalization code';
$string['config_noListAvailableDefault'] = 'No Reading List Found';
$string['config_openNewWindow'] = 'Open list in new window';
$string['config_displayItemCount'] = 'Display number of items';
$string['config_displayLastUpdated'] = 'Display last updated time';
$string['config_institutionCode'] = 'Alma institution code';
$string['config_permalinkBaseUrl'] = 'Permalink base URL';
$string['config_permalinkBaseUrlDesc'] = 'For example: https://mydomain.com';
$string['config_publishedListsOnly'] = 'Also display unpublished lists';
$string['config_sortByName'] = 'Name';
$string['config_sortByModification'] = 'Modification date';
$string['config_sortBy'] = 'Sort lists by';

$string['config_sortAscending'] = 'Ascending';
$string['config_sortDescending'] = 'Descending';
$string['config_sortByDir'] = 'Sort order';
$string['config_authLocal'] = 'Local';
$string['config_authSaml'] = 'SAML';
$string['config_authCas'] = 'CAS';
$string['config_authSetting'] = 'Authentication method';
$string['config_debugSetting'] = 'Debug mode';

$string['config_useCache'] = 'Use cache';
$string['config_useCacheDesc'] = 'When this option is turned on, the reading list will be cached until the expiry time below is reached';
$string['config_cacheExpiration'] = 'Cache expiration time (minutes)';
$string['config_cacheExpirationDesc'] = 'The time in minutes until the cache expires and the reading list is refreshed';

// singular or plurals for displaying the number of items on a list
$string['item'] = 'item';

// label for use when showing date the list was last updated
$string['lastUpdated'] = 'Last updated';

// added this to prove this was really a UTF-8 FILE!! on a mac 'file filename.txt' reports a UTF-8 file as ASCII if there are NO diacritics in the file!
$spuriousVar = 'î';
