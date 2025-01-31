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
 * provider
 *
 * @package   mod_oercollection
 * @copyright 2024 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions related with the oercollection plugin.
 */
class behat_mod_oercollection extends behat_base {

    /**
     * Verifies that the URL of the newly opened tab matches the expected URL.
     *
     * @Then the URL should be :url
     *
     * @param string $url The expected URL.
     * @throws moodle_exception If the URL of the newly opened tab does not match the expected URL.
     */
    public function the_url_should_be($url) {
        $session = $this->getSession();
        $driver = $session->getDriver();

        $windownames = $driver->getWindowNames();

        if (count($windownames) <= 1) {
            throw new \moodle_exception(get_string('error_notab', 'mod_oercollection'));
        }

        $driver->switchToWindow(end($windownames));

        $session->wait(5000, "document.readyState === 'complete'");

        $currenturl = $session->getCurrentUrl();

        if (strpos($currenturl, $url) === false) {
            throw new \moodle_exception(get_string('url_mismatch', 'mod_oercollection', (object)[
                'actual' => $currenturl,
                'expected' => $url,
            ]));
        }
    }
}
