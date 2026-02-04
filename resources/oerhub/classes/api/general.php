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

namespace oerapi_oerhub\api;

use core_reportbuilder\local\helpers\aggregation;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Oerhub
 *
 * @package    oerapi_oerhub
 * @copyright  2024 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class general extends \mod_oercollection\api\general {
    private $baseurl;
    private $oercollectionid;
    private $mediaiconmapping;

    public function __construct($baseurl, $oercollectionid) {
        $this->baseurl = $baseurl;
        $this->oercollectionid = $oercollectionid;
        $oerhubconfig = get_config('oerapi_oerhub');
        $this->mediaiconmapping = json_decode($oerhubconfig->mediatypeicon, true);
    }

    public function get_resource_html($oerresourceid) {
        $response = $this->call_repo(null, $oerresourceid);

        if ($response === false || $response == '{}' || empty($response)) {
            return null;
        }

        $jsondata = json_decode($response, true);

        if ($jsondata === null || !isset($jsondata['data'])) {
            return null;
        }

        return $this->create_resource_html($jsondata['data']);
    }

    public function get_search_form($searchstring = null) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        return $renderer->render_from_template('oerapi_oerhub/searchform', ['actionurl' => $this->baseurl, 'searchstring' => $searchstring]);
    }

    public function is_api_available() {
        $oerhubconfig = get_config('oerapi_oerhub');
        return !empty($oerhubconfig->requesturl);
    }

    public function get_results($searchstring = null, $filteroptions = null, $page = 0, $size = 20) {
        if (is_null($searchstring)) {
            return null;
        }

        $decodedvalues = ['query' => $searchstring, 'size' => $size, 'page' => $page];
        $showfilter = null;

        if ($filteroptions != '{}' && !is_null($filteroptions)) {
            $filteroptions = json_decode($filteroptions, true);

            if (!empty($filteroptions['disciplines'])) {
                $decodedvalues['disciplines'] = [['id' => $filteroptions['disciplines']]];
            }

            if (!empty($filteroptions['mediatype']) && $filteroptions['mediatype'] != '') {
                $decodedvalues['mediaTypes'] = [$filteroptions['mediatype']];
            }

            if (!empty($filteroptions['languages'])) {
                $decodedvalues['languages'] = [['id' => $filteroptions['languages']]];
            }

            if (!empty($filteroptions['yearfrom'])) {
                $decodedvalues['startDate'] = $filteroptions['yearfrom'] . '-01-01';
            }

            if (!empty($filteroptions['yearto'])) {
                $decodedvalues['endDate'] = $filteroptions['yearto'] . '-12-31';
            }
            $showfilter = true;
        }

        if (!array_key_exists('mediaTypes', $decodedvalues)) {
            $oerhubconfig = get_config('oerapi_oerhub');
            if ($oerhubconfig->filtermediatype) {
                $filtermediatype = explode(',', $oerhubconfig->filtermediatype);
                $filtermediatype = ['', ...$filtermediatype];
                $decodedvalues['mediaTypes'] = $filtermediatype;
            }
        }

        $searchstring = json_encode($decodedvalues);
        $response = $this->call_repo($searchstring);

        $jsondata = json_decode($response, true);
        $results = [];

        if ($response !== false && $jsondata !== null && isset($jsondata['data']['hits']['hits'])) {
            foreach($jsondata['data']['hits']['hits'] as $item) {
                $results[] = [
                    'oerhtml' => $this->create_resource_html($item),
                    'oerhubid' => $item['_id'],
                    'link' => $item['_source']['oea_object_direct_link'],
                    'title' => $item['_source']['oea_title'],
                ];
            }
        }

        if (count($results) != 0) {
            $foundcount = $jsondata['data']['hits']['total']['value'];
            $oersearchresultlist = $results;
        } else {
            $foundcount = 0;
            $oersearchresultlist = [];
        }

        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $PAGE->requires->js_call_amd('oerapi_oerhub/filterutil', 'init');

        $filterdata = null;
        if ($jsondata !== null && isset($jsondata['disciplines'])) {
            $filterdata = $this->create_filter_form_data($jsondata, $decodedvalues);
        }

        $templatecontext = [
            'oersearchresultlist' => $oersearchresultlist,
            'oerid' => $this->oercollectionid,
            'filterdata' => $filterdata,
            'foundcount' => $foundcount,
            'open' => $showfilter,
            'selected' . $size => true,
        ];

        $resulthtml = $renderer->render_from_template('oerapi_oerhub/resultlist', $templatecontext);

        return ['resulthtml' => $resulthtml, 'foundcount' => $foundcount];
    }

    private function create_filter_form_data($jsondata, $filteroptions) {
        $lang = current_language();
        $namelang = ($lang !== 'de') ? 'name_en' : 'name_de';

        $filterdata = [
            'actionurl' => $this->baseurl,
            'filteroptions' => [
                $this->create_filter_option('disciplines', $jsondata['disciplines'], $filteroptions['disciplines'][0] ?? null, 'id', $namelang),
                $this->create_filter_option('mediatype', $jsondata['mediaType'], $filteroptions['mediaTypes'][0] ?? null),
                $this->create_filter_option('languages', $jsondata['languages'], $filteroptions['languages'][0] ?? null, 'id', $namelang),
            ],
            'yearfrom' => isset($filteroptions['startDate']) ? substr($filteroptions['startDate'], 0, 4) : null,
            'yearto' => isset($filteroptions['endDate']) ? substr($filteroptions['endDate'], 0, 4) : null,
        ];

        return $filterdata;
    }

    private function create_filter_option($name, $data, $selectedOption = null, $valueKey = null, $labelKey = null) {
        $options = array_map(function($item) use ($selectedOption, $valueKey, $labelKey) {
            $value = $valueKey ? $item[$valueKey] : $item;
            $optionlabel = $labelKey ? $item[$labelKey] : $item;

            return [
                'value' => $value,
                'optionlabel' => $optionlabel,
                'selected' => $selectedOption && ($selectedOption['id'] ?? $selectedOption) == $value
            ];
        }, $data);

        return [
            'name' => $name,
            'label' => get_string($name, 'oerapi_oerhub'),
            'options' => $options
        ];
    }

    /**
     * Renders resource HTML based on JSON data from an external OER source.
     *
     * @param array $jsondata The data structure containing OER details.
     * @return string HTML ready to be displayed.
     */
    private function create_resource_html($jsondata): string {
        global $PAGE, $OUTPUT;

        $source = $jsondata['_source'];
        $licenseicons = array_flip([
            'BY', 'CC', 'NC', 'NC-EU',
            'NC-JP', 'ND', 'PD', 'REMIX',
            'SA', 'SAMPLING.PLUS',
            'SAMPLING', 'SHARE', 'ZERO',
        ]);
        $sublicenses = explode('-', $source['oea_classification_02'] ?? '');

        // Build license icon HTML.
        $iconhtml = [];
        foreach ($sublicenses as $sublicense) {
            if (isset($licenseicons[$sublicense])) {
                $iconhtml[] = $OUTPUT->pix_icon(
                    strtolower($sublicense),
                    '', // Optionally pass alt text here
                    'oerapi_oerhub'
                );
            }
        }

        $mediatype = $source['oea_classification_05'] ?? null;
        $mediaicon = false;

        if ($mediatype && !is_null($this->mediaiconmapping) && isset($this->mediaiconmapping[$mediatype])) {
            $mediaicon = $OUTPUT->pix_icon(
                $this->mediaiconmapping[$mediatype],
                get_string('mediaicontooltip', 'oerapi_oerhub') . $mediatype,
                'core',
                ['class' => 'big-icon'],
            );
        }

        $abstract = $source['oea_abstract'] ?? '';
        $format = strip_tags($abstract) !== $abstract ? FORMAT_HTML : FORMAT_MOODLE;

        // Prepare context for the template, using ?? '' to avoid notices.
        $templatecontext = [
            'title'           => format_string($source['oea_title'] ?? ''),
            'abstract'        => format_text($abstract, $format),
            'thumbnail'       => $source['oea_thumbnail_url']   ?? '',
            'authors'         => implode('; ', $source['oea_authors'] ?? []),
            'uploaddate'      => $this->format_date($source['oea_classification_03'] ?? ''),
            'licenseicons'    => implode('', $iconhtml),
            'license'         => $source['oea_classification_02'] ?? '',
            'licenseurl'      => $source['rights']['description'] ?? '',
            'oerresourcelink' => $source['oea_object_direct_link'] ?? '',
            'mediaicon'       => $mediaicon,
            'oerhubid'        => $jsondata['_id'] ?? uniqid('oer_'),
        ];

        $renderer = $PAGE->get_renderer('core');
        return $renderer->render_from_template('oerapi_oerhub/resource', $templatecontext);
    }

    /**
    * Format date according to the user's selected language.
    * EN: YYYY-MM-DD
    * DE: DD.MM.YYYY
    *
    * @param string $datestring The date string to format (expected format: YYYY-MM-DD)
    * @return string Formatted date string
    */
    private function format_date(string $datestring): string {
        if (empty($datestring)) {
            return '';
        }

        // Parse the date assuming YYYY-MM-DD format from API.
        $timestamp = strtotime($datestring);

        if ($timestamp === false) {
            return $datestring;
        }

        $lang = current_language();

        if ($lang === 'de') {
            return date('d.m.Y', $timestamp);
        } else if ($lang === 'en') {
            return date('Y-m-d', $timestamp);
        } else {
            return userdate($timestamp, get_string('strftimedate', 'langconfig'));
        }
    }

    private function call_repo($postdata=null, $resourceid=null) {
        $oerhubconfig = get_config('oerapi_oerhub');
        $serverurl = $oerhubconfig->requesturl;

        if (empty($serverurl)) {
            return false;
        }

        $curloptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];

        if (!is_null($resourceid)) {
            $serverurl .= $resourceid;
            $curloptions[CURLOPT_CUSTOMREQUEST] = 'GET';
        } else {
            $curloptions[CURLOPT_CUSTOMREQUEST] = 'POST';
            $curloptions[CURLOPT_POSTFIELDS] = $postdata;
            $curloptions[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        }

        $curloptions[CURLOPT_URL] = $serverurl;
        $curl = curl_init();
        curl_setopt_array($curl, $curloptions);
        $response = curl_exec($curl);
        curl_close($curl);


        //$url = 'https://oerhub.at/search/';
        //$postdata = json_encode(['query' => 'Inklusion']);
        /*$postdata = '{
            "query": "Inklusion"
        }';*/
        //$response = download_file_content($url, null, $postdata, true);


        return $response;
    }
}
