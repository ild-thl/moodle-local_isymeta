# moodle-local_isymeta
Adds an additional menu item to the seettings navigation menu of a course, to manage additional metadata, that describes the courses content and other properites.

This metadata is consumed by other plugins to display more details about a course before a user has enroled into it. See [moodle-block_isymetaselect](https://github.com/ild-thl/moodle-block_isymetaselect).

Additionally the plugin provides a way to export metadata as a [MOOChub/schema](https://github.com/MOOChub/schema) compatible json file. 
This allows publishing courses on [MOOChub](https://moochub.org/) by providing the relevant metadata.
The MOOChub data will be generated on demand when accessing the following URL: yourmoodlesite.com/local/ildmeta/get_moochub_courses.php
To generate data that is compatible with BIRD, access the following URL: yourmoodlesite.com/local/ildmeta/get_bird_courses.php

Beware these URLs can be accessed by anyone. There are no login or capability checks made. Make sure that no sensitive data is included.

## Usage
Before describing your first set of courses, you should edit the vocabularies and providers, that are available in the metadata form.

To create a new provider access /local/ildmeta/edit_provider.php or go to Site administration -> Plugins -> Local Plugins -> ILD Meta -> Edit provider

To edit vocabularies for coursetypes, courseformats, audiences, and subjectareas access /local/ildmeta/edit_vocabulary.php or go to Site administration -> Plugins -> Local Plugins -> ILD Meta -> Edit vocabulary. Here you can edit or delete existing terms. Make sure you use the correct json grammar to describe your data. You can add terms in diffrent languages by using the respective language code. See [list of language codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).

## Installation
The plugin needs to be installed inside the "local" folder and needs to be named ildmeta. 

    git clone -b master https://github.com/ild-thl/moodle-local_isymeta.git ildmeta
    
## Dependencies
Ther are no dependencies for this plugin to work.
To show additional course metadata managed by this plugin on a course preview page you need to install the following plugin as well:
[moodle-block_isymetaselect (master branch)](https://github.com/ild-thl/moodle-block_isymetaselect/tree/master).
