```
===============================================
=== OJS Block Pages Plugin
=== Version: (see version.xml)
=== Author: Joe Simpson, forked from the work by Juan Pablo Alperin <pkp@alperin.ca>
===============================================
```

## About

This plugin is intended to provide block level content management. It allows
for the creation of static content pages with the assistance of an block editor.

This is a fork of the PKP's static pages plugin whereby the system has been updated
to provide for a block editing experience instead of simply HTML.

## License

This plugin is licensed under the GNU General Public License v3. See the file
COPYING for the complete terms of this license.

## System Requirements

This plugin is compatible with OJS, OMP, and OPS. A compatible version ships
with each application.

## Installation

You will need to run webpack from the package.json commands to build the javascript.

To do this with a place you have NodeJS installed:

```
npm install
npm run build
```

## Management

New pages can be added/edited/deleted through the Plugin Management interface.

The PATH chosen for each page determines where the page is later accessed. To
direct users to static content created with this plugin, place links to
http://www.../index.php/pages/view/%PATH%, where %PATH% is a value you choose.

## Contact/Support

![OICC Press in Collaboration with Invisible Dragon](https://images.invisibledragonltd.com/oicc-collab.png)

This project is brought to you by [Invisible Dragon](https://invisibledragonltd.com/ojs/) in collaboration with
[OICC Press](https://oiccpress.com/)
