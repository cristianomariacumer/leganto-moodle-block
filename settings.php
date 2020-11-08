<?php
// Released under the LGPL Licence - http://www.gnu.org/licenses/lgpl.html. Anyone is free to change or redistribute this code.
$this->mandatory = true;
$settings->add(new admin_setting_configtext('leganto/blockTitle',get_string('config_blockTitle', 'block_leganto'),get_string('config_blockTitleDesc', 'block_leganto'),get_string('config_blockTitleDefault', 'block_leganto')));

$settings->add(new admin_setting_configtext('leganto/noListAvailable',get_string('config_noListAvailable', 'block_leganto'),get_string('config_noListAvailableDesc', 'block_leganto'),get_string('config_noListAvailableDefault', 'block_leganto')));

$settings->add(new admin_setting_configcheckbox('leganto/openNewWindow', get_string('config_openNewWindow', 'block_leganto'),'', 1, true));

$settings->add(new admin_setting_configcheckbox('leganto/publishedListsOnly', get_string('config_publishedListsOnly', 'block_leganto'),'', 0));

$sortOptions = array(
    'Name'=>get_string('config_sortByName', 'block_leganto'),
    'ModificationDate'=>get_string('config_sortByModification', 'block_leganto'));

$settings->add(new admin_setting_configselect('leganto/sortBy', get_string('config_sortBy', 'block_leganto'), '', 'Name', $sortOptions));

$sortDirOptions = array(
        'ASC'=>get_string('config_sortAscending', 'block_leganto'),
        'DESC'=>get_string('config_sortDescending', 'block_leganto'));

$settings->add(new admin_setting_configselect('leganto/sortByOrder', get_string('config_sortByDir', 'block_leganto'), '', 'ASC', $sortDirOptions));

$settings->add(new admin_setting_configcheckbox('leganto/displayItemCount', get_string('config_displayItemCount', 'block_leganto'),'', 1, true));

$settings->add(new admin_setting_configcheckbox('leganto/displayLastUpdated', get_string('config_displayLastUpdated', 'block_leganto'),'', 1, true));

$settings->add(new admin_setting_configtext('leganto/almaApiUrl', get_string('config_almaApiUrl', 'block_leganto'), get_string('config_almaApiUrlDesc', 'block_leganto'), 'https://api-eu.hosted.exlibrisgroup.com'));

$settings->add(new admin_setting_configtext('leganto/apiKey',get_string('config_apiKey', 'block_leganto'), get_string('config_apiKeyDesc', 'block_leganto'), ''));

$settings->add(new admin_setting_configtext('leganto/ltiProfile',get_string('config_ltiProfile', 'block_leganto'),'', ''));

$settings->add(new admin_setting_configtext('leganto/permalinkBaseUrl',get_string('config_permalinkBaseUrl', 'block_leganto'), get_string('config_permalinkBaseUrlDesc', 'block_leganto'), ''));

$settings->add(new admin_setting_configtext('leganto/institutionCode',get_string('config_institutionCode', 'block_leganto'),'',''));

$authOptions = array(
    'LOCAL'=>get_string('config_authLocal', 'block_leganto'),
    'SAML'=>get_string('config_authSaml', 'block_leganto'),
    'CAS'=>get_string('config_authCas', 'block_leganto'));

$settings->add(new admin_setting_configselect('leganto/auth', get_string('config_authSetting', 'block_leganto'), '', 'LOCAL', $authOptions));

$settings->add(new admin_setting_configcheckbox('leganto/useCache',get_string('config_useCache', 'block_leganto'), get_string('config_useCacheDesc', 'block_leganto'), ''));

$settings->add(new admin_setting_configtext('leganto/cacheExpiration',get_string('config_cacheExpiration', 'block_leganto'), get_string('config_cacheExpirationDesc', 'block_leganto'), '', PARAM_INT));

$settings->add(new admin_setting_configcheckbox('leganto/debug', get_string('config_debugSetting', 'block_leganto'),'', 0));



