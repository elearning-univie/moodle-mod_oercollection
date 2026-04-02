OER Collection
===================

This file is part of the mod_oercollection plugin for Moodle - <http://moodle.org/>

*Author:* Angela Baier, Adrian Czermak, Karri Pajarinen

*Copyright:* 2024 [University of Vienna](https://www.univie.ac.at/)

*License:* [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)

Description
-----------
The OER Collection plugin enables teachers to easily search, select, and integrate Open Educational Resources (OER) directly within Moodle courses. By connecting to the external repository [OER-Directory](https://portal.oerhub.at/en/) (part of the Austrian [OERhub](https://www.oerhub.at/en/)), it provides seamless access to quality-assured, openly licensed materials and supports their didactic integration into teaching scenarios.

Usage
-----------
The OER Collection allows teachers to create and manage collections of OER from external OER infrastructures and make them easily available for students:

* **Search and discover OER** within Moodle using a centralized interface
* **Filter results** based on relevant metadata such as format, subject or licensing
* **Preview and select resources** without leaving the learning environment
* **Integrate OER directly into courses** as structured learning elements
* **Provide didactic context**, such as assignments, learning goals, or instructions

Furthermore, the plugin ensures **transparency of licensing information**, including Creative Commons licenses, helping users to understand reuse conditions and comply with legal requirements.

**Example use cases**
* A lecturer searches for openly licensed videos on a specific topic and embeds them directly into a Moodle course with guiding questions.
* A course designer integrates multiple OER into a structured learning sequence, adding context and tasks for students.
* A teacher reuses existing OER and adapts them for a specific teaching scenario without leaving Moodle.

Requirements
-----------
The plugin is available for Moodle 4.4+.

* You need to install the subplugin "OER-API - OERhub" to be able to search in the OERhub (https://github.com/elearning-univie/moodle-oerapi_oerhub).

Installation
-----------
* Copy the code directly to the moodleroot/mod/oercollection directory.
* Log into Moodle as administrator.
* Open the administration area (http://your-moodle-site/admin) to start the installation automatically.

Privacy API
-----------
The plugin fully implements the Moodle Privacy API.

Documentation
-----------
You can find further information to the plugin on the [Wiki of the University of Vienna](https://wiki.univie.ac.at/x/to2WHg).

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