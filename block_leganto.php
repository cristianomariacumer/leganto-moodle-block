<?php
// Released under the LGPL Licence - http://www.gnu.org/licenses/lgpl.html. Anyone is free to change or redistribute this code.

class block_leganto extends block_base {
    function init() {
        $this->title   = get_config('leganto', 'blockTitle');
    }

    function get_content() {
        global $COURSE;

        if ($this->content !== NULL){
        return $this->content;
        }

        $this->content =  new stdClass;

        $configError = $this->getConfigurationErrors();

        if ($configError) {
            $this->content->text = $configError;
            return $this->content;
        }

        $renderable = new \block_leganto\output\main();
        $renderer = $this->page->get_renderer('block_leganto');

        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';

        return $this->content;
    }

    function has_config() {
        return true;
    }

    /**
     * Get the reading lists for a course.
     *
     * @param string $courseCode The course code
     * @return array The reading lists for the course
     */
    function getReadingLists($courseCode = '') {
        $ltiProfile = get_config('leganto', 'ltiProfile');
        $normalizeByLtiProfile = $ltiProfile ? '&normalize=' . urlencode($ltiProfile) : '';
        $courseUrl = get_config('leganto', 'almaApiUrl') . "/almaws/v1/courses";
        $courseUrlParameters = "exact_search=true&q=code~". urlencode($courseCode) . $normalizeByLtiProfile;

        $courses = $this->callAlmaAPI($courseUrl, $courseUrlParameters);

        if(empty($courses['@attributes'])){
            return array();
        }

        $totalCourses = $courses['@attributes']['total_record_count'];

        $this->debug("Total courses: $totalCourses");

        if($totalCourses == 0){
            //try search by searchableIds
            $courseUrlParameters = str_replace("code~", "searchableId~", $courseUrlParameters);
            $courses = $this->callAlmaAPI($courseUrl, $courseUrlParameters);

            $totalCourses = $courses['@attributes']['total_record_count'];

            if (empty($courses['@attributes'])) {
                return array();
            }

            $this->debug("Total courses: $totalCourses");

            if($totalCourses == 0){
                return array();
            }
        }

        //iterate over all courses
        $allCourses = $totalCourses > 1 ? $courses['course'] : array_values($courses);

        $allRls = array();

        for ($i=0; $i < count($allCourses); $i++) {

            if(empty($allCourses[$i]['@attributes'])){
                continue;
            }

            $rlURL = $allCourses[$i]['@attributes']['link'];

            if($rlURL == NULL){
                continue;
            }

            $rlURL .= "/reading-lists";

            $readingListsApi = ($this -> callAlmaAPI($rlURL));

            if(empty($readingListsApi['reading_list']) || count($readingListsApi['reading_list']) == 0){
                $this->debug('course[code: ' .$allCourses[$i]['code'] .' , id: ' . $allCourses[$i]['id'] . '] has no reading lists');
                continue;
            }

            $onlyOneRl = $this->is_assoc($readingListsApi['reading_list']);
            $readingLists = $onlyOneRl ? array_values($readingListsApi) : $readingListsApi['reading_list'];

            if($readingLists){
                $readingListsCount = $onlyOneRl ? 1 : count($readingLists);
                $this->debug('course[code: ' .$allCourses[$i]['code'] .' , id: ' . $allCourses[$i]['id'] . '] has ' . $readingListsCount . ' reading lists');
                $allRls = array_merge($allRls, $readingLists);
            }
        }

        $this->debug('Total reading lists: ' . (count($allRls) - 1));

        $readinglists = $this->processLists($allRls);

        return $readinglists;
    }

    function contextualTime($d, $large_ts=false) {
        $small_ts = strtotime($d .' ');

        if(!$large_ts) $large_ts = time();

        //add timezone offset
        $small_ts -= date('Z');

        $n = $large_ts - $small_ts;

        //echo  $large_ts . "largo " . date("Y-m-d H:i:s", $large_ts) . "<br>";
        if($n <= 1) return 'less than 1 second ago';
        if($n < (60)) return 'a few seconds ago';
        if($n < (60*60)) { $minutes = round($n/60); return 'about ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago'; }
        if($n < (60*60*16)) { $hours = round($n/(60*60)); return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago'; }
        if($n < (time() - strtotime('yesterday'))) return 'yesterday';
        if($n < (60*60*24)) { $hours = round($n/(60*60)); return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago'; }
        if($n < (60*60*24*6.5)) return 'about ' . round($n/(60*60*24)) . ' days ago';
        if($n < (time() - strtotime('last week'))) return 'last week';
        if(round($n/(60*60*24*7))  == 1) return 'about a week ago';
        if($n < (60*60*24*7*3.5)) return 'about ' . round($n/(60*60*24*7)) . ' weeks ago';
        if($n < (time() - strtotime('last month'))) return 'last month';
        if(round($n/(60*60*24*7*4))  == 1) return 'about a month ago';
        if($n < (60*60*24*7*4*11.5)) return 'about ' . round($n/(60*60*24*7*4)) . ' months ago';
        if($n < (time() - strtotime('last year'))) return 'last year';
        if(round($n/(60*60*24*7*52)) == 1) return 'about a year ago';
        if($n >= (60*60*24*7*4*12)) return 'about ' . round($n/(60*60*24*7*52)) . ' years ago';
        return false;
    }

    function callAlmaAPI($url, $parameters=''){
	global $CGF;

	if(substr($url, 0, 4 ) !== "http"){
		//not production
		$url = get_config('leganto', 'almaApiUrl') . $url;
	}

        $apiKey = $this->getApiKey();
        $apiUrl = $url . "?apikey=$apiKey&" . $parameters;

	$this->debug("Calling API URL:	$apiUrl");

        // using php curl
        $ch = curl_init();
        $header=array("content-type"=>"application/xml");
        $options = array(
            CURLOPT_URL            => $apiUrl, // tell curl the URL
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_HTTP_VERSION      => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER     => $header
        );
	    
	//add support for system proxy
	if(isset($CFG->proxyhost) && $CFG->proxyhost != "") {
            $proxyhost = $CFG->proxyhost . ":" . $CFG->proxyport;
            $options[CURLOPT_PROXY] = $proxyhost;
            $oprions[CURLOPT_FOLLOWLOCATION] = 1;
                if(isset($CFG->proxytype) && $CFG->proxytype == "SOCKS5"){
                     $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
                }
                if(isset($CFG->proxyuser) && $CFG->proxyuser != ""){
                     $proxyauth = $CFG->proxyuser . ":" . $CFG->proxypassword;
                     $options[CURLOPT_PROXYUSERPWD] = $proxyauth;
                }
        }


        curl_setopt_array($ch, $options);
        $response = curl_exec($ch); // execute the request and get a response
        $data = array();
        if($response){
            $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);

			$this->debug("Response: $json");

            $data = json_decode($json,true); // decode the returned JSON data
        }else{
			$this->debug("Response is empty");
		}

        curl_close($ch);

        return $data;
    }

    /**
     * Process lists.
     *
     * @param array $lists Array of lists
     * @return array The processed reading lists
     */
    function processLists($lists){

        $this->debug('Start processing reading lists');

        // Sort and filter lists.
        $lists = $this->sortLists($lists);

        $permalink = $this->getPermalink();

        $processedLists = array();
        foreach ($lists as $key => $list) {
            if (empty($list) || empty($list['id'])) {
                // Not a list.
                continue;
            }

            $lid = $list['id'];
            if(!$lid){
                // Not a list.
                continue;
            }

            $this->debug('Processing rl[name: ' .$list['name'].' , id: '.$lid .']');

            if (key_exists($lid, $processedLists)) {
                $this->debug('List '. $lid .' has already been processed');
                continue;
            }

            $validList = $this->isListValid($list);

            if(!$validList){
                $this->debug('List '. $lid .' is not valid');
                continue;
            }

            // List link.
            $href = str_replace("listId", $lid, $permalink['url']);
            $list['link'] = html_writer::tag('a', $list['name'], array('href' => $href, 'target' => $permalink['target']));

            // Items count.
            if (get_config('leganto', 'displayItemCount')) {
                $citationUrl = $list['@attributes']['link'] . "/citations";
                $rlCitations = $this->callAlmaAPI($citationUrl);

                $htmlCountCitationText = '';
                if(!empty($rlCitations['citation']) && $rlCitations['citation']){
                    //list has citations
                    $onlyOneCit = $this->is_assoc($rlCitations['citation']);
                    $citCount = $onlyOneCit ? 1 : count($rlCitations['citation']);

                    $this->debug("List has $citCount citations");

                    $htmlCountCitationText = ' (' . $citCount . ' ' . get_string('item', 'block_leganto') . ($onlyOneCit ? '' : 's') . ')';
                }
                $list['item_count'] = html_writer::tag('span', $htmlCountCitationText);
            } else {
                $list['item_count'] = '';
            }

            // Modified date.
            $tagDate = '';
            if ($list['last_modified_date'] !== NULL && get_config('leganto', 'displayLastUpdated')){
                $lastUpdatedTime = $this->contextualTime($list['last_modified_date']);
                $lastUpdated = get_string('lastUpdated', 'block_leganto') .' '. $lastUpdatedTime;
                $tagDate = html_writer::tag('div', $lastUpdated);
            }
            $list['modified_date'] = $tagDate;

            $processedLists[$lid] = $list;
        }

		$this->debug('Done processing reading lists');

        return $processedLists;
    }

    function isListValid($list){

        if(!$list['id']){
            //not a list
            return FALSE;
        }

        if($list['visibility'] == 'ARCHIVED'){
            //not an actice list
			$this->debug('List is ARCHIVED');
            return FALSE;
        }

        $alsoDisplayUnPublishedLists = get_config('leganto', 'publishedListsOnly');

        if($alsoDisplayUnPublishedLists == 0){
            return $list['visibility'] !== 'DRAFT';
        }

        return true;
    }

    function getPermalink(){
        $permalink = array();
        if(get_config('leganto', 'openNewWindow') == 1){
            $permalink['target'] = '_blank';
        }

        $baseUrl = get_config('leganto', 'permalinkBaseUrl');
        $institutionCode = get_config('leganto', 'institutionCode');

        $authMethod = get_config('leganto', 'auth');
        $permalink['url'] = $baseUrl.'/leganto/public/'.$institutionCode.'/lists/listId?auth=' . $authMethod;

        return $permalink;
    }

    function sortLists($lists){
        $sortBy = get_config('leganto', 'sortBy');;
        usort($lists,array($this,'sortBy'.$sortBy));

        $direction  = get_config('leganto', 'sortByOrder');

        if($direction == 'DESC'){
            $lists = array_reverse($lists);
        }

        return $lists;
    }

    function sortByName($a,$b){
		if(empty($a["name"]) || empty($b["name"])){
			return 1;
		}

        return strcmp($a["name"], $b["name"]);
    }

    function sortByModificationDate($a,$b){
		if(empty($a["last_modified_date"]) || empty($b["last_modified_date"])){
			return 1;
		}

        return strtotime($a["last_modified_date"]) - strtotime($b["last_modified_date"]);
    }

    function is_assoc($arr){
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

	function isDebugMode(){
        return get_config('leganto', 'debug') == 1;
    }

	function debug($msg){
		if($this->isDebugMode()){
            if (!isset($this->debugmsg)) {
                $this->debugmsg = '';
            }

			$this->debugmsg .= "<script> console.log('$msg');</script>";
		}
	}

    function getConfigurationErrors(){
        $errors = '';
        if(!get_config('leganto', 'almaApiUrl')){
            $errors .= html_writer::tag('div', 'Alma API URL is not configured');
        }

        if(!$this->getApiKey()){
            $errors .= html_writer::tag('div', 'API key is not configured');
        }

        if(!get_config('leganto', 'permalinkBaseUrl')){
            $errors .= html_writer::tag('div', 'Permalink base URL is not configured');
        }

        if(!get_config('leganto', 'institutionCode')){
            $errors .= html_writer::tag('div', 'Institution code is not configured');
        }

        return $errors;
    }

	function getApiKey(){
        return get_config('leganto', 'apiKey') ? get_config('leganto', 'apiKey') : get_config('leganto', 'apikey');
    }
}
