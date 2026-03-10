OER Collection
===================

This file is part of the mod_oercollection plugin for Moodle - <http://moodle.org/>

*Author:* Angela Baier, Adrian Czermak, Karri Pajarinen

*Copyright:* 2024 [University of Vienna](https://www.univie.ac.at/)

*License:* [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)

Description
-----------
With the OER collection resource, you can search for and find available Open Educational Resources (OER) from the [OERhub](https://oerhub.at) and embed them directly into a Moodle course.

Usage
-----------
Teachers want to search for published Open Educational Resources (OER) via [OERhub](https://oerhub.at) to use them in their own lecture, such as documents, videos, podcasts or other materials. In order to refine their search, it is possible to filter the results according to various criteria: discipline, media type, language and published from/ to.
OER of choice can be added easily to the OER Collection and can be supplemented with further notes for didactic purpose.

Students can access the related OER directly via the provided link in the module.

Requirements
-----------
The plugin is available for Moodle 4.4+.

* The subplugin "OER-API - OERhub" is included in this repository and will be installed automatically.

Installation
-----------
* Copy the code directly to the mod/oercollection directory.
* Log into Moodle as administrator.
* Open the administration area (http://your-moodle-site/admin) to start the installation automatically.

Privacy API
-----------
The plugin fully implements the Moodle Privacy API.

Documentation
-----------
You can find further information to the plugin on the [Wiki of the University of Vienna](https://wiki.univie.ac.at/x/to2WHg).

Configuration
-----------
The following admin settings are available under *Site administration > Plugins > Activity modules > OER Collection*:

* **Active OER API provider** (`mod_oercollection/activeoerapi`): If two OER API providers are present due to duplicate installation or previous versions: select which OER API provider plugin should be set active.
* **Request URL** (`oerapi_oerhub/requesturl`): The URL of the OERhub server. Default: `https://portal.oerhub.at/search`
* **Filter media type** (`oerapi_oerhub/filtermediatype`): Optional. Restrict displayed media types by entering a comma-separated list of file extensions (e.g. `mp4,pdf`). If left empty, all media types are shown.
* **Media type icon** (`oerapi_oerhub/mediatypeicon`): Optional. Define icons for each displayed media type as a key/value pair in JSON style, where key is the mediatype in OERhub and value is the Moodle icon (e.g. `{"pdf":"f/pdf"}`). If left empty, OER will be displayed without an icon.

Bug Reports / Support
-----------
We try our best to deliver bug-free plugins, but we cannot test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/academic-moodle-cooperation/moodle-mod_oercollection/issues). Please
provide a detailed bug description, including the plugin and Moodle version and, if applicable, a
screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful
and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.

License
-----------
This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.

Good luck and have fun!