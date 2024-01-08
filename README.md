# moodle-local_isymeta

Adds an additional menu item to the seettings navigation menu of a course, to manage additional metadata, that describes the courses content and other properites.

This metadata is consumed by other plugins to display more details about a course before a user has enroled into it. See [moodle-block_isymetaselect](https://github.com/ild-thl/moodle-block_isymetaselect).

## export metadata

Additionally the plugin provides a way to export metadata as a [MOOChub/schema](https://github.com/MOOChub/schema) compatible json file.
This allows publishing courses on [MOOChub](https://moochub.org/) by providing the relevant metadata.
The MOOChub data will be generated on demand when accessing the following URL: yourmoodlesite.com/local/ildmeta/get_moochub_courses.php
To generate data that is compatible with BIRD, access the following URL: yourmoodlesite.com/local/ildmeta/get_bird_courses.php
Beware these URLs can be accessed by anyone. There are no login or capability checks made. Make sure that no sensitive data is included.

### Moochub metadata by course idnumber or course id

Providing the manually given course id (Moodles idnumber) MOOChub data for a single course will be generated on demand when accessing the following URL: `yourmoodlesite.com/local/ildmeta/get_moochub_courses.php?idn=IDNUMBER`
or for more than one idn use an idn[] e.g.:
`yourmoodlesite.com/local/ildmeta/get_moochub_courses.php?idn[]=IDNUMBER&idn[]=IDNUMBER`.
The same will work for the Moodle course ids by the use of the url parameter id instead of idn in the examples above.

### Moochub version

Per default moochub v3 is exported. Use moochub-version=VERSION in the url parameter list or in the Accept request HTTP header for other versions e.g.:
`yourmoodlesite.com/local/ildmeta/get_moochub_courses.php?moochub-version=VERSION`
or

```bash
curl --location --globoff 'yourmoodlesite.com/local/ildmeta/get_moochub_courses.php' --header 'Accept: moochub-version=3'  
```

## Usage

Before describing your first set of courses, you should edit the vocabularies and providers, that are available in the metadata form.

To create a new provider access /local/ildmeta/edit_provider.php or go to Site administration -> Plugins -> Local Plugins -> ILD Meta -> Edit provider

To edit vocabularies for coursetypes, courseformats, audiences, and subjectareas access /local/ildmeta/edit_vocabulary.php or go to Site administration -> Plugins -> Local Plugins -> ILD Meta -> Edit vocabulary. Here you can edit or delete existing terms. Make sure you use the correct json grammar to describe your data. You can add terms in diffrent languages by using the respective language code. See [list of language codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).

### Compencies
The course competences are added to the metadata if a competence from one of the following frameworks is attached to the course:
* ESCO
* DigComp
* GRETA
  
Note: The name of the competency framework must match exactly the name given in the list above. The version number of the framework needs to be set as ID-Nummer of the competency framework.


## Installation

The plugin needs to be installed inside the "local" folder and needs to be named ildmeta.

```bash
git clone -b master https://github.com/ild-thl/moodle-local_isymeta.git ildmeta
```

To enable schema validation, you can install the opis/json-schema library by running the following command inside the plugin directory:

```bash
composer require opis/json-schema
```

## Dependencies

Ther are no dependencies for this plugin to work.
To show additional course metadata managed by this plugin on a course preview page you need to install the following plugin as well:
[moodle-block_isymetaselect (master branch)](https://github.com/ild-thl/moodle-block_isymetaselect/tree/master).
