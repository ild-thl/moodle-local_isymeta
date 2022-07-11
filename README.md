# moodle-local_isymeta
Adds additional settings to the seettings navigation menu of a course, to manage additional metadata, that describes the courses content and other properites.

This metadata can either be used by other plugins to display more details about a course before a use has enroled into it. See [moodle-block_isymetaselect](https://github.com/ild-thl/moodle-block_isymetaselect).

Otherwise this metadata can be exported as a json file to the [MOOChub](https://moochub.org/) Portal.
The MOOChub data will be generated on demand when accessing the following URL: yourmoodlesite.com/local/ildmeta/get_moochub_courses.php
To generate data that is compatible with BIRD, access the following URL: yourmoodlesite.com/local/ildmeta/get_bird_courses.php

Beware these URLs can be accessed by anyone. There are no login or capability checks made. Make sure that no sensitive data is used.

## Usage
Before describing your first set of courses, you should edit the vocabularies and providers, that can be used in the form.

To create a new provider access /local/ildmeta/edit_provider.php or go to Site administration -> Plugins -> Local Plugins -> ILD Meta -> Edit provider

To edit vocabularies for coursetypes, courseformats, audineces, and subjectareas access /local/ildmeta/edit_vocabulary.php or got to Site administration -> Plugins -> Local Plugins -> ILD Meta -> Edit vocabulary. Here you can edit or delete existing terms. Make sure you use the correct json format to describe your data. You can add terms in diffrent languages by using the correct language code. See [list of language codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).

## Installation
    git clone https://github.com/ild-thl/moodle-block_isymetaselect.git ildmeta

## Upgrade
When you already have a version of this plugin installed and want to upgrade to this version, we suggest to reinstall the plugin completely, because the upgrade.php script is not yet updated to make all the necessary changes. When reimporting the sql data after reinstallation, beware that the property "university" was renamed to "provider".
    
## Dependencies
This moodle plugin is required by [moodle-block_isymetaselect (master branch)](https://github.com/ild-thl/moodle-block_isymetaselect/tree/master).
