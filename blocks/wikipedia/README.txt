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
 * Wikipedia block is a Moodle block to search in the Wikipedia.
 * 
 * @package    block
 * @subpackage wikipedia
 * @copyright  2006 Aggelos Panagiotakis
 * @copyright  2006 David Horat - http://www.nordakademie.de
 * @copyright  2013 Ralf Krause - http://www.moodletreff.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

======================
 Infomation

The Wikipedia block is a Moodle block to search in the Wikipedia
The block works with Moodle 2.5 (also tested with all other versions 2.x)

======================
 Authors

2013
- Ralf Krause - ralf.krause@gmail.com
- Moodletreff Düsseldorf, Germany - http://www.moodletreff.de

2006
- David Horat - david.horat@gmail.com 
- FH NordAkademie, Germany - http://www.nordakademie.de

2006
- Aggelos Panagiotakis - agelospanagiotakis@gmail.com
- Initial work
- Thanks to Mitsuhiro Yoshida for the Japanese Wikipedia logo and the "global $CFG;" hint

======================
 Features

- Search in Wikipedia with several languages
- Automatic search language selection based on the current interface language
- Automatic logo selection based on the current interface language
- Logo images (only for English, French, German, Greek, Italian, Dutch, Japanese, 
    Polish, Portuguese, Russian, Spanish, and Swedish)
  
======================
 Development Info

- All images are 122 x 36 with a transparent background 
- The logo image filename is calculated using the current language, eg: "wikipedia-en.gif"
- The only string in the language file is string['pluginname'] = 'Wikipedia';
- The string for the search button comes from core_search

======================
 Changelog

1.3
- Changed the code for Moodle 2.5
- Added search in some more languages (but no logos)

1.2
- Changed code to meet Moodle Coding Guidelines
- Added search in Norwegian, Finish and Chinese (still need logos)

1.1
- Added search in Rusish
- Added automatic search language selection based on the current interface language
- Added logo images of French, Greek, Italian, Dutch, Japanese, Polish, Portuguese, Rusish and Swedish
- Added "global $CFG;" to support the directive "register_globals = off"
- Removed the language files. Now it calculates the wikipedia logo filename with the language. (Convention over Configuration principle)

1.0
- Added search in English, French, German, Greek, Italian, Dutch, Japanese, Polish, Portuguese, Spanish and Swedish
- Added logo images of English, German and Spanish
