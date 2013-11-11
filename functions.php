<?php
error_reporting(0);
/*
 * Debug Mode
   //$RB_DEBUG_MODE = true;
 */


/*
 * Set Sessions
 */

	add_action('init', 'rb_agency_init_sessions');
		function rb_agency_init_sessions() {
			if (!session_id()) {
				session_start();
			}
		}

// Set Mail
	add_filter('wp_mail_content_type','rb_agency_set_content_type');
		function rb_agency_set_content_type($content_type){
					return 'text/html';
		}


// *************************************************************************************************** //
/*
 * Header for Administrative Pages
 */

	// Admin Head Section 
	add_action('admin_head', 'rb_agency_admin_head');
		function rb_agency_admin_head() {
			// Ensure we are in the admin section of wordpress
			if( is_admin() ) {

				// Get Custom Admin Styles
				wp_register_style( 'rbagencyadmin', plugins_url('/style/admin.css', __FILE__) );
				wp_enqueue_style( 'rbagencyadmin' );

				// Load Jquery if not registered
				if ( ! wp_script_is( 'jquery', 'registered' ) )
					wp_register_script( 'jquery', plugins_url( 'https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js', __FILE__ ), false, '1.8.3' );

				// Load custom fields javascript
				wp_enqueue_script( 'customfields', plugins_url('js/js-customfields.js', __FILE__) );
			}
		}
	

// *************************************************************************************************** //
/*
 * Header for Public facing Pages
 */

	add_action('wp_head', 'rb_agency_inserthead');
		// Call Custom Code to put in header
		function rb_agency_inserthead() {
			// Ensure we are NOT in the admin section of wordpress
			if( !is_admin() ) {
				
				// Get Custom Styles
				wp_register_style( 'rbagency-style', plugins_url('RB-Agency/style/forms.css'));
				wp_enqueue_style( 'rbagency-style' );
			}	
		}

        add_action('wp_enqueue_scripts', 'rb_agency_insertscripts');
		
                function rb_agency_insertscripts() {

                        if( !is_admin() ) {
					if(get_query_var('type') == "search-basic" || 
					   get_query_var('type') == "search-badvanced" ){
						wp_enqueue_script( 'customfields-search', plugins_url('js/js-customfields.js', __FILE__) );
						wp_register_style( 'rbagency-formstyle', plugins_url('RB-Agency/style/forms.css'));
						wp_enqueue_style( 'rbagency-formstyle' );
					}
                                
			}	
		}


// *************************************************************************************************** //
/*
 * Customize WordPress Dashboard
 */

	// Pull User Identified Settings/Options 
	$rb_agency_options_arr = get_option('rb_agency_options');
	// Can we show the ads? Or keep it clean?
	$rb_agency_option_advertise = isset($rb_agency_options_arr['rb_agency_option_advertise']) ? $rb_agency_options_arr['rb_agency_option_advertise'] : 0;

	if($rb_agency_option_advertise == 0) {  // Reversed it, now 1 = Hide Advertising

	add_action('wp_dashboard_setup', 'rb_agency_add_dashboard' );
		// Hoook into the 'wp_dashboard_setup' action to register our other functions
		function rb_agency_add_dashboard() {

			global $wp_meta_boxes;

			// Create Dashboard Widgets
			wp_add_dashboard_widget('rb_agency_dashboard_quicklinks', __("RB Agency Updates", rb_agency_TEXTDOMAIN), 'rb_agency_dashboard_quicklinks');
		
			// reorder the boxes - first save the left and right columns into variables
			$left_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
			$right_dashboard = $wp_meta_boxes['dashboard']['side']['core'];
			
			// take a copy of the new widget from the left column
			$rb_agency_dashboard_merge_array = array("rb_agency_dashboard_quicklinks" => $left_dashboard["rb_agency_dashboard_quicklinks"]);
			
			unset($left_dashboard['rb_agency_dashboard_quicklinks']); // remove the new widget from the left column
			$right_dashboard = array_merge($rb_agency_dashboard_merge_array, $right_dashboard); // use array_merge so that the new widget is pushed on to the beginning of the right column's array  
			
			// finally replace the left and right columns with the new reordered versions
			$wp_meta_boxes['dashboard']['normal']['core'] = $left_dashboard; 
			$wp_meta_boxes['dashboard']['side']['core'] = $right_dashboard;
		}

		// Create the function to output the contents of our Dashboard Widget
		function rb_agency_dashboard_quicklinks() {

			// Display Quicklinks
			$rb_agency_options_arr = get_option('rb_agency_options');
			if (isset($rb_agency_options_arr['dashboardQuickLinks'])) {
			  	echo $rb_agency_options_arr['dashboardQuickLinks'];
			}
			$rss = fetch_feed("http://rbplugin.com/category/wordpress/rbagency/feed/");

			// Checks that the object is created correctly 
			if (!is_wp_error($rss)) { 

				// Figure out how many total items there are, but limit it to 5. 
				$maxitems = $rss->get_item_quantity($num_items); 

				// Build an array of all the items, starting with element 0 (first element).
				$rss_items = $rss->get_items(0, $maxitems); 
			}

			echo "<div class=\"feed-searchsocial\">\n";
			if ($maxitems == 0) {
				echo "No Connection\n";
			} else {

				// Loop through each feed item and display each item as a hyperlink.
				foreach ( $rss_items as $item ) {
					echo "  <div class=\"blogpost\">\n";
					echo "    <h4><a href='". $item->get_permalink() ."' title='Posted ". $item->get_date('j F Y | g:i a') ."' target=\"_blank\">". $item->get_title() ."</a></h4>\n";
					echo "    <div class=\"description\">". $item->get_description() ."</div>\n";
					echo "    <div class=\"clear\"></div>\n";
					echo "  </div>\n";
				}
			}
			echo "</div>\n";
			echo "<hr />\n";
			echo "Need help? Check out RB Agency <a href=\"http://rbplugin.com\" target=\"_blank\" title=\"RB Agency Documentation\">Documentation</a>.<br />";
		}
	}


// *************************************************************************************************** //
/*
 * Add Custom Classes to <body>
 */

	add_filter("body_class", "rb_agency_insertbodyclass");
		// Add CSS Class based on URL
		function rb_agency_insertbodyclass($classes) {
			// Remove Blog
			if (rb_is_page("rb_profile")) {
				$classes[] = 'rbagency-profile';
			} elseif (rb_is_page("rb_dashboard")) {
				$classes[] = 'rbagency-dashboard';
			} elseif (rb_is_page("rb_category")) {
				$classes[] = 'rbagency-category';
			} elseif (rb_is_page("rb_register")) {
				$classes[] = 'rbagency-register';
			} elseif (rb_is_page("rb_search")) {
				$classes[] = 'rbagency-search';
			} elseif (rb_is_page("rb_print")) {
				$classes[] = 'rbagency-print';
			} else {
				$classes[] = 'rbagency';
			}
			return $classes;
		}


// *************************************************************************************************** //
/*
 * Add Rewrite Rules based on Path
 */
	add_filter('init','rbflush_rules');
	function rbflush_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
		
	add_filter('rewrite_rules_array','rb_agency_rewriteRules');
		// Adding a new rule
		function rb_agency_rewriteRules($rules) {
			$newrules = array();
			$newrules['search-basic'] = 'index.php?type=search-basic';
			$newrules['search-advanced'] = 'index.php?type=search-advanced';
			$newrules['search-results'] = 'index.php?type=search-result';
			$newrules['profile-category/(.*)/([0-9])$'] = 'index.php?type=category&target=$matches[1]&paging=$matches[2]';
			$newrules['profile-category/([0-9])$'] = 'index.php?type=category&paging=$matches[1]';
			$newrules['profile-category/(.*)'] = 'index.php?type=category&target=$matches[1]';
			$newrules['profile-category'] = 'index.php?type=category&target=all';
			$newrules['profile-casting/(.*)$'] = 'index.php?type=casting&target=$matches[1]';
			$newrules['profile-casting'] = 'index.php?type=casting&target=casting';
			$newrules['profile-print'] = 'index.php?type=print';
			$newrules['profile-email'] = 'index.php?type=email';
			$newrules['dashboard'] = 'index.php?type=dashboard';
			$newrules['client-view/(.*)$'] = 'index.php?type=profilecastingcart&target=$matches[1]';
			$newrules['profile/(.*)/contact'] = 'index.php?type=profilecontact&target=$matches[1]';
			$newrules['profile/(.*)$'] = 'index.php?type=profile&target=$matches[1]';
			$newrules['get-state/(.*)$'] = 'index.php?type=getstate&country=$matches[1]';

			$newrules['version-rb-agency'] = 'index.php?type=version'; // ping this page for version checker

			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_option_profilelist_castingcart  = isset($rb_agency_options_arr['rb_agency_option_profilelist_castingcart']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_castingcart'] : 0;
			
			$rb_agency_option_profilelist_favorite	 = isset($rb_agency_options_arr['rb_agency_option_profilelist_favorite']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_favorite'] : 0;
			
			if ($rb_agency_option_profilelist_favorite) {
				$newrules['profile-favorite'] = 'index.php?type=favorite';
			}
			if ($rb_agency_option_profilelist_castingcart) {
				$newrules['profile-casting-cart'] = 'index.php?type=castingcart';
			}
			return $newrules + $rules;
		}

	// Get Veriables & Identify View Type
	add_action( 'query_vars', 'rb_agency_query_vars' );
		function rb_agency_query_vars( $query_vars ) {
			$query_vars[] = 'type';
			$query_vars[] = 'target';
			$query_vars[] = 'paging';
			$query_vars[] = 'value';
			$query_vars[] = 'country';
			
			return $query_vars;
		}

	// Set Custom Template
	add_filter('template_include', 'rb_agency_template_include', 1, 1);
		function rb_agency_template_include( $template ) {
			if ( get_query_var( 'type' ) ) {
				//echo get_query_var( 'type' );

				if (get_query_var( 'type' ) == "search-basic" || 
					get_query_var( 'type' ) == "search-result" || 
					get_query_var( 'type' ) == "search-advanced" ) {
	
					// Public Profile Search
					return rb_agency_BASEREL . 'view/profile-search.php';

				} elseif (get_query_var( 'type' ) == "profilecastingcart") {
				// Casting cart
					return rb_agency_BASEREL . 'view/profile-castingcart.php';

				} elseif (get_query_var( 'type' ) == "category") {
				// Category View
					return dirname(__FILE__) . '/view/profile-category.php';

				} elseif (get_query_var( 'type' ) == "profile") {
				// Profile View
					return rb_agency_BASEREL . 'view/profile-view.php';

				} elseif (get_query_var( 'type' ) == "profilecontact") {
				// Profile Contact Form
					return dirname(__FILE__) . '/theme/view-profile-contact.php';

				} elseif (get_query_var( 'type' ) == "dashboard") {
				// Dashboard TODO: REMOVE
					return dirname(__FILE__) . '/theme/view-dashboard.php';

				} elseif (get_query_var( 'type' ) == "print") {
				// Print Mode: TODO REFACTOR
					return dirname(__FILE__) . '/theme/view-print.php';

				} elseif (get_query_var( 'type' ) == "favorite") {
				// VIEW Favorites: TODO REFACTOR
					return dirname(__FILE__) . '/theme/view-favorite.php';

				} elseif (get_query_var( 'type' ) == "casting") {
					return dirname(__FILE__) . '/theme/view-castingcart.php';

				} elseif (get_query_var( 'type' ) == "version") {
					return dirname(__FILE__) . '/version.php'; 
				} elseif (get_query_var( 'type' ) == "getstate") {
					return rb_agency_BASEREL . 'view/partial/get-state.php'; 
				}
			}
			return $template;
		}


// *************************************************************************************************** //
/*
 *  Errors & Alerts
 */

	// Create Message Wrapper
	function rb_agency_adminmessage_former($message, $errormsg = false) {
		if ($errormsg) {
			echo '<div id="message" class="error">';
		} else {
			echo '<div id="message" class="updated fade">';
		}
		echo "<p><strong>$message</strong></p></div>";
	}

	/** 
	  * Call rb_agency_adminmessage() when showing other admin 
	  * messages. The message only gets shown in the admin
	  * area, but not on the frontend of your WordPress site. 
	  */
	add_action('admin_notices', 'rb_agency_adminmessage'); 
		function rb_agency_adminmessage() {

			// Are Permalinks Enabled?
			if ( get_option('permalink_structure') == '' ) {
				rb_agency_adminmessage_former('<a href="'. admin_url("options-permalink.php") .'">'. __("Permalinks", rb_agency_TEXTDOMAIN) .'</a> '. __("are not configured.  This will cause RB Agency not to function properly.", rb_agency_TEXTDOMAIN), true);
			}

		}


// *************************************************************************************************** //
/*
 *  General Functions
 */




	/**
     * Create the directory (if exists, create new name)
     *
     * @param string $ProfileGallery
     */
	function rb_agency_createdir($ProfileGallery, $Force_create = true){

		if (!is_dir(rb_agency_UPLOADPATH . $ProfileGallery)) {
			mkdir(rb_agency_UPLOADPATH . $ProfileGallery, 0755);
			chmod(rb_agency_UPLOADPATH . $ProfileGallery, 0777);
		} else {
			if($Force_create){
				$finished = false;
				$pos = 0;                 // we're not finished yet (we just started)
				while ( ! $finished ):                   // while not finished
					$pos++;
					$NewProfileGallery = $ProfileGallery ."-".$pos;   // output folder name
					if ( ! is_dir(rb_agency_UPLOADPATH . $NewProfileGallery) ): // if folder DOES NOT exist...
						mkdir(rb_agency_UPLOADPATH . $NewProfileGallery, 0755);
						chmod(rb_agency_UPLOADPATH . $NewProfileGallery, 0777);
						$ProfileGallery = $NewProfileGallery;  // Set it to the new  thing
						$finished = true;                    // ...we are finished
					endif;
				endwhile;
			}
		}
		return $ProfileGallery;
	}

	/**
     * Check directory, if doesnt exist, create, if exists, skip
     *
     * @param string $ProfileGallery
     */
	function rb_agency_checkdir($ProfileGallery){
		if (!is_dir(rb_agency_UPLOADPATH . $ProfileGallery)) {
			mkdir(rb_agency_UPLOADPATH . $ProfileGallery, 0755);
			chmod(rb_agency_UPLOADPATH . $ProfileGallery, 0777);
			// defensive return
			return $ProfileGallery;
		} else {
			//defensive return
			return $ProfileGallery;
		}
	}





















	/**
     * Get Profile Name
     *
     * @param id $ProfileID
     */
	function rb_agency_getprofilename($ProfileID) {
		global $rb_agency_option_profilenaming;
		
		if ($rb_agency_option_profilenaming == 0) {
			$ProfileContactDisplay = $ProfileContactNameFirst . "". $ProfileContactNameLast;
		} elseif ($rb_agency_option_profilenaming == 1) {
			$ProfileContactDisplay = $ProfileContactNameFirst . "". substr($ProfileContactNameLast, 0, 1);
		} elseif ($rb_agency_option_profilenaming == 2) {
			// It already is :)
		}
	}


	/**
     * Identify Current Langauge
     *
     */
	function rb_agency_getActiveLanguage() {
		if (function_exists('icl_get_languages')) {
			// fetches the list of languages
			$languages = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR');
			$activeLanguage = 'en';
		
			// runs through the languages of the system, finding the active language
			foreach($languages as $language) {

				// tests if the language is the active one
				if($language['active'] == 1) {
				  	$activeLanguage = $language['language_code'];
				}
				return "/". $activeLanguage;
			}
		} else {
			return "";
		}
	}

	/**
     * Generate random number
     *
     */
	function rb_agency_random() {
		return preg_replace("/([0-9])/e","chr((\\1+112))",rand(100000,999999));
	}

	/**
     * Get users role
     *
     */
	function rb_agency_get_userrole() {
		global $current_user;
		get_currentuserinfo();
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		return $user_role;
	};

	/**
     * Convert Date & time to UnixTimestamp
     *
     * @param string $datetime
     */
	function rb_agency_convertdatetime($datetime) {
		// Convert
		list($date, $time) = explode(' ', $datetime);
		list($year, $month, $day) = explode('-', $date);
		list($hours, $minutes, $seconds) = explode(':', $time);

		$UnixTimestamp = mktime($hours, $minutes, $seconds, $month, $day, $year);
		return $UnixTimestamp;
	}

	/**
     * Get Profile Name
     *
     * @param string $timestamp (unix timestamp)
     * @param string $offset  (offset from server)
     */
	function rb_agency_makeago($timestamp, $offset){
		// Ensure the Timestamp is not null
		if (isset($timestamp) && !empty($timestamp) && ($timestamp <> "0000-00-00 00:00:00") && ($timestamp <> "943920000")) {
			// Offset 
			// TODO: Remove hard coded server time
			$offset = $offset-5;

			// Offset Math
			$timezone_offset = (int)$offset;
			$time_altered = time() -  $timezone_offset *60 *60;
			$difference = $time_altered - $timestamp;

			// Prepare Text
			// TODO: Add multi lingual
			$periods = array("sec", "min", "hr", "day", "week", "month", "year", "decade");
			$lengths = array("60","60","24","7","4.35","12","10");

			// Logic
			for($j = 0; $difference >= $lengths[$j]; $j++)
			$difference /= $lengths[$j];
			$difference = round($difference);
			if($difference != 1) $periods[$j].= "s";
			$text = "$difference $periods[$j] ago";
			if ($j > 10) { exit; }

			return $text;
		} else {
			// If timestamp is blank, return non value
			return "--";
		}
	}

	/**
     * Get Profile's Age
     *
     * @param string $p_strDate
     */
	function rb_agency_get_age($p_strDate) {
	//Get Age Option if it should display with months included
		$rb_agency_options_arr = get_option('rb_agency_options');
		if (isset($rb_agency_options_arr['rb_agency_option_profilelist_bday']) && $rb_agency_options_arr['rb_agency_option_profilelist_bday'] == true) {
			
			list($Y,$m,$d) = explode("-",$p_strDate);
			$dob = "$d-$m-$Y";
			$localtime = getdate();
			$today = $localtime['mday']."-".$localtime['mon']."-".$localtime['year'];
			$dob_a = explode("-", $dob);
			$today_a = explode("-", $today);
			$dob_d = $dob_a[0];$dob_m = $dob_a[1];$dob_y = $dob_a[2];
			$today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
			$years = $today_y - $dob_y;
			$months = $today_m - $dob_m;

			if ($today_m.$today_d < $dob_m.$dob_d) {
				$years--;
				$months = 12 + $today_m - $dob_m;
			}
			if ($today_d < $dob_d) {
				$months--;
			}

			$firstMonths=array(1,3,5,7,8,10,12);
			$secondMonths=array(4,6,9,11);
			$thirdMonths=array(2);

			if($today_m - $dob_m == 1) {
				if(in_array($dob_m, $firstMonths)) 
				{
					array_push($firstMonths, 0);
				}
				elseif(in_array($dob_m, $secondMonths)) 
				{
					array_push($secondMonths, 0);
				}elseif(in_array($dob_m, $thirdMonths)) 
				{
					array_push($thirdMonths, 0);
				}
			}

			if($months >= 12){
				$months = $months - 12;
				$years++;
			}
			if($years == 0){
				$years = "";
			} else {
				$years = $years . " yr(s) ";
			}
			if($months == 0){
				$months = "";
			} else {
				$months = $months . " mo(s) ";
			}

			return  $years . $months;

		// Or just do it the old way
		} else {
			list($Y,$m,$d) = explode("-",$p_strDate);
			return( date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y );
		}

	}





	/**
     * Get Current User ID
     *
     */
	function rb_agency_get_current_userid(){
		global $current_user;
		get_currentuserinfo();
		return $current_user->ID;
	}

	/**
     * Get Filename Extension
     *
     * @param string $filename
     */
	function rb_agency_filenameextension($filename) {
		$pos = strrpos($filename, '.');
		if($pos===false) {
			return false;
		} else {
			return substr($filename, $pos+1);
		}
	}



	/**
     * Generate Video Thumbnail
     *
     * @param string $videoID
     */
	function rb_agency_get_videothumbnail($videoID) {
		$videoID = ltrim($videoID);
		if (substr($videoID, 0, 23) == "http://www.youtube.com/") {
			$videoID = rb_agency_get_VideoID($videoID);
		} elseif (substr($videoID, 0, 7) == "<object") {
			$videoID = rb_agency_get_VideoFromObject($videoID);
		}
		$rb_agency_get_videothumbnail = "<img src='http://img.youtube.com/vi/" . $videoID . "/default.jpg' />";
		return $rb_agency_get_videothumbnail;
	}

	/**
     * Strip out VideoID from URL
     *
     * @param string $videoURL
     */
	function rb_agency_get_VideoID($videoURL) {
		if (substr($videoURL, 0, 23) == "http://www.youtube.com/") {
			$videoURL = str_replace("http://www.youtube.com/v/", "", $videoURL);
			$videoURL = str_replace("http://www.youtube.com/watch?v=", "", $videoURL);
			$videoURL = str_replace("&feature=search", "", $videoURL);
			$videoURL = str_replace("?fs=1&amp;hl=en_US", "", $videoURL);
			$videoID = $videoURL; // substr($videoURL, 25, 15);
		} else {
			$videoID = $videoURL;
		}
		return $videoID;
	}

	/**
     * Create embed code from URL
     *
     * @param string $videoObject
     */
	function rb_agency_get_VideoFromObject($videoObject) {
		if (substr(strtolower($videoObject), 0, 7) == "<object") {
			$videoObject = strip_tags($videoObject, '<embed>');
			//$videoObject = str_replace('<embed src="', '', $videoObject);
			$videoObject = substr($videoObject, 13);
			$videoObject = str_replace("http://www.youtube.com/v/", "", $videoObject);
			$videoObject_newend = strpos($videoObject, '?');
			$videoObject = substr($videoObject, 0, $videoObject_newend);
		} else {
			$videoObject = rb_agency_get_VideoID($videoObject);
		}
		return $videoObject;
	}



	/**
     * Generate Folder Name
     *
     * @param $ID - record id, $first = first name, $last - last name, $display - contact display
	 * @return - formatted folder name 
     */
	function generate_foldername($ID = NULL, $first, $last, $display){

		$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_profilenaming  = (int)$rb_agency_options_arr['rb_agency_option_profilenaming'];
		if ($rb_agency_option_profilenaming == 0) {
				$ProfileGalleryFixed = $first . "-". $last;
		} elseif ($rb_agency_option_profilenaming == 1) {
				$ProfileGalleryFixed = $first . "-". substr($last, 0, 1);
		} elseif ($rb_agency_option_profilenaming == 2) {
				$ProfileGalleryFixed = $display;
		} elseif ($rb_agency_option_profilenaming == 3) {
				$ProfileGalleryFixed = "ID".$ID;
		} elseif ($rb_agency_option_profilenaming == 4) {
				$ProfileGalleryFixed = $first;
		} elseif ($rb_agency_option_profilenaming == 5) {
				$ProfileGalleryFixed = $last;
		}

		return RBAgency_Common::format_stripchars($ProfileGalleryFixed); 
	}

	/**
     * List Categories
     *
     * @param array $atts 
     */
	function rb_agency_categorylist($atts, $content = NULL) {
		/*
		EXAMPLE USAGE: 

		if (function_exists('rb_agency_categorylist')) { 
			$atts = array('profilesearch_layout' => 'advanced');
			rb_agency_categorylist($atts); }

		*/

		// Set It Up	
		global $wp_rewrite;
		extract(shortcode_atts(array(
			"profilesearch_layout" => "advanced"
		), $atts));

		// Query
		$queryList = "SELECT dt.DataTypeID, dt.DataTypeTitle, dt.DataTypeTag, COUNT(profile.ProfileID) AS CategoryCount FROM ".table_agency_data_type." dt,".table_agency_profile." profile where dt.DataTypeID= profile.ProfileType and profile.ProfileIsActive = 1 group by dt.DataTypeID ORDER BY dt.DataTypeTitle ASC";
		$resultsList = mysql_query($queryList);
		$countList = mysql_num_rows($resultsList);			

		// Loop through Results
		while ($dataList = mysql_fetch_array($resultsList)) {
			echo "<div class=\"profile-category\">\n";
			if ($DataTypeID == $dataList["DataTypeID"]) {
				echo "  <div class=\"name\"><strong>". $dataList["DataTypeTitle"] ."</strong> <span class=\"count\">(". $dataList["CategoryCount"] .")</span></div>\n";
			} else {
				echo "  <div class=\"name\"><a href=\"/profile-category/". $dataList["DataTypeTag"] ."/\">". $dataList["DataTypeTitle"] ."</a> <span class=\"count\">(". $dataList["CategoryCount"] .")</span></div>\n";
			}
			echo "</div>\n";
		}
		if ($countList < 1) {
			echo __("No Categories Found", rb_agency_TEXTDOMAIN);
		}
	}


	/**
     * List Profiles
     *
     * @param array $atts 
     */
	function rb_agency_profilelist($atts, $content = NULL) {

			// Print or Export

			// Commented by Sunil to fix profile print/pdf issue
		//	if(get_query_var('target')!="print" AND get_query_var('target')!="pdf"){
				// Commented by Sunil to fix profile display issue
				//if (isset($profilecastingcart)){   //to tell prrint and pdf generators its for casting cart and new link
					// Get Preferences
		$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_privacy					 = isset($rb_agency_options_arr['rb_agency_option_privacy']) ? $rb_agency_options_arr['rb_agency_option_privacy'] :0;
		$rb_agency_option_profilelist_count			 = isset($rb_agency_options_arr['rb_agency_option_profilelist_count']) ? $rb_agency_options_arr['rb_agency_option_profilelist_count']:0;
		$rb_agency_option_profilelist_perpage		 = isset($rb_agency_options_arr['rb_agency_option_profilelist_perpage']) ?$rb_agency_options_arr['rb_agency_option_profilelist_perpage']:0;
		$rb_agency_option_profilelist_sortby		 = isset($rb_agency_options_arr['rb_agency_option_profilelist_sortby']) ?$rb_agency_options_arr['rb_agency_option_profilelist_sortby']:0;
		$rb_agency_option_layoutprofilelist		 	 = isset($rb_agency_options_arr['rb_agency_option_layoutprofilelist']) ? $rb_agency_options_arr['rb_agency_option_layoutprofilelist']:0;
		$rb_agency_option_profilelist_expanddetails	 = isset($rb_agency_options_arr['rb_agency_option_profilelist_expanddetails']) ? $rb_agency_options_arr['rb_agency_option_profilelist_expanddetails']:0;
		$rb_agency_option_locationtimezone 			 = isset($rb_agency_options_arr['rb_agency_option_locationtimezone']) ? (int)$rb_agency_options_arr['rb_agency_option_locationtimezone']:0;
		$rb_agency_option_profilelist_favorite		 = isset($rb_agency_options_arr['rb_agency_option_profilelist_favorite']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_favorite']:0;
		$rb_agency_option_profilenaming				 = isset($rb_agency_options_arr['rb_agency_option_profilenaming']) ?$rb_agency_options_arr['rb_agency_option_profilenaming']:0;
		$rb_agency_option_profilelist_castingcart 	 = isset($rb_agency_options_arr['rb_agency_option_profilelist_castingcart']) ?(int)$rb_agency_options_arr['rb_agency_option_profilelist_castingcart']:0;
		$rb_agency_option_profilelist_printpdf 	     = isset($rb_agency_options_arr['rb_agency_option_profilelist_printpdf']) ?(int)$rb_agency_options_arr['rb_agency_option_profilelist_printpdf']:0;

		// Set It Up	
		global $wp_rewrite;
		$cusFields = array("Suit","Bust","Shirt","Dress");  //for custom fields min and max

		// Exctract from Shortcode
		extract(shortcode_atts(array(
			"profileid" => NULL,
			"profilecontactnamefirst" => NULL,
			"profilecontactnamelast" => NULL,
			"profilelocationcity" => NULL,
			"profiletype" => NULL,
			"type" => NULL,
			"profileisactive" => NULL,
			"profilegender" => NULL,
			"gender" => NULL,
			"profilestatheight_min" => NULL,
			"profilestatheight_max" => NULL,
			"profilestatweight_min" => NULL,
			"profilestatweight_max" => NULL,
			"profiledatebirth_min" => NULL,
			"age_start" => NULL,
			"profiledatebirth_max" => NULL,
			"age_stop" => NULL,
			"featured" => NULL,
			"stars" => NULL,
			"paging" => NULL,
			"pagingperpage" => NULL,
			"override_privacy" => NULL,
			"profilefavorite" => NULL,
			"profilecastingcart" => NULL,
			"getprofile_saved" => NULL,
			"profilecity" => NULL,
			"profilestate" => NULL,
			"profilezip" => NULL
		), $atts));

		// Filter It
		$sort = "profile.ProfileContactDisplay";

		//$limit = " LIMIT 0,". $rb_agency_option_profilelist_perpage;
		$dir = "asc";
		// Should we override the privacy settings?
		if(strpos($pageURL,'client-view') > 0 && (get_query_var('type') == "profilesecure")){
			$OverridePrivacy = 1;
		}

		// Option to show all profiles
		if (isset($OverridePrivacy)) {
			// If sent link, show both hidden and visible
			$filter = "WHERE profile.ProfileIsActive IN (1, 4)";
		} else {
			$filter = "WHERE profile.ProfileIsActive = 1";
		}

		// Pagination
		if (!isset($paging) || empty($paging)) {
			$paging = 1; 
			if (get_query_var('paging')) {
				$paging = get_query_var('paging'); 
			} else { 
				preg_match('/[0-9]/', $_SERVER["REQUEST_URI"], $matches, PREG_OFFSET_CAPTURE);
				if ($matches[0][1] > 0) {
					$paging = str_replace("/", "", substr($_SERVER["REQUEST_URI"], $matches[0][1]));
				} else {
					$paging = 1; 
				}
			}
		}
		if (!isset($pagingperpage) || empty($pagingperpage)) { $pagingperpage = $rb_agency_option_profilelist_perpage; }
		if($pagingperpage=="0"){$pagingperpage="10";}//make it a default value

		// Legacy Field Names
		if (isset($type) && !empty($type)){ $profiletype = $type; }
		if (isset($gender) && !empty($gender)){  $profilegender = $gender; }
		if (isset($age_start) && !empty($age_start)){ $profiledatebirth_min = $age_start; }
		if (isset($age_stop) && !empty($age_stop)){ $profiledatebirth_max = $age_stop; }
		$ProfileID 					= $profileid;
		$ProfileContactNameFirst	= $profilecontactnamefirst;
		$ProfileContactNameLast    	= $profilecontactnamelast;
		$ProfileLocationCity		= $profilelocationcity;
		$ProfileType				= $profiletype;
		$ProfileIsActive			= $profileisactive;
		$ProfileGender    			= $profilegender;
		$ProfileStatHeight_min		= $profilestatheight_min;
		$ProfileStatHeight_max		= $profilestatheight_max;
		$ProfileStatWeight_min		= $profilestatheight_min;
		$ProfileStatWeight_max		= $profilestatheight_max;
		$ProfileDateBirth_min		= $profiledatebirth_min;
		$ProfileDateBirth_max		= $profiledatebirth_max;
		$ProfileIsFeatured			= $featured;
		$ProfileIsPromoted			= $stars;
		$OverridePrivacy			= $override_privacy;
		$GetProfileSaved			= $getprofile_saved;
		$City						= $profilecity;
		$State						= $profilestate;
		$Zip						= $profilezip;  

		// Name
		if (isset($ProfileContactNameFirst) && !empty($ProfileContactNameFirst)){
			$ProfileContactNameFirst = $ProfileContactNameFirst;
			$filter .= " AND profile.ProfileContactNameFirst LIKE '". $ProfileContactNameFirst ."%'";
		}
		if (isset($ProfileContactNameLast) && !empty($ProfileContactNameLast)){
			$ProfileContactNameLast = $ProfileContactNameLast;
			$filter .= " AND profile.ProfileContactNameLast LIKE '". $ProfileContactNameLast ."%'";
		}

		// Type
		if (isset($ProfileType) && !empty($ProfileType)){
			$ProfileType = $ProfileType;
			$filter .= " AND FIND_IN_SET(". $ProfileType .", profile.ProfileType) ";
		} else {
			$ProfileType = "";
		}

		// Profile Search Saved 
		if(isset($GetProfileSaved) && !empty($GetProfileSaved)){
			$filter .= " AND profile.ProfileID IN(".$GetProfileSaved.") ";
		}

		// Gender
		if (isset($ProfileGender) && !empty($ProfileGender)){
			$filter .= " AND profile.ProfileGender='".$ProfileGender."'";
		} else {
			$ProfileGender = "";
		}

		// Age
		$date = gmdate('Y-m-d', time() + $rb_agency_option_locationtimezone *60 *60);
		if (isset($ProfileDateBirth_min) && !empty($ProfileDateBirth_min)){
			$selectedYearMin = date('Y-m-d', strtotime('-'. $ProfileDateBirth_min .' year'. $date));
			$filter .= " AND profile.ProfileDateBirth <= '$selectedYearMin'";
		}
		if (isset($ProfileDateBirth_max) && !empty($ProfileDateBirth_max)){
			$selectedYearMax = date('Y-m-d', strtotime('-'. $ProfileDateBirth_max - 1 .' year'. $date));
			$filter .= " AND profile.ProfileDateBirth >= '$selectedYearMax'";
		}
		if (isset($ProfileIsFeatured)){
			$filter .= " AND profile.ProfileIsFeatured = '1' ";
		}
		if (isset($ProfileIsPromoted)){
			$filter .= " AND profile.ProfileIsPromoted = '1' ";
		}

		// City
		if (isset($City) && !empty($City)){
			$City = $City;
			$filter .= " AND profile.ProfileLocationCity = '". ucfirst($City) ."'";
		}

		// State
		if (isset($State) && !empty($State)){
			$State = $State;
			$filter .= " AND profile.ProfileLocationState = '". ucfirst($State) ."'";
		}

		// Zip
		if (isset($Zip) && !empty($Zip)){
			$Zip = $Zip;
			$filter .= " AND profile.ProfileLocationZip = '". ucfirst($Zip) ."'";
		}
		// Can we show the profiles?
		// P R I V A C Y FILTER ====================================================
		if ( (isset($OverridePrivacy)) || 
			//Must be logged to view model list and profile information
			($rb_agency_option_privacy == 2 && is_user_logged_in()) || 
			// Model list public. Must be logged to view profile information
			($rb_agency_option_privacy == 1 && is_user_logged_in()) ||
			// All Public
			($rb_agency_option_privacy == 0) ||
			//admin users
			(is_user_logged_in() && current_user_can( 'manage_options' )) ||
			//  Must be logged as "Client" to view model list and profile information
			($rb_agency_option_privacy == 3 && is_user_logged_in() && is_client_profiletype()) )
		{

		$atts["type"]="casting";
					$addtionalLink='&nbsp;|&nbsp;<a id="sendemail" href="javascript:">Email to Admin</a>';
				}
				
				// print, downloads links to be added on top of profile list
				$links='<div class="rblinks">';

				/*
				 * Set Print / PDF in Settings
				 */
					if(get_query_var('target')!="results" && $rb_agency_option_profilelist_printpdf){// hide print and download PDF in Search result
						$links.='
						<div class="rbprint-download">
							<a target="_blank" href="'.get_bloginfo('wpurl').'/profile-category/print/?gd='.$atts["gender"].'&ast='.$atts["age_start"].'&asp='.$atts["age_stop"].'&t='.$atts["type"].'">Print</a></a>&nbsp;|&nbsp;<a target="_blank" href="'.get_bloginfo('wpurl').'/profile-category/pdf/?gd='.$atts["gender"].'&ast='.$atts["age_start"].'&asp='.$atts["age_stop"].'&t='.$atts["type"].'">Download PDF</a>'.$addtionalLink.'
						</div><!-- .rbprint-download -->';
					}

					$links.='<div class="rbfavorites-castings">';

					if(is_permitted("favorite") && (!rb_is_page("rb_casting") && !rb_is_page("rb_favorites")) ){
						if($rb_agency_options_arr['rb_agency_option_profilelist_favorite']==1){
								$links.='<a href="'.get_bloginfo('siteurl').'/profile-favorite/">'.__("View Favorites", rb_agency_TEXTDOMAIN).'</a>';
						}
					}

					if(is_permitted("casting") && (!rb_is_page("rb_casting") && !rb_is_page("rb_favorites"))){
						if($_SERVER['REQUEST_URI']!="/profile-casting/"){
								if($rb_agency_options_arr['rb_agency_option_profilelist_castingcart']==1){
									if($rb_agency_options_arr['rb_agency_option_profilelist_favorite']==1){$links.='&nbsp;|&nbsp;';}
									$links.='<a href="'.get_bloginfo('siteurl').'/profile-casting/">'.__("Casting Cart", rb_agency_TEXTDOMAIN).'</a>';
								}
						}
					}
					$links.='</div><!-- .rbfavorites-castings -->
				</div><!-- .rblinks -->';			
			//}
			/*
			 *  sorting options is activated if set on in admin/settings
			 */
			if($rb_agency_option_profilelist_sortby){

				// Enqueue our js script
				wp_enqueue_script( 'list_reorder', plugins_url('rb-agency/js/list_reorder.js'),array('jquery'));

				// Dropdown
				$links.='<div id="rbfilter-sort">';
				$links.='<div class="rbsort">
						<label>Sort By: </label>
						<select id="sort_by">
							<option value="">Sort List</option>
							<option value="1">Age</option>
							<option value="2">Name</option>
							<option value="3">Date Joined</option>
						</select>
						<select id="sort_option">
							<option value="">Sort Options</option>
						</select>
						</div>';
				$links.='</div>';
			}

			//remove  if its just for client view of listing via casting email
			if(get_query_var('type')=="profilesecure"){ $links="";}

			if(get_query_var('type')=="favorite"){ $links="";} // we dont need print and download pdf in favorites page
			
			echo "<div class=\"rbclear\"></div>\n";
			echo "$links<div id=\"profile-results\">\n";
			
			if(get_query_var('target')!="print" AND get_query_var('target')!="pdf"){ //if its printing or PDF no need for pagination belo

				/*********** Paginate **************/
					$qItem = mysql_query("SELECT
					profile.ProfileGallery, profile.ProfileContactDisplay, profile.ProfileDateBirth, profile.ProfileLocationState, profile.ProfileID as pID , 
					customfield_mux.*,  
					(SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media  WHERE  profile.ProfileID = media.ProfileID  AND media.ProfileMediaType = \"Image\"  AND media.ProfileMediaPrimary = 1) 
					AS ProfileMediaURL 
					FROM ". table_agency_profile ." profile 
				 	LEFT JOIN ". table_agency_customfield_mux ."  AS customfield_mux 
					ON profile.ProfileID = customfield_mux.ProfileID  
					$filter  GROUP BY profile.ProfileID ORDER BY $sort $dir  ".(isset($limit) ? $limit : "")."");
					$items = mysql_num_rows($qItem); // number of total rows in the database
				  
				if($items > 0) {
					$p = new rb_agency_pagination;
					$p->items($items);
					$p->limit($pagingperpage); // Limit entries per page
					$p->target($_SERVER["REQUEST_URI"]);
					$p->currentPage($paging); // Gets and validates the current page
					$p->calculate(); // Calculates what to show
					$p->parameterName('paging');
					$p->adjacents(1); //No. of page away from the current page
					
					if(!isset($paging)) {
						$p->page = 1;
					} else {
						$p->page = $paging;
					}
					//Query for limit paging
					$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
				} else {
					$limit = "";
				}
				if(get_query_var('target')=="print"){$limit = "";} //to remove limit on print page
				if(get_query_var('target')=="pdf"){$limit = "";} //to remove limit on pdf page
				mysql_free_result($qItem);

			}//if(get_query_var('target')!="print" 

			/*
			 * check permissions
			 */
			$sqlFavorite_userID='';
			$sqlCasting_userID='';
			if(is_permitted('casting')){
				// Casting Cart 
				$sqlCasting_userID = " cart.CastingCartTalentID = profile.ProfileID   AND cart.CastingCartProfileID = '".rb_agency_get_current_userid()."'  ";
			} 
			if(is_permitted('favorite')){
				// Display Favorites 
				$sqlFavorite_userID  = " fav.SavedFavoriteTalentID = profile.ProfileID  AND fav.SavedFavoriteProfileID = '".rb_agency_get_current_userid()."' ";
			} 

			/*
			 * Execute the Query
			 */
			if (isset($profilefavorite) && !empty($profilefavorite)){
				// Execute query showing favorites
				$queryList = "SELECT profile.ProfileID, profile.ProfileGallery, profile.ProfileContactDisplay, profile.ProfileDateBirth, profile.ProfileLocationState, profile.ProfileID as pID, fav.SavedFavoriteTalentID, fav.SavedFavoriteProfileID, (SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL FROM ". table_agency_profile ." profile INNER JOIN  ".table_agency_savedfavorite." fav WHERE $sqlFavorite_userID AND profile.ProfileIsActive = 1 GROUP BY fav.SavedFavoriteTalentID";
			} elseif (isset($profilecastingcart) && !empty($profilecastingcart)){
				// There is a Casting Cart ID present

				// Get User ID
				$user = get_userdata(rb_agency_get_current_userid());  

				// check if user is admin, if yes this allow the admin to view other users cart 
				if($user->user_level==10 AND get_query_var('target')!="casting") {
					$sqlCasting_userID = " cart.CastingCartTalentID = profile.ProfileID AND cart.CastingCartProfileID = '".get_query_var('target')."' ";
				}

				// Execute the query showing casting cart
				$queryList = "SELECT profile.ProfileID, profile.ProfileGallery, profile.ProfileContactDisplay, profile.ProfileDateBirth, profile.ProfileLocationState, profile.ProfileID as pID, cart.CastingCartTalentID, cart.CastingCartTalentID, (SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL FROM ". table_agency_profile ." profile INNER JOIN  ".table_agency_castingcart." cart WHERE $sqlCasting_userID AND ProfileIsActive = 1 GROUP BY profile.ProfileID";  

			} elseif ($_GET['t']=="casting"){
				// TODO: Find ?????????????????????  Purpose?
				$queryList = "SELECT profile.ProfileID, profile.ProfileGallery, profile.ProfileContactDisplay, profile.ProfileDateBirth, profile.ProfileLocationState, profile.ProfileID as pID, cart.CastingCartTalentID, cart.CastingCartTalentID, (SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL FROM ". table_agency_profile ." profile INNER JOIN  ".table_agency_castingcart." cart WHERE  $sqlCasting_userID AND ProfileIsActive = 1 GROUP BY profile.ProfileID";  
			} elseif ($fastload){
				// Execute Query in slim down mode, only return name, face and link
				$queryList = "
					SELECT 
						profile.ProfileID,
						profile.ProfileID as pID, 
						profile.ProfileGallery,
						profile.ProfileContactDisplay, 
						(   SELECT media.ProfileMediaURL 
							FROM ". table_agency_profile_media ." media 
							WHERE profile.ProfileID = media.ProfileID 
								AND media.ProfileMediaType = \"Image\" 
								AND media.ProfileMediaPrimary = 1
						) 
						AS ProfileMediaURL 
					FROM ". table_agency_profile ." profile 
						$filter  
					GROUP BY profile.ProfileID 
					ORDER BY $sort $dir $limit";

			} else {
				// Execute Query   removed profile.*,
				$queryList = "
				SELECT 
					profile.ProfileID,
					profile.ProfileID as pID, 
					profile.ProfileGallery,
					profile.ProfileContactDisplay, 
					profile.ProfileDateBirth, 
					profile.ProfileDateCreated,
					profile.ProfileLocationState
				FROM ". table_agency_profile ." profile 
				$filter  
				GROUP BY profile.ProfileID 
				ORDER BY $sort $dir $limit";
			}
			// Query
			/*echo "queryList".$queryList;
			echo "<br /><br />";
			echo "sort".$sort;
			echo "dir".$dir;
			echo "limit".$limit;*/
			$resultsList = mysql_query($queryList);
			$countList = mysql_num_rows($resultsList);

			$rb_user_isLogged = is_user_logged_in();

			// Get Naming Convention
			$rb_agency_option_profilenaming = isset($rb_agency_options_arr['rb_agency_option_profilenaming']) ?$rb_agency_options_arr['rb_agency_option_profilenaming']:0;

			if($countList > 0){

			# this will replace the timthumb function as it is not working properly all the time.	
			$displayHTML ="	<script type='text/javascript' src='".rb_agency_BASEDIR."js/resize.js'></script>";

			$profileDisplay = 0;
			$countFav = 0;
			while ($dataList = mysql_fetch_assoc($resultsList)) {
					
				$profileDisplay++;
				if ($profileDisplay == 1 ){

					/*********** Show Count/Pages **************/
					 $displayHTML .= "  <div id=\"profile-results-info\">\n";
						
						# Temporarily removed this as required
						#if(count($dataList) > 0){
						#	$displayHTML .="    <div class=\"profile-results-info-countpage\">\n";
						#		echo "<strong>Item on this list: ".count($countList)."</strong>";
						#	$displayHTML .="    </div>\n";
						#}

						if($items > 0) {
							if ((!isset($profilefavorite) && empty($profilefavorite)) && (!isset($profilecastingcart) && empty($profilecastingcart))){ 
								$displayHTML .="    <div class=\"profile-results-info-countpage\">\n";
									echo $p->show();  // Echo out the list of paging. 
								$displayHTML .= "    </div>\n";
							}
						}

						if ($rb_agency_option_profilelist_count) {
							if ((!isset($profilefavorite) && empty($profilefavorite)) && (!isset($profilecastingcart) && empty($profilecastingcart))){  
								$displayHTML .= "    <div id=\"profile-results-info-countrecord\">\n";
								$displayHTML .="    	". __("Displaying", rb_agency_TEXTDOMAIN) ." <strong>". $countList ."</strong> ". __("of", rb_agency_TEXTDOMAIN) ." ". $items ." ". __(" records", rb_agency_TEXTDOMAIN) ."\n";
								$displayHTML .="    </div>\n";
							}
						}

					$displayHTML.="  </div><!-- #profile-results-info -->\n";
					$displayHTML.="  <div class=\"rbclear\"></div>\n";
				}
				
				if($profileDisplay == 1){
					$displayHTML.="  <div id=\"profile-list\">\n";
				}
				$displayHTML .= "<div id=\"rbprofile-".$dataList["ProfileID"]."\" class=\"rbprofile-list profile-list-layout0\" >\n";
				
				$p_image = rb_get_primary_image($dataList["ProfileID"]); 

				/*
				 * load sorting values
				 */
				$displayHTML .= '<input id="br'.$dataList["ProfileID"].'" type="hidden" class="p_birth" value="'.$dataList["ProfileDateBirth"].'">';
				$displayHTML .= '<input id="nm'.$dataList["ProfileID"].'" type="hidden" class="p_name" value="'.$dataList["ProfileContactDisplay"].'">';
				$displayHTML .= '<input id="cr'.$dataList["ProfileID"].'" type="hidden" class="p_created" value="'.$dataList["ProfileDateCreated"].'">';

				if ($p_image){ 
					
					#dont need other image for hover if its for print or pdf download view and dont use timthubm
					if(get_query_var('target')!="print" AND get_query_var('target')!="pdf"){

						if($rb_agency_options_arr['rb_agency_option_profilelist_thumbsslide']==1){  //show profile sub thumbs for thumb slide on hover
							$images=getAllImages($dataList["ProfileID"]);
							$images=str_replace("{PHOTO_PATH}",rb_agency_UPLOADDIR ."". $dataList["ProfileGallery"]."/",$images);
						}
						$displayHTML .="<div  class=\"image\">"."<a href=\"". rb_agency_PROFILEDIR ."". $dataList["ProfileGallery"] ."/\" style=\"background-image: url(".rb_agency_UPLOADDIR ."". $dataList["ProfileGallery"] ."/". $p_image.")\"></a>".$images."</div>\n";
					} else {
						$displayHTML .="<div  class=\"image\">"."<a href=\"". rb_agency_PROFILEDIR ."". $dataList["ProfileGallery"] ."/\" style=\"background-image: url(".rb_agency_UPLOADDIR ."". $dataList["ProfileGallery"] ."/". $p_image.")\"></a>".$images."</div>\n";
					}

				} else {
				 	$displayHTML .= "  <div class=\"image image-broken\" style='background:lightgray; color:white; font-size:20px; text-align:center; line-height:120px; vertical-align:bottom'>No Image</div>\n";
				}

				$displayHTML .= "  <div class=\"profile-info\">\n";
				$displayHTML .= "     <h3 class=\"name\"><a href=\"". rb_agency_PROFILEDIR ."". $dataList["ProfileGallery"] ."/\" class=\"scroll\">". stripslashes($dataList["ProfileContactDisplay"]) ."</a></h3>\n";

				if ($rb_agency_option_profilelist_expanddetails) {
				 	$displayHTML .= "     <div class=\"details\"><span class=\"details-age\">". rb_agency_get_age($dataList["ProfileDateBirth"]) ."</span>";
					if($dataList["ProfileLocationState"]!=""){
						$displayHTML .= "<span class=\"divider\">, </span><span class=\"details-state\">". $dataList["ProfileLocationState"] ."</span>";
					} 
					$displayHTML .= "</div>\n";
				}
				
				//echo "loaded: ".microtime()." ms";
				if($rb_user_isLogged ){
					//Get Favorite & Casting Cart links
					$displayHTML .= rb_agency_get_miscellaneousLinks($dataList["ProfileID"]);
				}

				$displayHTML .=" </div> <!-- .profile-info --> \n";
				$displayHTML .=" </div><!-- .rbprofile-list -->\n";
			} // endwhile datalist

			$displayHTML .= "  <div class=\"rbclear\"></div>\n";
			$displayHTML .= "  </div><!-- #profile-list -->\n";		
		} // endif countlist

		// There are no profiles returned.  Display empty message
		if ($countList < 1) {
			$displayHTML .= __("No Profiles Found", rb_agency_TEXTDOMAIN);
		}
		
		// Close Formatting
		$displayHTML .= "  <div class=\"rbclear\"></div>\n";
		$displayHTML .= "</div><!-- #profile-results -->\n";
		
		// Commented by Sunil to fix profile display issue
//		} else {
//			if($rb_agency_option_privacy == 3 && is_user_logged_in() && !is_client_profiletype()){
//				echo "<h2>This is a restricted page. For Clients only.</h2>";
//			} else {
//				// Show Login Form
//				include("theme/include-login.php");
//			}
//		}

		echo  $displayHTML;

		// debug mode
		//  rb_agency_checkExecution();
		//add the thumbs slides on hover of profile listing
		echo "<script type=\"text/javascript\" src=\"". rb_agency_BASEDIR ."js/thumbslide.js\"></script>\n"; 
		echo "<script type=\"text/javascript\" src=\"". rb_agency_BASEDIR ."js/textscroller.js\"></script>\n"; 
		echo "<script type=\"text/javascript\" src=\"". rb_agency_BASEDIR ."js/image-resize.js\"></script>\n";

		//load javascript for add to casting cart	
		if(get_query_var('target')!="print" AND get_query_var('target')!="pdf" AND get_query_var('type')!="profilesecure" AND !isset($profilecastingcart)){
			echo'	<script>
						function addtoCart(pid){
							var qString = \'usage=addtocart&pid=\' +pid;
							var apid = "addtocart"+pid;
							$.post(\''.rb_agency_BASEDIR.'theme/sub_db_handler.php\', qString, processResponseAddtoCart);
							//document.getElementById(pid).style.display="none";
							// document.getElementById(apid).style.backgroundPosition="0 -134px;";
							}
						function processResponseAddtoCart(data) {			
						}
					</script>';
		} //end if(get_query_var
	}

	//get profile images
	function getAllImages($profileID){
		$queryImg = "SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $profileID ."\" AND ProfileMediaType = \"Image\" ORDER BY ProfileMediaPrimary DESC LIMIT 0, 7 ";
		$resultsImg = mysql_query($queryImg);
		$countImg = mysql_num_rows($resultsImg);
		while ($dataImg = mysql_fetch_array($resultsImg)) {//style=\"display:none\" 
		 	$images.="<img  class=\"roll\" src=\"".rb_agency_BASEDIR."/tasks/timthumb.php?src={PHOTO_PATH}". $dataImg['ProfileMediaURL'] ."&w=200&q=30\" alt='' style='width:148px'   />\n";
		}
	return $images;
	}

	// Profile List
	function rb_agency_profilefeatured($atts, $content = NULL) {

		/*
		if (function_exists('rb_agency_profilefeatured')) { 
			$atts = array('count' => 8, 'type' => 0);
			rb_agency_profilefeatured($atts); }
		*/

		// Set It Up	
		global $wp_rewrite;
		extract(shortcode_atts(array(
				"type" => 0,
				"count" => 1
		), $atts));
		if ($type == 1) { // Featured
			$sqlWhere = " AND profile.ProfileIsPromoted=1";
		}
		echo "<div id=\"profile-featured\">\n";

		/*
		 * Execute Query
		 */
			$queryList = "SELECT profile.*,

			(SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media 
			 WHERE profile.ProfileID = media.ProfileID 
			 AND media.ProfileMediaType = \"Image\" 
			 AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL 
			 FROM ". table_agency_profile ." profile 
	 		 WHERE profile.ProfileIsActive = 1 ".(isset($sql) ? $sql : "") ."
			 AND profile.ProfileIsFeatured = 1  
			 ORDER BY RAND() LIMIT 0,$count";

		$rb_agency_options_arr = get_option('rb_agency_options');
		$resultsList = mysql_query($queryList);
		$countList = mysql_num_rows($resultsList);
		while ($dataList = mysql_fetch_array($resultsList)) {
			echo "<div class=\"rbprofile-list\">\n";
			if (isset($dataList["ProfileMediaURL"]) ) { 
			echo "  <div class=\"image\"><a href=\"". rb_agency_PROFILEDIR ."". $dataList["ProfileGallery"] ."/\"><img src=\"". rb_agency_UPLOADDIR ."". $dataList["ProfileGallery"] ."/". $dataList["ProfileMediaURL"] ."\" /></a></div>\n";
			} else {
			echo "  <div class=\"image image-broken\"><a href=\"". rb_agency_PROFILEDIR ."". $dataList["ProfileGallery"] ."/\">No Image</a></div>\n";
			}
			echo "<div class=\"profile-info\">";
					$rb_agency_option_profilenaming = isset($rb_agency_options_arr['rb_agency_option_profilenaming']) ?$rb_agency_options_arr['rb_agency_option_profilenaming']:0;
					if ($rb_agency_option_profilenaming == 0) {
						$ProfileContactDisplay = $dataList["ProfileContactNameFirst"] . " ". $dataList["ProfileContactNameLast"];
					} elseif ($rb_agency_option_profilenaming == 1) {
						$ProfileContactDisplay = $dataList["ProfileContactNameFirst"] . " ". substr($dataList["ProfileContactNameLast"], 0, 1);
					} elseif ($rb_agency_option_profilenaming == 2) {
						$ProfileContactDisplay = $dataList["ProfileContactNameFirst"];
					} elseif ($rb_agency_option_profilenaming == 3) {
						$ProfileContactDisplay = "ID ". $ProfileID;
					} elseif ($rb_agency_option_profilenaming == 4) {
						$ProfileContactDisplay = $ProfileContactNameFirst;
					} elseif ($rb_agency_option_profilenaming == 5) {
						$ProfileContactDisplay = $ProfileContactNameLast;
					}
			echo "     <h3 class=\"name\"><a href=\"". rb_agency_PROFILEDIR ."". $dataList["ProfileGallery"] ."/\">". $ProfileContactDisplay ."</a></h3>\n";
			if (isset($rb_agency_option_profilelist_expanddetails)) {
				echo "<div class=\"details\"><span class=\"details-age\">". rb_agency_get_age($dataList["ProfileDateBirth"]) ."</span>";
				if($dataList["ProfileLocationState"]!=""){
					echo "<span class=\"divider\">, </span><span class=\"details-state\">". $dataList["ProfileLocationState"] ."</span>";
				} 
				echo "</div>\n";
			}

			if(is_user_logged_in()){
				// Add Favorite and Casting Cart links		
				rb_agency_get_miscellaneousLinks($dataList["ProfileID"]);
			}
			echo "  </div><!-- .profile-info -->\n";
			echo "</div><!-- .rbprofile-list -->\n";
		}
		if ($countList < 1) {
			echo __("No Featured Profiles", rb_agency_TEXTDOMAIN);
		}
		echo "  <div style=\"clear: both; \"></div>\n";
		echo "</div><!-- #profile-featured -->\n";
	}

	// Profile Search
	function rb_agency_profilesearch($atts, $content = NULL){

		// Profile Class
		include_once(rb_agency_BASEREL ."app/profile.class.php");
		
		$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_privacy  = $rb_agency_options_arr['rb_agency_option_privacy'];
		
		// Set It Up	
		global $wp_rewrite;
		extract(shortcode_atts(array(
				"profilesearch_layout" => "advanced"
		), $atts));
		
		if ( ($rb_agency_option_privacy > 1 && is_user_logged_in()) || ($rb_agency_option_privacy < 2) ) {
		 	$isSearchPage = 1;
				if(!isset($_POST['form_mode'])){
					echo RBAgency_Profile::search_form("", "", 0,$profilesearch_layout);
				} else {
					if ( (isset($_POST['form_mode']) && $_POST['form_mode'] == "full" ) ){
							echo "					<input type=\"button\" name=\"back_search\" value=\"". __("Go Back to Advanced Search", rb_agency_TEXTDOMAIN) . "\" class=\"button-primary\" onclick=\"javasctipt:window.location.href='".get_bloginfo("wpurl")."/search-advanced/'\"/>";
					} elseif ( (get_query_var("type") == "search-advanced")|| (isset($_POST['form_mode']) && $_POST['form_mode'] == "simple" ) ){
							echo "					<input type=\"button\" name=\"back_search\" value=\"". __("Go Back to Basic Search", rb_agency_TEXTDOMAIN) . "\" class=\"button-primary\" onclick=\"javascript:window.location.href='".get_bloginfo("wpurl")."/search-basic/'\"/>";
					}
				}
		}
	}


// *************************************************************************************************** //
// Image Resizing 
class rb_agency_image {
 
	var $image;
	var $image_type;

	function load($filename) {

		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		if( $this->image_type == IMAGETYPE_JPEG ) {

			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ) {

			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			$this->image = imagecreatefrompng($filename);
		}
	}

	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=NULL) {

	if( $image_type == IMAGETYPE_JPEG ) {
		imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) {

			imagegif($this->image,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) {

			imagepng($this->image,$filename);
		}
		if( $permissions != NULL) {

			chmod($filename,$permissions);
		}
	}

	function output($image_type=IMAGETYPE_JPEG) {

		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image);
		} elseif( $image_type == IMAGETYPE_GIF ) {

			imagegif($this->image);
		} elseif( $image_type == IMAGETYPE_PNG ) {

			imagepng($this->image);
		}
	}

	function getWidth() {

		return imagesx($this->image);
	}

	function getHeight() {

		return imagesy($this->image);
	}

	function resizeToHeight($height) {

		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}

	function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getHeight() * $ratio;
		$this->resize($width,$height);
	}

	function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getHeight() * $scale/100;
		$this->resize($width,$height);
	}

	function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}      

	function orientation() {
		if ($this->getWidth() == $this->getHeight()) {
			return "square";
		} elseif ($this->getWidth() > $this->getHeight()) {
			return "landscape";
		} else {
			return "portrait";
		}
	}

}

/*
 * Pagination
 **************************************************************************************************/

class rb_agency_pagination {

	/*Default values*/
	var $total_pages = -1;//items
	var $limit = NULL;
	var $target = ""; 
	var $page = 1;
	var $adjacents = 2;
	var $showCounter = false;
	var $className = "rbpagination";
	var $parameterName = "page";
	var $urlF = false;//urlFriendly
	/*Buttons next and previous*/
	var $nextT = "Next";
	var $nextI = "&#187;"; //&#9658;
	var $prevT = "Previous";
	var $prevI = "&#171;"; //&#9668;
	/*****/
	var $calculate = false;
	
	#Total items
	function items($value){$this->total_pages = (int) $value;}
	
	#how many items to show per page
	function limit($value){$this->limit = (int) $value;}
	
	#Page to sent the page value
	function target($value){$this->target = $value;}
	
	#Current page
	function currentPage($value){$this->page = (int) $value;}
	
	#How many adjacent pages should be shown on each side of the current page?
	function adjacents($value){$this->adjacents = (int) $value;}
	
	#show counter?
	function showCounter($value=""){$this->showCounter=($value===true)?true:false;}
	#to change the class name of the pagination div
	function changeClass($value=""){$this->className=$value;}
	function nextLabel($value){$this->nextT = $value;}
	function nextIcon($value){$this->nextI = $value;}
	function prevLabel($value){$this->prevT = $value;}
	function prevIcon($value){$this->prevI = $value;}
	#to change the class name of the pagination div
	function parameterName($value=""){$this->parameterName=$value;}
	#to change urlFriendly
	function urlFriendly($value="%"){
		if(eregi('^ *$',$value)){
				$this->urlF=false;
				return false;
			}
		$this->urlF=$value;
	}
	
	var $pagination;
	function pagination(){}
	function show(){
		if(!$this->calculate)
			if($this->calculate())
				echo "<div class=\"$this->className\">$this->pagination</div>\n";
	}

	function getOutput(){
		if(!$this->calculate)
			if($this->calculate())
				return "<div class=\"$this->className\">$this->pagination</div>\n";
	}

	function get_pagenum_link($id) {
		if (substr($this->target, 0, 9) == "admin.php") {
			// We are in Admin
		
			if (strpos($this->target,'?') === false) {
				if ($this->urlF) {
					return str_replace($this->urlF,$id,$this->target);
				} else {
					return "$this->target?$this->parameterName=$id";
				}
			} else {
					return "$this->target&$this->parameterName=$id";
			}
		
		} else {
			
			// We are in Page		
			preg_match('/[0-9]/', $this->target, $matches, PREG_OFFSET_CAPTURE);
			if ($matches[0][1] > 0) {
				return substr($this->target, 0, $matches[0][1]) ."/$id/";
			} else {
				return "$this->target/$id/";
			}
			
		} // End Admin/Page Toggle
	}
	
	function calculate(){
		$this->pagination = "";
		$this->calculate == true;
		$error = false;
		if($this->urlF and $this->urlF != '%' and strpos($this->target,$this->urlF)===false){
				//Es necesario especificar el comodin para sustituir
				echo "Especificaste un wildcard para sustituir, pero no existe en el target<br />";
				$error = true;
			}elseif($this->urlF and $this->urlF == '%' and strpos($this->target,$this->urlF)===false){
				echo "Es necesario especificar en el target el comodin % para sustituir el número de página<br />";
				$error = true;
			}
		if($this->total_pages < 0){
				echo "It is necessary to specify the <strong>number of pages</strong> (\$class->items(1000))<br />";
				$error = true;
			}
		if($this->limit == NULL){
				echo "It is necessary to specify the <strong>limit of items</strong> to show per page (\$class->limit(10))<br />";
				$error = true;
			}
		if($error)return false;
		
		$n = trim('<span>'. $this->nextT.'</span> '.$this->nextI);
		$p = trim($this->prevI.' <span>'.$this->prevT .'</span>');
		
		/* Setup vars for query. */
		if($this->page) 
			$start = ($this->page - 1) * $this->limit;      //first item to display on this page
		else
			$start = 0;                               		//if no page var is given, set start to 0
	
		/* Setup page vars for display. */
		$prev = $this->page - 1;                            //previous page is page - 1
		$next = $this->page + 1;                            //next page is page + 1
		$lastpage = ceil($this->total_pages/$this->limit);  //lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;                        		//last page minus 1
		
		/* 
			Now we apply our rules and draw the pagination object. 
			We're actually saving the code to a variable in case we want to draw it more than once.
		*/
		
		if($lastpage > 1){
			if($this->page){
				//anterior button
				if($this->page > 1)
						$this->pagination .= "<a href=\"".$this->get_pagenum_link($prev)."\" class=\"pagedir prev\">$p</a>";
					else
						$this->pagination .= "<span class=\"pagedir disabled\">$p</span>";
			}
			//pages	
			if ($lastpage < 7 + ($this->adjacents * 2)){//not enough pages to bother breaking it up
				for ($counter = 1; $counter <= $lastpage; $counter++){
						if ($counter == $this->page)
								$this->pagination .= "<span class=\"pageno current\">$counter</span>";
							else
								$this->pagination .= "<a class=\"pageno\" href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
					}
			}
			elseif($lastpage > 5 + ($this->adjacents * 2)){//enough pages to hide some
				//close to beginning; only hide later pages
				if($this->page < 1 + ($this->adjacents * 2)){
						for ($counter = 1; $counter < 4 + ($this->adjacents * 2); $counter++){
								if ($counter == $this->page)
										$this->pagination .= "<span class=\"current\">$counter</span>";
									else
										$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
							}
						$this->pagination .= "...";
						$this->pagination .= "<a href=\"".$this->get_pagenum_link($lpm1)."\">$lpm1</a>";
						$this->pagination .= "<a href=\"".$this->get_pagenum_link($lastpage)."\">$lastpage</a>";
					}
				//in middle; hide some front and some back
				elseif($lastpage - ($this->adjacents * 2) > $this->page && $this->page > ($this->adjacents * 2)){
						$this->pagination .= "<a href=\"".$this->get_pagenum_link(1)."\">1</a>";
						$this->pagination .= "<a href=\"".$this->get_pagenum_link(2)."\">2</a>";
						$this->pagination .= "...";
						for ($counter = $this->page - $this->adjacents; $counter <= $this->page + $this->adjacents; $counter++)
							if ($counter == $this->page)
									$this->pagination .= "<span class=\"current\">$counter</span>";
								else
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
						$this->pagination .= "...";
						$this->pagination .= "<a href=\"".$this->get_pagenum_link($lpm1)."\">$lpm1</a>";
						$this->pagination .= "<a href=\"".$this->get_pagenum_link($lastpage)."\">$lastpage</a>";
					}
				//close to end; only hide early pages
				else {
						$this->pagination .= "<a href=\"".$this->get_pagenum_link(1)."\">1</a>";
						$this->pagination .= "<a href=\"".$this->get_pagenum_link(2)."\">2</a>";
						$this->pagination .= "...";
						for ($counter = $lastpage - (2 + ($this->adjacents * 2)); $counter <= $lastpage; $counter++)
							if ($counter == $this->page)
									$this->pagination .= "<span class=\"current\">$counter</span>";
								else
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
					}
			}
			if($this->page){
				//siguiente button
				if ($this->page < $counter - 1)
						$this->pagination .= "<a href=\"".$this->get_pagenum_link($next)."\" class=\"pagedir next\">$n</a>";
					else
						$this->pagination .= "<span class=\"pagedir disabled\">$n</span>";
					if($this->showCounter)$this->pagination .= "<div class=\"pagedir pagination_data\">($this->total_pages Pages)</div>";
			}
		}
		return true;
	}
}

// retrieving data type title
// if rb-interact is not installed
if ( !function_exists('retrieve_title') ) {  
	function retrieve_title($id=0) {
	   global $wpdb;
                /* 
		* return title
		*/
		$check_type = "SELECT DataTypeTitle FROM ". table_agency_data_type ." WHERE DataTypeID = " . $id;
		$check_query = mysql_query($check_type) OR die(mysql_error());
		if(mysql_num_rows($check_query) > 0){
			$fetch = mysql_fetch_assoc($check_query);
			return $fetch['DataTypeTitle'];
		} else {
			return false;
		}
	}
}

// *************************************************************************************************** //
// Custom Fields
function rb_custom_fields($visibility = 0, $ProfileID, $ProfileGender, $ProfileGenderShow = false, $SearchMode = false){
	
	$all_permit = false; // set to false
	
	if($ProfileID != 0){
		$query = mysql_query("SELECT ProfileType FROM ".table_agency_profile." WHERE ProfileID = ".$ProfileID);
		$fetchID = mysql_fetch_assoc($query);
		$ptype = $fetchID["ProfileType"];	
		if(strpos($ptype,",") > -1){
			$t = explode(",",$ptype);
			$ptype = ""; 
			foreach($t as $val){
				$ptyp[] = retrieve_title($val);
			}
			$ptype = implode(",",$ptyp);
		} else {
			$ptype = retrieve_title($ptype);
		}
		$ptype = str_replace(",","",$ptype);
	} else {
		$all_permit = true;
	}
	
	
	$query3 = "SELECT * FROM ". table_agency_customfields ." WHERE ProfileCustomView = ".$visibility."  ORDER BY ProfileCustomOrder";
	$results3 = mysql_query($query3) or die(mysql_error());
	$count3 = 0;
	
	while ($data3 = mysql_fetch_assoc($results3)) {

		   /*
			* Get Profile Types to
			* filter models from clients
			*/
			$permit_type = false;
			$PID = $data3['ProfileCustomID'];
			$get_types = "SELECT ProfileCustomTypes FROM ". table_agency_customfields_types .
						" WHERE ProfileCustomID = " . $PID;
	
			$result = mysql_query($get_types);
			$types = "";
			while ( $p = mysql_fetch_array($result)){
					$types = $p['ProfileCustomTypes'];			    
			}
			if($types != "" || $types != NULL){
				if(strpos($types,",") > -1){
					$types = explode(",",$types);
					foreach($types as $t){
						if(strpos($ptype,$t) > -1) {$permit_type=true; break;}  
					}			
				} else {
						if(strpos($ptype,$types) > -1) $permit_type=true;  
				}
			}
			  
			
			if($permit_type || $all_permit){
				if($ProfileGenderShow ==true){
					if($data3["ProfileCustomShowGender"] == $ProfileGender && $count3 >=1 ){ // Depends on Current LoggedIn User's Gender
						$count3++;
						rb_custom_fields_template($visibility, $ProfileID, $data3);
					} elseif(empty($data3["ProfileCustomShowGender"])) {
						$count3++;
						rb_custom_fields_template($visibility, $ProfileID, $data3);
					}
				} else {
						$count3++;
						rb_custom_fields_template($visibility, $ProfileID, $data3);
				}
			}

			// END Query2
		echo "    </td>\n";
		echo "  </tr>\n";
	} // End while
	if ($count3 == 0) {
		echo "  <tr valign=\"top\">\n";
		echo "    <th scope=\"row\">". __("There are no custom fields loaded", rb_agency_TEXTDOMAIN) .".  <a href=". admin_url("admin.php?page=rb_agency_settings&ConfigID=7") ."'>". __("Setup Custom Fields", rb_agency_TEXTDOMAIN) ."</a>.</th>\n";
		echo "  </tr>\n";
	}
}

// *************************************************************************************************** //
// Custom Fields TEMPLATE 
function rb_custom_fields_template($visibility = 0, $ProfileID, $data3){

	$rb_agency_options_arr 				= get_option('rb_agency_options');
	$rb_agency_option_unittype  		= $rb_agency_options_arr['rb_agency_option_unittype'];
	$rb_agency_option_profilenaming 	= (int)$rb_agency_options_arr['rb_agency_option_profilenaming'];
	$rb_agency_option_locationtimezone 	= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];
	
	if( (!empty($data3['ProfileCustomID']) || $data3['ProfileCustomID'] !="") ){ 
   
		$subresult = mysql_query("SELECT ProfileID,ProfileCustomValue,ProfileCustomID FROM ". table_agency_customfield_mux ." WHERE ProfileCustomID = '". $data3['ProfileCustomID'] ."' AND ProfileID = ". $ProfileID);
		$row = @mysql_fetch_assoc($subresult);
		
		$ProfileCustomValue = $row["ProfileCustomValue"];
		$ProfileCustomTitle = $data3['ProfileCustomTitle'];
		$ProfileCustomType  = $data3['ProfileCustomType'];
	
			// SET Label for Measurements
			// Imperial(in/lb), Metrics(ft/kg)
			$rb_agency_options_arr = get_option('rb_agency_options');
			 $rb_agency_option_unittype  = $rb_agency_options_arr['rb_agency_option_unittype'];
			 $measurements_label = "";
			if ($ProfileCustomType == 7) { //measurements field type
				if ($rb_agency_option_unittype ==0) { // 0 = Metrics(ft/kg)
					if($data3['ProfileCustomOptions'] == 1){
						$measurements_label  ="<em>(cm)</em>";
					} elseif($data3['ProfileCustomOptions'] == 2) {
						$measurements_label  ="<em>(kg)</em>";
					} elseif($data3['ProfileCustomOptions'] == 3) {
					  	$measurements_label  ="<em>(In Feet/Inches)</em>";
					}
				} elseif($rb_agency_option_unittype ==1) { //1 = Imperial(in/lb)
					if($data3['ProfileCustomOptions'] == 1){
						$measurements_label  ="<em>(In Inches)</em>";
					} elseif($data3['ProfileCustomOptions'] == 2) {
					  	$measurements_label  ="<em>(In Pounds)</em>";
					} elseif($data3['ProfileCustomOptions'] == 3) {
					  	$measurements_label  ="<em>(In Feet/Inches)</em>";
					}
				}
			}  
			$isTextArea = "";
			if($ProfileCustomType == 4){
				$isTextArea ="textarea-field"; 
			}
		echo "  <tr valign=\"top\" class=\"".$isTextArea."\">\n";
		echo "    <th scope=\"row\"><div class=\"box\">". $data3['ProfileCustomTitle'].$measurements_label."</div></th>\n"; 
		echo "    <td>\n";		  
		  
			if ($ProfileCustomType == 1) { //TEXT
						echo "<input type=\"text\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"". $ProfileCustomValue ."\" /><br />\n";						
			} elseif ($ProfileCustomType == 2) { // Min Max
			
				$ProfileCustomOptions_String = str_replace(",",":",strtok(strtok($data3['ProfileCustomOptions'],"}"),"{"));
				list($ProfileCustomOptions_Min_label,$ProfileCustomOptions_Min_value,$ProfileCustomOptions_Max_label,$ProfileCustomOptions_Max_value) = explode(":",$ProfileCustomOptions_String);
			 
				if (!empty($ProfileCustomOptions_Min_value) && !empty($ProfileCustomOptions_Max_value)) {
					echo "<br /><br /> <label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Min", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>\n";
					echo "<input type=\"text\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"". $ProfileCustomOptions_Min_value ."\" />\n";
					echo "<br /><br /><br /><label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Max", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>\n";
					echo "<input type=\"text\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"". $ProfileCustomOptions_Max_value ."\" /><br />\n";
				} else {
					echo "<br /><br />  <label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Min", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>\n";
					echo "<input type=\"text\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"".$_SESSION["ProfileCustomID". $data3['ProfileCustomID']]."\" />\n";
					echo "<br /><br /><br /><label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Max", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>\n";
					echo "<input type=\"text\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"".$_SESSION["ProfileCustomID". $data3['ProfileCustomID']]."\" /><br />\n";
				}
			 
			} elseif ($ProfileCustomType == 3) {  // Drop Down
				
				list($option1,$option2) = explode(":",$data3['ProfileCustomOptions']);	
					
				$data = explode("|",$option1);
				$data2 = explode("|",$option2);
				
				echo "<label class=\"dropdown\">".$data[0]."</label>";
				echo "<select name=\"ProfileCustomID". $data3['ProfileCustomID'] ."[]\">\n";
				echo "<option value=\"\">--</option>";
					$pos = 0;
					foreach($data as $val1){
						
						if($val1 != end($data) && $val1 != $data[0]){
						
							if ($val1 == $ProfileCustomValue ) {
								$isSelected = "selected=\"selected\"";
								echo "<option value=\"".$val1."\" ".$isSelected .">".$val1."</option>";
							} else {
								echo "<option value=\"".$val1."\" >".$val1."</option>";
							}					
						}
					}
				echo "</select>\n";
					
					
				if (!empty($data2) && !empty($option2)) {
					echo "<label class=\"dropdown\">".$data2[0]."</label>";
				
						$pos2 = 0;
						echo "11<select name=\"ProfileCustomID". $data3['ProfileCustomID'] ."[]\">\n";
						echo "<option value=\"\">--</option>";
						foreach($data2 as $val2){
								if($val2 != end($data2) && $val2 !=  $data2[0]){
									echo "<option value=\"".$val2."\" ". selected($val2, $ProfileCustomValue) ." >".$val2."</option>";
								}
							}
						echo "</select>\n";
				}
			} elseif ($ProfileCustomType == 4) {
					echo "<textarea style=\"width: 100%; min-height: 300px;\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\">". $ProfileCustomValue ."</textarea>";
			} elseif ($ProfileCustomType == 5) {
				echo "<fieldset>";
				$array_customOptions_values = explode("|",$data3['ProfileCustomOptions']);

				foreach($array_customOptions_values as $val){
					$xplode = explode(",",$ProfileCustomValue);
					if(!empty($val)){
						echo "<label class=\"checkbox\"><input type=\"checkbox\" value=\"". $val."\"   "; if(in_array($val,$xplode)){ echo "checked=\"checked\""; } echo" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."[]\" /> ";
						echo "". $val."</label><br />";                               
					}
				}
				echo "</fieldset>";

			} elseif ($ProfileCustomType == 6) {
				
				$array_customOptions_values = explode("|",$data3['ProfileCustomOptions']);
				
				foreach($array_customOptions_values as $val){
					echo "<fieldset>";
						echo "<label><input type=\"radio\" value=\"". $val."\" "; checked($val, $ProfileCustomValue); echo" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."[]\" />";
						echo "". $val."</label><br/>";
					echo "</fieldset>";
				}
			} elseif ($ProfileCustomType == 7) { //Imperial/Metrics
			
				if($data3['ProfileCustomOptions']==3){
					if($rb_agency_option_unittype == 1){
						// 
						echo "<select name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\">\n";
						if (empty($ProfileCustomValue)) {
							echo "  <option value=\"\">--</option>\n";
						}
						// 
						$i=12;
						$heightraw = 0;
						$heightfeet = 0;
						$heightinch = 0;
						while($i<=90)  { 
							$heightraw = $i;
							$heightfeet = floor($heightraw/12);
							$heightinch = $heightraw - floor($heightfeet*12);
							echo " <option value=\"". $i ."\" ". selected($ProfileCustomValue, $i) .">". $heightfeet ." ft ". $heightinch ." in</option>\n";
							$i++;
						}
						echo " </select>\n";
					} else {
					// 
					echo "  <input type=\"text\" id=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"". $ProfileCustomValue ."\" />\n";
					}
				} else {
					  //validate for float type.
					  echo "  <input class='imperial_metrics' type=\"text\" id=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" name=\"ProfileCustomID". $data3['ProfileCustomID'] ."\" value=\"". $ProfileCustomValue ."\" />
					          <div class='error_msg' style='color:red; min-width:0px'></div>";
				}						
			}									
	} // End if Empty ProfileCustomID
}
/*/
*   ================ Get Country Title ===================
*   @returns Country Title
/*/   
function rb_agency_getCountryTitle($country_id=""){
	
	global $wpdb;
	
	if(empty($country_id)) return false;
	
	$query ="SELECT CountryTitle FROM ". table_agency_data_country ." WHERE CountryID = " . $country_id;
	$result = $wpdb->get_row($query);

	if(count($result) > 0){
		return $result->CountryTitle;
	}	
	
	return false;

}

/*/
*   ================ Get Profile Gender for each user ===================
*   @returns GenderTitle
/*/   
function rb_agency_getGenderTitle($ProfileGenderID){
 
	$query = "SELECT GenderID, GenderTitle FROM ". table_agency_data_gender ." WHERE GenderID='".$ProfileGenderID."'";
	$results = mysql_query($query) or die(mysql_error());
	$count = mysql_num_rows($results);

	if($count > 0){
	 	$data = mysql_fetch_assoc($results);
		return $data["GenderTitle"];
	} else {
		return 0;	 
	}
	rb_agency_checkExecution();
}

/*/
*   ================ Filters custom fields to show based on assigned gender ===================
*   @returns GenderTitle
/*/
function rb_agency_filterfieldGender($ProfileCustomID, $ProfileGenderID){

	$query = "SELECT * FROM ". table_agency_customfields ." WHERE ProfileCustomView = 0 AND ProfileCustomID ='".$ProfileCustomID."' AND ProfileCustomShowGender IN('".$ProfileGenderID."','') ";
	$results = mysql_query($query) or die(mysql_error());
	$count = mysql_num_rows($results);

	if($count > 0){
	 	return true;  
	} else {
	 	return false;
	}
	rb_agency_checkExecution();
}
 
/*/
* ======================== Get Favorite & Casting Cart Links ===============
* @Returns links
/*/		
function rb_agency_get_miscellaneousLinks($ProfileID = ""){
 
	rb_agency_checkExecution();

	$disp = "";
	$disp .= "<div class=\"favorite-casting\">";

	if (is_permitted('favorite')) {
		if(!empty($ProfileID)){
			$queryFavorite = mysql_query("SELECT fav.SavedFavoriteTalentID as favID FROM ".table_agency_savedfavorite." fav WHERE ".rb_agency_get_current_userid()." = fav.SavedFavoriteProfileID AND fav.SavedFavoriteTalentID = '".$ProfileID."' ") or die(mysql_error());
			$dataFavorite = mysql_fetch_assoc($queryFavorite); 
			$countFavorite = mysql_num_rows($queryFavorite);
			if($countFavorite <= 0){
					$disp .= "    <div class=\"favorite\"><a title=\"Save to Favorites\" rel=\"nofollow\" href=\"javascript:;\" class=\"save_favorite\" id=\"".$ProfileID."\"></a></div>\n";
			}else{
					$disp .= "<div class=\"favorite\"><a rel=\"nofollow\" title=\"Remove from Favorites\" href=\"javascript:;\" class=\"favorited\" id=\"".$ProfileID."\"></a></div>\n";
			}					

		}
	}	
	
	if (is_permitted('casting')) {
		if(!empty($ProfileID)){
			$queryCastingCart = mysql_query("SELECT cart.CastingCartTalentID as cartID FROM ".table_agency_castingcart."  cart WHERE ".rb_agency_get_current_userid()." = cart.CastingCartProfileID AND cart.CastingCartTalentID = '".$ProfileID."' ") or die(mysql_error());
			$dataCastingCart = mysql_fetch_assoc($queryCastingCart); 
			$countCastingCart = mysql_num_rows($queryCastingCart);
			if($countCastingCart <=0){
					$disp .= "<div class=\"castingcart\"><a title=\"Add to Casting Cart\" href=\"javascript:;\" id=\"".$ProfileID."\"  class=\"save_castingcart\"></a></div></li>";
			} else {
					if(get_query_var('type')=="casting"){ //hides profile block when icon is click
						$divHide="onclick=\"javascript:document.getElementById('div$ProfileID').style.display='none';\"";
					}
					$disp .= "<div class=\"castingcart\"><a $divHide href=\"javascript:void(0)\"  id=\"".$ProfileID."\" title=\"Remove from Casting Cart\"  class=\"saved_castingcart\"></a></div>";
			}
		}
	}

	$disp .= "</div><!-- .favorite-casting -->";
 	return $disp; 
}


/*/
* ======================== NEW Get Favorite & Casting Cart Links ===============
* @Returns links
/*/		
function rb_agency_get_new_miscellaneousLinks($ProfileID = ""){
 
	$rb_agency_options_arr 				= get_option('rb_agency_options');
	$rb_agency_option_profilelist_favorite		= isset($rb_agency_options_arr['rb_agency_option_profilelist_favorite']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_favorite'] : 0;
	$rb_agency_option_profilelist_castingcart 	= isset($rb_agency_options_arr['rb_agency_option_profilelist_castingcart']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_castingcart'] : 0;
	rb_agency_checkExecution();

	if ($rb_agency_option_profilelist_favorite) {
		//Execute query - Favorite Model
		if(!empty($ProfileID)){
			$queryFavorite = mysql_query("SELECT fav.SavedFavoriteTalentID as favID FROM ".table_agency_savedfavorite." fav WHERE ".rb_agency_get_current_userid()." = fav.SavedFavoriteProfileID AND fav.SavedFavoriteTalentID = '".$ProfileID."' ") or die(mysql_error());
			$dataFavorite = mysql_fetch_assoc($queryFavorite); 
			$countFavorite = mysql_num_rows($queryFavorite);
		}
	}	
	
	if ($rb_agency_option_profilelist_castingcart) {
      	//Execute query - Casting Cart
		if(!empty($ProfileID)){
			$queryCastingCart = mysql_query("SELECT cart.CastingCartTalentID as cartID FROM ".table_agency_castingcart."  cart WHERE ".rb_agency_get_current_userid()." = cart.CastingCartProfileID AND cart.CastingCartTalentID = '".$ProfileID."' ") or die(mysql_error());
			$dataCastingCart = mysql_fetch_assoc($queryCastingCart); 
			$countCastingCart = mysql_num_rows($queryCastingCart);
		}
	}

	$disp = "";
	$disp .= "<div class=\"favorite-casting\">";
        
	if ($rb_agency_option_profilelist_castingcart) {
		if($countCastingCart <=0){
			$disp .= "<div class=\"newcastingcart\"><a title=\"Add to Casting Cart\" href=\"javascript:;\" id=\"".$ProfileID."\"  class=\"save_castingcart\">ADD TO CASTING CART</a></div></li>";
		} else {
			if(get_query_var('type')=="casting"){ //hides profile block when icon is click
			 	$divHide="onclick=\"javascript:document.getElementById('div$ProfileID').style.display='none';\"";
			}
			$disp .= "<div class=\"gotocastingcard\"><a $divHide href=\"". get_bloginfo("wpurl") ."/profile-casting/\"  title=\"Go to Casting Cart\">VIEW CASTING CART</a></div>";
	  	}
	}
        
        
	if ($rb_agency_option_profilelist_favorite) {
		
		if($countFavorite <= 0){
			$disp .= "<div class=\"newfavorite\"><a title=\"Save to Favorites\" rel=\"nofollow\" href=\"javascript:;\" class=\"save_favorite\" id=\"".$ProfileID."\">SAVE TO FAVORITES</a></div>\n";
		}else{
			$disp .= "<div class=\"viewfavorites\"><a rel=\"nofollow\" title=\"View Favorites\" href=\"".  get_bloginfo("wpurl") ."/profile-favorite/\"/>VIEW FAVORITES</a></div>\n";
		}					
	}
			

	$disp .= "</div><!-- .favorite-casting -->";
 	return $disp; 
}



/*/
* ======================== Get New Custom Fields ===============
* @Returns Custom Fields
/*/
function rb_agency_getNewProfileCustomFields($ProfileID, $ProfileGender, $LabelTag="strong") {

	global $wpdb;
	global $rb_agency_option_unittype;
	
	$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC"));
	foreach ($resultsCustom as $resultCustom) { 

		if( $resultCustom->ProfileCustomID != 16 ):

		if(!empty($resultCustom->ProfileCustomValue )){
			if ($resultCustom->ProfileCustomType == 7) { //measurements field type
			   	if($rb_agency_option_unittype == 0){ // 0 = Metrics(ft/kg)
					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(cm)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(kg)";
					}
			 	} elseif ($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)
					if($resultCustom->ProfileCustomOptions == 1){
					$label = "(in)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(lbs)";
					} elseif($resultCustom->ProfileCustomOptions == 3){
						$label = "(ft/in)";
					}
			 	} 
				$measurements_label = "<span class=\"label\">". $label ."</span>";
			} else {
				$measurements_label = "";
			}

			// Lets not do this...
			$measurements_label = "";
		 
			if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
				if ($resultCustom->ProfileCustomType == 7){ 
					if($resultCustom->ProfileCustomOptions == 3){ 
					   	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	echo "<li><".$LabelTag.">". $resultCustom->ProfileCustomTitle .$measurements_label.":</".$LabelTag."> ".$heightfeet."ft ".$heightinch." in</li>\n";
					} else {
					   	echo "<li><".$LabelTag.">". $resultCustom->ProfileCustomTitle .$measurements_label.":</".$LabelTag."> ". $resultCustom->ProfileCustomValue ."</li>\n";
					}
				} else {
					   	echo "<li><".$LabelTag.">". $resultCustom->ProfileCustomTitle .$measurements_label.":</".$LabelTag."> ". $resultCustom->ProfileCustomValue ."</li>\n";
				}
			} elseif ($resultCustom->ProfileCustomView == "2") {
				if ($resultCustom->ProfileCustomType == 7){
					if($resultCustom->ProfileCustomOptions == 3){
					 	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	echo "<li><".$LabelTag.">". $resultCustom->ProfileCustomTitle .$measurements_label.":</".$LabelTag."> ".$heightfeet."ft ".$heightinch." in</li>\n";
					} else {
						echo "<li><".$LabelTag.">". $resultCustom->ProfileCustomTitle .$measurements_label.":</".$LabelTag."> ". $resultCustom->ProfileCustomValue ."</li>\n";
					}
				} else {
					echo "<li><".$LabelTag.">". $resultCustom->ProfileCustomTitle .$measurements_label.":</".$LabelTag."> ". $resultCustom->ProfileCustomValue ."</li>\n";
				}
			}
		}
		endif;
	}
}

/*/
* ======================== Get Custom Fields ===============
* @Returns Custom Fields
/*/
function rb_agency_getProfileCustomFields($ProfileID, $ProfileGender) {

	global $wpdb;
	global $rb_agency_option_unittype;
	
	$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC"));
	foreach ($resultsCustom as $resultCustom) {
		if(!empty($resultCustom->ProfileCustomValue )){
			if ($resultCustom->ProfileCustomType == 7) { //measurements field type
			   	if($rb_agency_option_unittype == 0){ // 0 = Metrics(ft/kg)
					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(cm)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(kg)";
					}
			 	} elseif ($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)
					if($resultCustom->ProfileCustomOptions == 1){
					$label = "(in)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(lbs)";
					} elseif($resultCustom->ProfileCustomOptions == 3){
						$label = "(ft/in)";
					}
			 	}
				$measurements_label = "<span class=\"label\">". $label ."</span>";
			} else {
				$measurements_label = "";
			}

			// Lets not do this...
			$measurements_label = "";
		 
			if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
				if ($resultCustom->ProfileCustomType == 7){
					if($resultCustom->ProfileCustomOptions == 3){
					   	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ".$heightfeet."ft ".$heightinch." in</li>\n";
					} else {
					   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
					}
			   	} else {
					if ($resultCustom->ProfileCustomType == 4){
					   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong><br/> ". nl2br($resultCustom->ProfileCustomValue) ."</li>\n";
					} else {
					   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong>  ". split_language(',',', ',$resultCustom->ProfileCustomValue) ."</li>\n";
					}
			   	}
			  
			} elseif ($resultCustom->ProfileCustomView == "2") {
				if ($resultCustom->ProfileCustomType == 7){
				  	if($resultCustom->ProfileCustomOptions == 3){
					 	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ".$heightfeet."ft ".$heightinch." in</li>\n";
				  	} else {
						echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
				  	}
			   	} else {
					echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
			   	}
			}
		}
	}
}

/*/
* ======================== Get Custom Fields ===============
* @Returns Custom Fields
/*/
function rb_agency_getProfileCustomFields_admin($ProfileID, $ProfileGender) {

	global $wpdb;
	global $rb_agency_option_unittype;
	$html = "";
	
	$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC"));
	foreach ($resultsCustom as $resultCustom) {
		if(!empty($resultCustom->ProfileCustomValue )){
			if ($resultCustom->ProfileCustomType == 7) { //measurements field type
			   	if($rb_agency_option_unittype == 0){ // 0 = Metrics(ft/kg)
					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(cm)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(kg)";
					}
			 	} elseif ($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)
					if($resultCustom->ProfileCustomOptions == 1){
					$label = "(in)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(lbs)";
					} elseif($resultCustom->ProfileCustomOptions == 3){
						$label = "(ft/in)";
					}
			 	}
				$measurements_label = "<span class=\"label\">". $label ."</span>";
			} else {
				$measurements_label = "";
			}

			// Lets not do this...
			$measurements_label = "";
		 
			if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
				if ($resultCustom->ProfileCustomType == 7){
					if($resultCustom->ProfileCustomOptions == 3){
					   	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ".$heightfeet."ft ".$heightinch." in</li>\n";
					} else {
					   	$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
					}
			   	} else {
					if ($resultCustom->ProfileCustomType == 4){
					   	$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong><br/> ". nl2br($resultCustom->ProfileCustomValue) ."</li>\n";
					} else {
					   	$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong>  ". split_language(',',', ',$resultCustom->ProfileCustomValue) ."</li>\n";
					}
			   	}
			  
			} elseif ($resultCustom->ProfileCustomView == "2") {
				if ($resultCustom->ProfileCustomType == 7){
				  	if($resultCustom->ProfileCustomOptions == 3){
					 	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ".$heightfeet."ft ".$heightinch." in</li>\n";
				  	} else {
						$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
				  	}
			   	} else {
					$html .=  "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
			   	}
			}
		}
	}
	
	return $html;
}

/*/
* ======================== Split Languages ===============
* @Returns languages separatad by space
* @param $delimiter : the needle to split the languages
* @param $separator : the language output separator
* @param $languages : the languages
/*/
function split_language($delimiter, $separator, $languages){
	$languages = explode($delimiter, $languages);
	for ($start=0; $start < count($languages); $start++) {
		$languages = implode($separator, $languages);
	}
	return $languages;
}

/*/
* ======================== Get Custom Fields ===============
* @Returns Custom Fields excluding a title
* @parm includes an array of title
/*/
function rb_agency_getProfileCustomFieldsExTitle($ProfileID, $ProfileGender, $title_to_exclude) {

	global $wpdb;
	global $rb_agency_option_unittype;
	
	$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC"));
	foreach ($resultsCustom as $resultCustom) {
		if(!in_array($resultCustom->ProfileCustomTitle, $title_to_exclude)){ 	
			if(!empty($resultCustom->ProfileCustomValue )){
				if ($resultCustom->ProfileCustomType == 7) { //measurements field type
					if($rb_agency_option_unittype == 0){ // 0 = Metrics(ft/kg)
						if($resultCustom->ProfileCustomOptions == 1){
							$label = "(cm)";
						} elseif($resultCustom->ProfileCustomOptions == 2){
							$label = "(kg)";
						}
				 	} elseif ($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)
						if($resultCustom->ProfileCustomOptions == 1){
							$label = "(in)";
						} elseif($resultCustom->ProfileCustomOptions == 2){
							$label = "(lbs)";
						} elseif($resultCustom->ProfileCustomOptions == 3){
							$label = "(ft/in)";
						}
					}
				 	$measurements_label = "<span class=\"label\">". $label ."</span>";
				} else {
					$measurements_label = "";
				}

				// Lets not do this...
				$measurements_label = "";
			 
				if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
					if ($resultCustom->ProfileCustomType == 7){
						if($resultCustom->ProfileCustomOptions == 3){
						   	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
						   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ".$heightfeet."ft ".$heightinch." in</li>\n";
						} else {
						  	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
						}
				   	} else {
						if ($resultCustom->ProfileCustomType == 4){
							echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong><br/> ". nl2br($resultCustom->ProfileCustomValue) ."</li>\n";
						} else {
							echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
						}
				   	}
				  
				} elseif ($resultCustom->ProfileCustomView == "2") {
					if ($resultCustom->ProfileCustomType == 7){
					  	if($resultCustom->ProfileCustomOptions == 3){
						 	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
						   	echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ".$heightfeet."ft ".$heightinch." in</li>\n";
					  	} else {
							echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
					  	}
				   	} else {
						echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
				   	}
				}
			}
		}
	} 
} 

function rb_agency_getProfileCustomFieldsExperienceDescription($ProfileID, $ProfileGender, $title_to_exclude) {

	global $wpdb;
	global $rb_agency_option_unittype;
	
	$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC"));
	foreach ($resultsCustom as $resultCustom) {
		if(!in_array($resultCustom->ProfileCustomTitle, $title_to_exclude)){ 	
			if(!empty($resultCustom->ProfileCustomValue )){
				
				// Lets not do this...
				$measurements_label = "";
			 if ($resultCustom->ProfileCustomTitle == 'Experience(s)'){
				if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
					
						echo "<li><strong>". $resultCustom->ProfileCustomTitle .$measurements_label.":</strong> ". $resultCustom->ProfileCustomValue ."</li>\n";
				   	
				  
				}  	
			 }
			}
		}
	} 
}

function rb_agency_getProfileCustomFieldsEcho($ProfileID, $ProfileGender,$exclude="",$include="") {
	global $wpdb;
	global $rb_agency_option_unittype;

	$query="SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ."";
	
	if(!empty($exclude)){$query.="AND ProfileCustomID IN($exclude)";}
	if(!empty($include)){$query.="AND ProfileCustomID NOT IN($include)";}

	$query.="GROUP 3 BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC ";
	
	$resultsCustom = $wpdb->get_results($query);
	foreach ($resultsCustom as $resultCustom) {
		if(!empty($resultCustom->ProfileCustomValue )){
			if ($resultCustom->ProfileCustomType == 7) { //measurements field type
			   	if($rb_agency_option_unittype == 0){ // 0 = Metrics(ft/kg)
					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(cm)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(kg)";
					}
				} elseif ($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)
					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(in)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(lbs)";
					 }elseif($resultCustom->ProfileCustomOptions == 3){
						$label = "(ft/in)";
					}
			 	}
				$measurements_label = "<span class=\"label\">". $label ."</span>";
			} else {
			 	$measurements_label = "";
			}

			// Lets not do this...
			$measurements_label = "";
		
			if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
				
				if ($resultCustom->ProfileCustomType == 7){
					if($resultCustom->ProfileCustomOptions == 3){
					   	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   echo "<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>".$heightfeet."ft ".$heightinch." in</span></li>\n";
					} else {
					   	echo  "<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
					}
			   	} else {
				   if($echo!="dontecho"){  // so it wont exit if PDF generator request info
					    if($resultCustom->ProfileCustomTitle.$measurements_label=="Experience"){return "";}
				   	
					echo "<li id='". $resultCustom->ProfileCustomTitle .$measurements_label."'><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
			   	}
			  }
			} elseif ($resultCustom->ProfileCustomView == "2") {
				if ($resultCustom->ProfileCustomType == 7){
				  	if($resultCustom->ProfileCustomOptions == 3){
					 	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	echo "<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>".$heightfeet."ft ".$heightinch." in</span></li>\n";
				  	} else {
						echo "<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
				  	}
			   	} else {
					echo "<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
			   }
			}
		}
	}
	if($echo=="dontecho"){return $return;}else{echo $return;}
}

function rb_agency_getProfileCustomFieldsCustom($ProfileID, $ProfileGender,$echo="") {

	global $wpdb;
	global $rb_agency_option_unittype;

	$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle,c.ProfileCustomType,c.ProfileCustomOptions, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC"));

	foreach ($resultsCustom as $resultCustom) {

		if(!empty($resultCustom->ProfileCustomValue )){
			if ($resultCustom->ProfileCustomType == 7) { //measurements field type
			   	if($rb_agency_option_unittype == 0){ // 0 = Metrics(ft/kg)
					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(cm)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(kg)";
					}
				} elseif ($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)

					if($resultCustom->ProfileCustomOptions == 1){
						$label = "(in)";
					} elseif($resultCustom->ProfileCustomOptions == 2){
						$label = "(lbs)";
					} elseif($resultCustom->ProfileCustomOptions == 3){
						$label = "(ft/in)";
					}
		 		}

				$measurements_label = "<span class=\"label\">". $label ."</span>";

	 		} else {
		 		$measurements_label = "";
	 		}

		 	// Lets not do this...
			$measurements_label = "";

			if (rb_agency_filterfieldGender($resultCustom->ProfileCustomID, $ProfileGender)){
				
				if ($resultCustom->ProfileCustomType == 7){
					if($resultCustom->ProfileCustomOptions == 3){
					   	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	$return.="<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>".$heightfeet."ft ".$heightinch." in</span></li>\n";
					} else {
					   	$return.="<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
					}
			   	} else {
				   	if($echo!="dontecho"){  // so it wont exit if PDF generator request info
					    if($resultCustom->ProfileCustomTitle.$measurements_label=="Experience") { return ""; }
				   	}
					$return.="<li id='". $resultCustom->ProfileCustomTitle .$measurements_label."'><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
			   	}
			  
			} elseif ($resultCustom->ProfileCustomView == "2") {
				if ($resultCustom->ProfileCustomType == 7){
				  	if($resultCustom->ProfileCustomOptions == 3){
					 	$heightraw = $resultCustom->ProfileCustomValue; $heightfeet = floor($heightraw/12); $heightinch = $heightraw - floor($heightfeet*12);
					   	$return.="<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>".$heightfeet."ft ".$heightinch." in</span></li>\n";
				  	} else {
						$return.="<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
				  	}
			   	} else {
					$return.="<li><label>". $resultCustom->ProfileCustomTitle .$measurements_label."</label><span>". $resultCustom->ProfileCustomValue ."</span></li>\n";
			   	}
			}
		}
	}	
	if($echo=="dontecho"){return $return;}else{echo $return;}
}

		$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_profilelist_favorite		 = isset($rb_agency_options_arr['rb_agency_option_profilelist_favorite']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_favorite'] : 0;
			
//****************************************************************************************************//
// Add / Handles Ajax Request ====== ADD To Favorites		

	function rb_agency_save_favorite() {
		global $wpdb;
		if(is_user_logged_in()){	
			if(isset($_POST["talentID"])){
				 $query_favorite = mysql_query("SELECT * FROM ".table_agency_savedfavorite." WHERE SavedFavoriteTalentID='".$_POST["talentID"]."'  AND SavedFavoriteProfileID = '".rb_agency_get_current_userid()."'" ) or die("error");
				 $count_favorite = mysql_num_rows($query_favorite);
				 $datas_favorite = mysql_fetch_assoc($query_favorite);
				 
				 if($count_favorite<=0){ //if not exist insert favorite!
					 
					   mysql_query("INSERT INTO ".table_agency_savedfavorite."(SavedFavoriteID,SavedFavoriteProfileID,SavedFavoriteTalentID) VALUES('','".rb_agency_get_current_userid()."','".$_POST["talentID"]."')") or die("error");
					 
				 }else{ // favorite model exist, now delete!
					 
					  mysql_query("DELETE FROM  ".table_agency_savedfavorite." WHERE SavedFavoriteTalentID='".$_POST["talentID"]."'  AND SavedFavoriteProfileID = '".rb_agency_get_current_userid()."'") or die("error");
				 }				
			}			
		}
		else {
			echo "not_logged";
		}
		die();
	}

	function rb_agency_save_favorite_javascript() {
	?>

		<!--RB Agency Favorite -->
		<script type="text/javascript" >jQuery(document).ready(function() { 
			jQuery(".favorite a:first, .favorite a").click(function(){
				var Obj = jQuery(this);
				jQuery.ajax({type: 'POST',url: '<?php echo admin_url('admin-ajax.php'); ?>',
				data: {action: 'rb_agency_save_favorite',  'talentID': jQuery(this).attr("id")},

				success: function(results) {
				if(results=='error'){ 
					Obj.fadeOut().empty().html("Error in query. Try again").fadeIn(); 
				} else if(results==-1) { 
					Obj.fadeOut().empty().html("<span style=\"color:red;font-size:11px;\">You're not signed in.</span><a href=\"<?php echo get_bloginfo("wpurl"); ?>/profile-member/\">Sign In</a>.").fadeIn();
					setTimeout(function() { 
						if(Obj.attr("class")=="save_favorite"){ 
								
							Obj.fadeOut().empty().html("").fadeIn();
							Obj.attr('title', 'Save to Favorites');
						} else { 
							Obj.fadeOut().empty().html("Favorited").fadeIn();
							Obj.attr('title', 'Remove from Favorites');
						} 
					}, 2000);
				} else { 
					if(Obj.attr("class")=="save_favorite") { 
						Obj.empty().fadeOut().empty().html("").fadeIn();
						Obj.attr("class","favorited"); 
						Obj.attr('title', 'Remove from Favorites') 
					}else{ 
						Obj.empty().fadeOut().empty().html("").fadeIn();
						Obj.attr('title', 'Save to Favorites'); 
						jQuery(this).find("a[class=view_all_favorite]").remove();
						Obj.attr("class","save_favorite");
						<?php  if(get_query_var( 'type' )=="favorite" || get_query_var( 'type' )=="castingcart"){ 
						$rb_agency_options_arr = get_option('rb_agency_options');
						$rb_agency_option_layoutprofilelist = $rb_agency_options_arr['rb_agency_option_layoutprofilelist'];  ?> 
						if(jQuery("input[type=hidden][name=favorite]").val() == 1){ 
							Obj.closest("div[class=profile-list-layout0]").fadeOut();} <?php }?>
					}
				}}})});});
		</script>
		<!--END RB Agency Favorite -->

		<!-- [class=profile-list-layout<?php echo (int)$rb_agency_option_layoutprofilelist; ?>]-->
		<?php
	}

	if($rb_agency_option_profilelist_favorite){
		 add_action('wp_footer', 'rb_agency_save_favorite_javascript');
	 	 add_action('wp_ajax_rb_agency_save_favorite', 'rb_agency_save_favorite');
	}

//****************************************************************************************************//
// Add / Handles Ajax Request ===== Add To Casting Cart

	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_profilelist_castingcart  = isset($rb_agency_options_arr['rb_agency_option_profilelist_castingcart']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_castingcart'] : 0;
	
	function rb_agency_save_castingcart() {
				global $wpdb;
			
				if(is_user_logged_in()){ 
					if(isset($_POST["talentID"])){ 
						$query_castingcart = mysql_query("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID='".$_POST["talentID"]."'  AND CastingCartProfileID = '".rb_agency_get_current_userid()."'" ) or die("error");
						$count_castingcart = mysql_num_rows($query_castingcart);
						$datas_castingcart = mysql_fetch_assoc($query_castingcart);
						 
						if($count_castingcart<=0){ //if not exist insert favorite!
							$wpdb->insert(table_agency_castingcart, array('CastingCartProfileID'=>rb_agency_get_current_userid(), 'CastingCartTalentID'=>$_POST["talentID"]));
						} else { // favorite model exist, now delete!
							mysql_query("DELETE FROM  ". table_agency_castingcart."  WHERE CastingCartTalentID='".$_POST["talentID"]."'  AND CastingCartProfileID = '".rb_agency_get_current_userid()."'") or die("error");
						}
					}
				}
				else {
					echo "not_logged";
				}
				die();
			}
		
		function rb_agency_save_castingcart_javascript() {
		?>
				<!--RB Agency CastingCart -->
				<script type="text/javascript" >
					jQuery(document).ready(function($) { 
						$("div[class=castingcart] a").click(function(){
							var Obj = $(this);jQuery.ajax({type: 'POST',url: '<?php echo admin_url('admin-ajax.php'); ?>',data: {action: 'rb_agency_save_castingcart',  'talentID': $(this).attr("id")},
								success: function(results) {   
									if(results=='error'){ Obj.fadeOut().empty().html("Error in query. Try again").fadeIn();  } 
									else if(results==-1){ Obj.fadeOut().empty().html("<span style=\"color:red;font-size:11px;\">You're not signed in.</span><a href=\"<?php echo get_bloginfo("wpurl"); ?>/profile-member/\">Sign In</a>.").fadeIn();  
											setTimeout(function() {   
												if(Obj.attr("class")=="save_castingcart"){  
													Obj.fadeOut().empty().html("").fadeIn();
												}else{  
													Obj.fadeOut().empty().html("").fadeIn();  } 
											 }, 2000);  }
									else{ if(Obj.attr("class")=="save_castingcart"){
										Obj.empty().fadeOut().html("").fadeIn();  
										Obj.attr("class","saved_castingcart"); 
										Obj.attr('title', 'Remove from Casting Cart'); 
									} else { 
									Obj.empty().fadeOut().html("").fadeIn();  
									Obj.attr("class","save_castingcart"); 
									Obj.attr('title', 'Add to Casting Cart');   
									$(this).find("a[class=view_all_castingcart]").remove();  
									<?php  if(get_query_var( 'type' )=="favorite" || get_query_var( 'type' )=="castingcart"){  
												$rb_agency_options_arr = get_option('rb_agency_options'); 
												$rb_agency_option_layoutprofilelist = $rb_agency_options_arr['rb_agency_option_layoutprofilelist']; ?> 
												if($("input[type=hidden][name=castingcart]").val() == 1){
													Obj.closest("div[class=profile-list-layout0]").fadeOut();  } <?php } ?> } }}}) });});</script>
				 <!--END RB Agency CastingCart -->
		<?php
		}
	if(isset($rb_agency_option_profilelist_castingcart)){
		add_action('wp_ajax_rb_agency_save_castingcart', 'rb_agency_save_castingcart');
		add_action('wp_footer', 'rb_agency_save_castingcart_javascript');
	}

/*/
* ======================== Get ProfileID by UserLinkedID ===============
* @Returns ProfileID
/*/
function rb_agency_getProfileIDByUserLinked($ProfileUserLinked){
  
   	if(!empty($ProfileUserLinked)){
      	$query = mysql_query("SELECT ProfileID,ProfileUserLinked FROM ".table_agency_profile." WHERE ProfileUserLinked = ".$ProfileUserLinked." ");
      	$fetchID = mysql_fetch_assoc($query);
		return $fetchID["ProfileID"];
   	}
}

/*/
* ======================== Get Media Categories===============
* @Returns Media Categories
/*/
function rb_agency_getMediaCategories($GenderID){

	$query = mysql_query("SELECT MediaCategoryID,MediaCategoryTitle,MediaCategoryGender,MediaCategoryOrder FROM  ".table_agency_data_media." ORDER BY MediaCategoryOrder");
	$count = mysql_num_rows($query);
	while($f = mysql_fetch_assoc($query)){
		if($f["MediaCategoryGender"] == $GenderID || $f["MediaCategoryGender"] == 0){
			echo "<option value=\"".$f["MediaCategoryTitle"]."\">".$f["MediaCategoryTitle"]."</option>";	 
		}
	}
}

/*/
* ======================== Show/Hide Admin Toolbar===============
* 
/*/
function rb_agency_disableAdminToolbar() {
	add_filter('show_admin_bar', '__return_false');
}

$rb_agencyinteract_options_arr = get_option('rb_agencyinteract_options');
$rb_agencyinteract_option_profilemanage_toolbar =isset($rb_agencyinteract_options_arr["rb_agencyinteract_option_profilemanage_toolbar"]) ? (int)$rb_agencyinteract_options_arr["rb_agencyinteract_option_profilemanage_toolbar"] : 0;

if($rb_agencyinteract_option_profilemanage_toolbar==1) {
  	rb_agency_disableAdminToolbar(); 
}

/*/
* ======================== Edit Text/Label/Header ===============
* 
/*/
//add_filter( 'gettext', 'rb_agency_editTitleText', 10, 3 );
function rb_agency_editTitleText($string){
	return "<span>".$string."<a href=\"javascript:;\" style=\"font-size:11px;color:blue;text-decoration:underline;\">Edit</a></span>";  
}

/*/
*================ Add toolbar menu ==============================
*
/*/ 
function rb_agency_callafter_setup() {

	if (current_user_can('level_10') && !is_admin()) {
		function rb_agency_add_toolbar($wp_toolbar) {
			$wp_toolbar->add_node(array(
				'id' => 'rb-agency-toolbar-settings',
				'title' => 'RB Agency Settings',
				'href' =>  get_admin_url().'admin.php?page=rb_agency_settings',
				'meta' => array('target' => 'rb-agency-toolbar-settings')
			));
		}
	  	add_action('admin_bar_menu', 'rb_agency_add_toolbar', 999);
	}
}
add_action( 'after_setup_theme',"rb_agency_callafter_setup");

/*/
*  PHP Profiler DEBUG MODE
/*/ 
function rb_agency_checkExecution() {

	global $RB_DEBUG_MODE;

	if($RB_DEBUG_MODE == true){

		$start = microtime();
		echo "<div style=\"float:left;border:1px solid #ccc;background:#ccc !important; color:black!important;\">";
		echo "<pre >";
		for($i=100;$i>0;$i--) {
			echo $i;
			echo "\n";
		}

		$end = microtime();
		$parseTime = $end-$start;
		
		echo "-DEBUG MODE- Time Execution";
		echo "\n\n";
		echo $parseTime;
		echo "</pre>";
		
		$trace = debug_backtrace();
		$file   = $trace[$level]['file'];
		$line   = $trace[$level]['line'];
		$object = $trace[$level]['object'];
		
      	if (is_object($object)) { $object = get_class($object); }
		$result = var_export( $var, true );
        
        echo "\n<pre>Dump: $result</pre>";
		echo "\n<pre>";

		debug_print_backtrace();

		echo "\n\nWhere called: line $line of $object \n(in $file)";
		echo "</pre>";
		echo "</div>";
	}
}

/*/
 *   Profile Extend Social Links
/*/ 
function rb_agency_getSocialLinks(){

	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_showsocial = $rb_agency_options_arr['rb_agency_option_showsocial'];

	if($rb_agency_option_showsocial){
		echo "		<div class=\"social addthis_toolbox addthis_default_style\">\n";
		echo "			<a href=\"http://www.addthis.com/bookmark.php?v=250&amp;username=xa-4c4d7ce67dde9ce7\" class=\"addthis_button_compact\">". __("Share", rb_agency_TEXTDOMAIN). "</a>\n";
		echo "			<span class=\"addthis_separator\">|</span>\n";
		echo "			<a class=\"addthis_button_facebook\"></a>\n";
		echo "			<a class=\"addthis_button_myspace\"></a>\n";
		echo "			<a class=\"addthis_button_google\"></a>\n";
		echo "			<a class=\"addthis_button_twitter\"></a>\n";
		echo "		</div><script type=\"text/javascript\" src=\"http://s7.addthis.com/js/250/addthis_widget.js#username=xa-4c4d7ce67dde9ce7\"></script>\n";
	}
}

//get previous and next profile link
function linkPrevNext($ppage,$nextprev,$type="",$division=""){

	if($nextprev=="next") { $nPid=$pid+1; }
	else { $nPid=$pid-1; }
	
	$sql="SELECT ProfileGallery FROM ".table_agency_profile." WHERE 1 AND ProfileGender ='$type'  AND ProfileType ='1' ";
	
	//filter division 
	if($division=="/women/"){$ageStart=17;$ageLimit=99;}
	elseif($division=="/men/"){$ageStart=17;$ageLimit=99;}
	elseif($division=="/teen-girls/"){$ageStart=12;$ageLimit=27;}
	elseif($division=="/teen-boys/"){$ageStart=12;$ageLimit=17;}
	elseif($division=="/girls/"){$ageStart=1;$ageLimit=12;}
	elseif($division=="/boys/"){$ageStart=1;$ageLimit=12;}
 	$sql.="	AND DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(ProfileDateBirth)), '%Y')+0 > $ageStart
        	AND DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(ProfileDateBirth)), '%Y')+0 <=$ageLimit
			AND ProfileIsActive = 1
	";
	$tempSql=$sql;
	
	//end filter 
	if($nextprev=="next"){ 
			$sql.=" AND  ProfileGallery > '$ppage' ORDER BY ProfileContactNameFirst ASC"; // to get next record
	} else {
		$sql.=" AND  ProfileGallery < '$ppage' ORDER BY ProfileContactNameFirst DESC"; // to get next
	}
  
	$sql.=" LIMIT 0,1 ";
	$query = mysql_query($sql);
	$fetch = mysql_fetch_assoc($query);  
  
  	if(empty($fetch["ProfileGallery"])){ //make sure it wont send empty url

	  	if($nextprev=="next"){ 
  		 	$sql=$tempSql."  ORDER BY ProfileContactNameFirst ASC"; // to get next record
 	  	}else{
  	     	$sql=$tempSql."  ORDER BY ProfileContactNameFirst DESC"; // to get next
      	}
	  
     	$sql.=" LIMIT 0,1 ";
	 	$query = mysql_query($sql);
     	$fetch = mysql_fetch_assoc($query);
  	}
		 
	return  $fetch["ProfileGallery"];
}

function getExperience($pid){ 
	$query = mysql_query("SELECT ProfileCustomValue FROM ".table_agency_customfield_mux." WHERE ProfileID = '".$pid."' AND ProfileCustomID ='16' ");
    $fetch = mysql_fetch_assoc($query);
    
    
    return  $fetch["ProfileCustomValue"];
}

function checkCart($currentUserID,$pid){
  	$query="SELECT * FROM  ".table_agency_castingcart." WHERE CastingCartProfileID='".$currentUserID."' AND CastingCartTalentID='".$pid."' ";
	$results = mysql_query($query) or die ( __("Error, query failed", rb_agency_TEXTDOMAIN ));
	return mysql_num_rows($results);
}

/* function that lists users for generating login/password */
function rb_display_profile_list(){  
    global $wpdb;
    $rb_agency_options_arr = get_option('rb_agency_options');
    $rb_agency_option_locationtimezone 		= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];

    echo "<div class=\"wrap\">\n";
    echo "  <h3 class=\"title\">". __("Profiles List", rb_agency_TEXTDOMAIN) ."</h3>\n";
		
    // Sort By
    $sort = "";
    if (isset($_GET['sort']) && !empty($_GET['sort'])){
        $sort = $_GET['sort'];
    }
    else {
        $sort = "profile.ProfileContactNameFirst";
    }
		
    // Sort Order
    $dir = "";
    if (isset($_GET['dir']) && !empty($_GET['dir'])){
        $dir = $_GET['dir'];
        if ($dir == "desc" || !isset($dir) || empty($dir)){
            $sortDirection = "asc";
        } else {
            $sortDirection = "desc";
        } 
    } else {
       $sortDirection = "desc";
       $dir = "asc";
    }
  	
    // Filter
    $filter = "WHERE profile.ProfileIsActive IN (0,1,4) ";
    if ((isset($_GET['ProfileContactNameFirst']) && !empty($_GET['ProfileContactNameFirst'])) || isset($_GET['ProfileContactNameLast']) && !empty($_GET['ProfileContactNameLast'])){
        if (isset($_GET['ProfileContactNameFirst']) && !empty($_GET['ProfileContactNameFirst'])){
                $selectedNameFirst = $_GET['ProfileContactNameFirst'];
                $query .= "&ProfileContactNameFirst=". $selectedNameFirst ."";
                $filter .= " AND profile.ProfileContactNameFirst LIKE '". $selectedNameFirst ."%'";
        }
        if (isset($_GET['ProfileContactNameLast']) && !empty($_GET['ProfileContactNameLast'])){
                $selectedNameLast = $_GET['ProfileContactNameLast'];
                $query .= "&ProfileContactNameLast=". $selectedNameLast ."";
                $filter .= " AND profile.ProfileContactNameLast LIKE '". $selectedNameLast ."%'";
        }
    }
    if (isset($_GET['ProfileLocationCity']) && !empty($_GET['ProfileLocationCity'])){
            $selectedCity = $_GET['ProfileLocationCity'];
            $query .= "&ProfileLocationCity=". $selectedCity ."";
            $filter .= " AND profile.ProfileLocationCity='". $selectedCity ."'";
    }
    if (isset($_GET['ProfileType']) && !empty($_GET['ProfileType'])){
            $selectedType = $_GET['ProfileType'];
            $query .= "&ProfileType=". $selectedType ."";
            $filter .= " AND profiletype.DataTypeID='". $selectedType ."'";
    }
    if (isset($_GET['ProfileVisible']) && !empty($_GET['ProfileVisible'])){
            $selectedVisible = $_GET['ProfileVisible'];
            $query .= "&ProfileVisible=". $selectedVisible ."";
            $filter .= " AND profile.ProfileIsActive='". $selectedVisible ."'";
    }
    if (isset($_GET['ProfileGender']) && !empty($_GET['ProfileGender'])){
            $ProfileGender = (int)$_GET['ProfileGender'];
            if($ProfileGender)
                    $filter .= " AND profile.ProfileGender='".$ProfileGender."'";
    }
		
    //Paginate
    $items = mysql_num_rows(mysql_query("SELECT * FROM ". table_agency_profile ." profile LEFT JOIN ". table_agency_data_type ." profiletype ON profile.ProfileType = profiletype.DataTypeID ". $filter  ."")); // number of total rows in the database
    if($items > 0) {
        $p = new rb_agency_pagination;
        $p->items($items);
        $p->limit(50); // Limit entries per page
        $p->target("admin.php?page=". $_GET['page'] . '&ConfigID=99' . $query);
        $p->currentPage($_GET[$p->paging]); // Gets and validates the current page
        $p->calculate(); // Calculates what to show
        $p->parameterName('paging');
        $p->adjacents(1); //No. of page away from the current page

        if(!isset($_GET['paging'])) {
                $p->page = 1;
        } else {
                $p->page = $_GET['paging'];
        }

        //Query for limit paging
        $limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
    } else {
        $limit = "";
    }
    
    /* Top pagination */
    echo "<div class=\"tablenav\">\n";
    echo "  <div class=\"tablenav-pages\">\n";
    
    if($items > 0) {
        echo $p->show();  // Echo out the list of paging. 
    }
    echo "  </div>\n";
    echo "</div>\n";
    /* End Top pagination */
     
    /* Table Content */
		    ?>
		<a class="button-secondary" id="bulk_generate" href="javascript:void(0)" style="margin-bottom: 5px" title="Generate" disabled="disabled">Generate</a>
		<a class="button-primary"  id="bulk_send_email" href="javascript:void(0)" style="margin-left: 5px" title="Send Email" disabled="disabled">Send Email</a>
		<a class="button-primary"  id="open_popup" href="javascript:void(0)" style="margin-left: 5px;float: right" title="Send Email">Edit Email Content</a>
		<div id="ch_bulk" style="float: none !important;margin-left: 10px;width: 34px;display: inline-block;position: relative;top: 7px;margin-top: -15px;"></div>
		<table cellspacing="0" class="widefat fixed">
		  <thead>
		    <tr class="thead">
		      <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
		      <th style="width:50px;"><a href="<?php echo admin_url("admin.php?page=". $_GET['page'] ."&ConfigID=99&sort=ProfileID&dir=". $sortDirection) ?>">ID</a></th>
		      <th style="width:250px;"><a href="<?php echo admin_url("admin.php?page=". $_GET['page'] ."&ConfigID=99&sort=ProfileContactNameFirst&dir=". $sortDirection) ?>">First Name</a></th>
		      <th style="width:250px;"><a href="<?php echo admin_url("admin.php?page=". $_GET['page'] ."&ConfigID=99&sort=ProfileContactNameLast&dir=". $sortDirection) ?>">Last Name</a></th>
		      <th style="width:250px;"><a href="<?php echo admin_url("admin.php?page=". $_GET['page'] ."&ConfigID=99&sort=ProfileGender&dir=". $sortDirection) ?>">Email Addresses</a></th>
		      <th style="width:100px;"></th>
		      <th style="width:100px;"></th>
		      <th></th>
		    </tr>
		  </thead>
		 <tbody>

		<?php
		$query = "SELECT * FROM ". table_agency_profile ." profile LEFT JOIN ". table_agency_data_type ." profiletype ON profile.ProfileType = profiletype.DataTypeID ". $filter  ." ORDER BY $sort $dir $limit";
		$results2 = mysql_query($query);
		$count = mysql_num_rows($results2);
		$i = 0;
		while ($data = mysql_fetch_array($results2)) {

		  $ProfileID = $data['ProfileID'];
		  $ProfileContactNameFirst = stripslashes($data['ProfileContactNameFirst']);
		  $ProfileContactNameLast = stripslashes($data['ProfileContactNameLast']);
		  $ProfileContactEmail = RBAgency_Common::format_propercase(stripslashes($data['ProfileContactEmail']));
		  
		  //get wp user info
		  $user_info = get_user_meta($data['ProfileUserLinked'],'user_login_info',true);
      	  $userlogin="";
		  $userpass="";
		  if($user_info){
			  $user_info = unserialize($user_info);	
			  $userlogin = $user_info[0];
			  $userpass = $user_info[1];
		  }
		  $i++;
		  if ($i % 2 == 0) {
		          $rowColor = " style='background: #fcfcfc'"; 
		  } else {
		          $rowColor = " "; 
		  } ?>
		  <tr <?php echo $rowColor ?>>
		    <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $ProfileID ?>" id="<?php echo $ProfileID ?>" data-firstname="<?php echo $ProfileContactNameFirst ?>" data-lastname="<?php echo $ProfileContactNameLast ?>" data-email="<?php echo $ProfileContactEmail ?>" class="administrator"  name="<?php echo $ProfileID ?>"/></th>
		    <td><?php echo $ProfileID ?></td>
		    <td><?php echo $ProfileContactNameFirst ?></td>
		    <td><?php echo $ProfileContactNameLast ?></td>
		    <td><?php echo $ProfileContactEmail ?></td>
		    <td><a href="javascript:void(0)" class="generate_lp button-secondary" data-id="<?php echo $ProfileID ?>" data-firstname="<?php echo $ProfileContactNameFirst ?>" data-lastname="<?php echo $ProfileContactNameLast ?>">Generate</a></td>
		    <td><a href="javascript:void(0)" class="email_lp button-primary" disabled="disabled" data-id="<?php echo $ProfileID ?>" id="em_<?php echo $ProfileID ?>" data-email="<?php echo $ProfileContactEmail ?>">Send Email</a></td>
		    <td>
		      <div id="ch_<?php echo $ProfileID ?>"></div>
		      <input id="l_<?php echo $ProfileID ?>" type="text" placeholder="Login" value="<?php echo (!empty($userlogin)) ? $userlogin : ""; ?>" /><br />
		      <input id="p_<?php echo $ProfileID ?>" type="text" placeholder="Password" value="<?php echo (!empty($userpass)) ? $userpass : "";  ?>" />         
		    </td>
		  </tr>
		  <?php
		}

		mysql_free_result($results2);
		if ($count < 1) {
		  if (isset($filter)) { 
		?>
		    <tr>
		      <th class="check-column" scope="row"></th>
		      <td class="name column-name" colspan="5">
		        <p>No profiles found with this criteria.</p>
		      </td>
		    </tr>
		<?php
		  } else {
		?>

		    <tr>
		      <th class="check-column" scope="row"></th>
		      <td class="name column-name" colspan="5">
		        <p>There aren't any profiles loaded yet!</p>
		      </td>
		    </tr>
		<?php
		  }
		} 
		?>
		     
		 </tbody>
		  <tfoot>
		    <tr class="thead">
		      <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox" /></th>
		      <th class="column" scope="col">ID</th>
		      <th class="column" scope="col">First Name</th>
		      <th class="column" scope="col">Last Name</th>
		      <th class="column" scope="col">Email Address</th>
		      <th class="column" scope="col"></th>
		      <th class="column" scope="col"></th>
		      <th class="column" scope="col"></th>
		    </tr>
		  </tfoot>
		</table>
		<div id="popup">
		    <span class="popup_close" title="Close">x</span>
		    <span class="popup_title">Use following shortcodes to put constructions in text</span><br />
		    <code>[login]</code>, <code>[password]</code>, <code>[url]</code><br />
		    <textarea id="emailContent" style="width:100%; height:200px"></textarea><br/>
		    <input id="saveEmailContent" type="button" value="Save" class="button-primary" style="float:right;"/>
		</div>
		<div id="popup_bg"></div>
		<script type="text/javascript">

		jQuery(document).ready(function($){
		  $('#open_popup').click(function(){
		    $('#popup_bg').fadeIn('fast',function(){
		      $('#popup').fadeIn('fast');
		      
		      // get custom email message, if defined
		      $.ajax({
		        url: ajaxurl,
		        type: 'get',
		        data: { 
		          action: 'read_email_cnt'
		        },
		        success: function(data){
		          if(data != 'empty'){
		            $('#emailContent').val(data);
		          }
		          else {
		            $('#emailContent').val('Hello, we generated new login and password for you at RB Agency\n\n[login]\n[password]\n\nYou can [url] here\n\nThanks.');
		          }
		        }
		      });
		    });
		  });
		    
		  $('#popup_bg, .popup_close').click(function(){
		    $('#popup').fadeOut('fast',function(){
		      $('#popup_bg').fadeOut('fast');
		    });
		  });
		    
		  $('.generate_lp').click(function(){
		    var pid = $(this).attr('data-id');
		    var pfname = $(this).attr('data-firstname');
		    var plname = $(this).attr('data-lastname');
		    
		    var lp_arr = generateLP(pid, pfname, plname);

		    var login = lp_arr[0];
		    var password = lp_arr[1];
		    
		    $('#l_' + pid).val(login);
		    $('#p_' + pid).val(password);
		    $('#em_' + pid).removeAttr('disabled');
		    $('#em_' + pid).bind('click', sendEmail);
		    $('#ch_' + pid).addClass('pending-profile');
		  });
		   
		   
		  $('#cb input[type=checkbox], .administrator').click(function(){ 
		    if($(this).is(':checked')){
		      $('#bulk_generate').removeAttr('disabled');
		      $('#bulk_generate').unbind('click').bind('click', bulkGenerateLP);
		    }
		    else { 
		      if($('.administrator:checked').length == 0 || $('.administrator:checked').length == 50)
		      $('#bulk_generate').attr('disabled', 'disabled');
		    }
		  });

		   
		  // saving custom email message
		  $('#saveEmailContent').click(function(){
		    var emailContent = $('#emailContent').val();
		    if(emailContent){
		      $.ajax({
		        url: ajaxurl,
		        type: 'post',
		        data: { 
		          action: 'write_email_cnt',
		          email_message: emailContent 
		        },
		        success: function(){
		          $('#popup').fadeOut('fast',function(){
		            $('#popup_bg').fadeOut('fast');
		          });
		        }
		      });
		    }
		  });
		      
		   
		  function generateLP(pid, pfname, plname){
		    var login = pfname.toLowerCase().substr(0, 5).replace(' ', '-') + pid + plname.toLowerCase().substr(-3, 3);
		    var password = generatepass(login);
		     
		    return [login, password];
		  }

		  function bulkGenerateLP(){
		    if($('.administrator:checked').length > 0){ 
		      $('#ch_bulk').removeClass();
		      $('.administrator:checked').each(function(){
		        var pid = $(this).attr('id');
		        var pfname = $(this).attr('data-firstname');
		        var plname = $(this).attr('data-lastname');

		        var lp_arr = generateLP(pid, pfname, plname);

		        var login = lp_arr[0];
		        var password = lp_arr[1];

		        $('#l_' + pid).val(login);
		        $('#p_' + pid).val(password);
		        $('#em_' + pid).removeAttr('disabled');
		        $('#em_' + pid).bind('click', sendEmail);
		        $('#ch_' + pid).addClass('pending-profile');
		      });
		         
		      $('#bulk_send_email').removeAttr('disabled');
		    }
		     
		    $('#bulk_send_email').unbind('click').bind('click', bulkSendEmail);
		  }

		  function sendEmail(){
		    var pid = $(this).attr('data-id');

		    var login = $('#l_' + pid).val();
		    var password = $('#p_' + pid).val();
		    var email = $(this).attr('data-email').toLowerCase();

		    if(login && password && email){
		      $('#ch_' + pid).removeClass('pending-profile').addClass('loading-profile');
		      $.ajax({
		        url: ajaxurl, // pointed to admin-ajax.php
		        type: 'post',
		        data: {
		          action: 'send_mail',
		          profileid : pid,
		          login : login,
		          password : password,
		          email : email
		        },
		        success: function(data){
		          if(data == 'SUCCESS'){
		            $('#ch_' + pid).removeClass('loading-profile').addClass('checked-profile');
		            $('#l_' + pid).attr('disabled', 'disabled');
		            $('#p_' + pid).attr('disabled', 'disabled');
		            $('#em_' + pid).attr('disabled', 'disabled');
		            $('#em_' + pid).unbind('click');
		          }
		          else {
		            $('#ch_' + pid).removeClass('loading-profile').addClass('error-profile');
		            alert(data);
		            return false;
		          }
		        }
		      });
		    }
		    else {
		       alert("Please Generate Login / Password, then send!");
		       return false;
		    }
		  }
		    
		  function bulkSendEmail(){ 
		    var usersLP = {};
		    if($('.administrator:checked').length > 0){ 
		        $('#ch_bulk').removeClass().addClass('loading-profile');
		        $('.administrator:checked').each(function(){
		          var pid = $(this).attr('id');
		          var login = $('#l_' + pid).val();
		          var password = $('#p_' + pid).val();
		          var email = $(this).attr('data-email');

		          usersLP[pid] = {
		            pid : pid,
		            login : login,
		            password : password,
		            email : email
		          };                         
		      });
		       
		      //console.log(usersLP);       
		      $.ajax({
		        url: ajaxurl, // pointed to admin-ajax.php
		        type: 'post',
		        data: {
		          action: 'send_bulk_mail',
		          users_pl : usersLP
		        },
		        success: function(data){
		          if(data == 'SUCCESS'){
		            $('#ch_bulk').removeClass().addClass('checked-profile');
		            $('.administrator:checked').each(function(){
		              var pid = $(this).attr('id');
		              $('#ch_' + pid).removeClass();
		            });
		          }
		          else {
		            $('#ch_bulk').removeClass().addClass('error-profile');
		          }
		        }
		      });
		    }
		  }   
		});

		var keylist = "abcdefghijklmnopqrstuvwxyz123456789";

		function generatepass(str){
		  var temp = '';
		  for (i=0; i < str.length; i++)
		    temp += keylist.charAt(Math.floor(Math.random() * keylist.length));
		  return temp;
		}
		</script>
	<?php
    /* End Table Content */

    /* Bottom pagination */
    echo "<div class=\"tablenav\">\n";
    echo "  <div class='tablenav-pages'>\n";

    if($items > 0) {
            echo $p->show();  // Echo out the list of paging. 
    }

    echo "  </div>\n";
    echo "</div>\n";
    /*End Bottom pagination */
    
    echo "</div>";

}

add_action('wp_ajax_send_mail', 'register_and_send_email');

function register_and_send_email(){ 
    global $wpdb;
    $profileid = (int)$_POST['profileid'];
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // getting required fileds from rb_agency_profile
    $profile_row = $wpdb->get_results( "SELECT ProfileID, ProfileContactDisplay, ProfileContactNameFirst, ProfileContactNameLast FROM ". table_agency_profile ." WHERE ProfileID = '" . $profileid . "'" );

    // creating new user
    $user_id = username_exists( $profile_row[0]->ProfileContactDisplay );
    if ( !$user_id and email_exists($user_email) == false ) {
        $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        $user_id = wp_create_user( $login, $random_password, $email ); 
        if(is_numeric($user_id)){
            wp_set_password( $password, $user_id );

            // updating some information we have in wp_users
            $wpdb->update( 
                    'wp_users', 
                    array( 'display_name' => $profile_row[0]->ProfileContactDisplay ), 
                    array( 'ID' => $user_id ), 
                    array( '%s' ), 
                    array( '%d' ) 
            );

            // inserting some information we have in wp_usermeta
            update_user_meta( $user_id, 'first_name', $profile_row[0]->ProfileContactNameFirst );
            update_user_meta( $user_id, 'last_name', $profile_row[0]->ProfileContactNameLast );
			
			// to store plain text login info
			$user_info_arr = serialize(array($login,$password));
			update_user_meta( $user_id, 'user_login_info', $user_info_arr);
			
			// linking the user ID with profile ID
			$wpdb->update(table_agency_profile,
				array( 'ProfileUserLinked' => $user_id ),
				array( 'ProfileID' => $profile_row[0]->ProfileID ),
				array( '%d' ),
				array( '%d' )
			);

            send_email_lp($login, $password, $email);

            echo 'SUCCESS';
        } else {
            echo $user_id->errors['existing_user_login'][0];
        }

    } else {
        echo 'The user is already registrated!';
    }
    
    die;
}

add_action('wp_ajax_send_bulk_mail', 'bulk_register_and_send_email');

function bulk_register_and_send_email(){
    global $wpdb;
    
    $users_lp = $_POST['users_pl'];
    //echo '<pre>'; print_r($users_lp);
    
    $success = FALSE;
    
    foreach($users_lp as $user_lp){ 
        $profile_row = $wpdb->get_results( "SELECT ProfileID, ProfileContactDisplay, ProfileContactNameFirst, ProfileContactNameLast FROM ". table_agency_profile ." WHERE ProfileID = '" . $user_lp['pid'] . "'");
        
        $user_id = username_exists( $profile_row[0]->ProfileContactDisplay );
        if ( !$user_id and email_exists($user_email) == false ) { 
            $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
            $user_id = wp_create_user( $user_lp['login'], $random_password, $user_lp['email'] ); 
            if(is_numeric($user_id)){ 
                wp_set_password( $user_lp['password'], $user_id );

                // updating some information we have in wp_users
                $wpdb->update( 
                        'wp_users', 
                        array( 'display_name' => $profile_row[0]->ProfileContactDisplay ), 
                        array( 'ID' => $user_id ), 
                        array( '%s' ), 
                        array( '%d' ) 
                );

                // inserting some information we have in wp_usermeta
                update_user_meta( $user_id, 'first_name', $profile_row[0]->ProfileContactNameFirst );
                update_user_meta( $user_id, 'last_name', $profile_row[0]->ProfileContactNameLast );
				
				// to store plain text login info
				$user_info_arr = serialize(array($user_lp['login'], $user_lp['password']));
				update_user_meta( $user_id, 'user_login_info', $user_info_arr);
				
				// linking the user ID with profile ID
				$wpdb->update( 
					table_agency_profile,
					array( 'ProfileUserLinked' => $user_id ),
					array( 'ProfileID' => $profile_row[0]->ProfileID ),
					array( '%d' ),
					array( '%d' )
				);
                
                send_email_lp($user_lp['login'], $user_lp['password'], $user_lp['email']);
                
                $success = TRUE;
                
            } else {
                //print_r($user_id);
            }
        }        
    }
    
    if($success){
        echo 'SUCCESS';
    }
    
    die;    
}

function send_email_lp($login, $password, $email){
    $admin_email = get_bloginfo('admin_email');

    $headers = 'From: RB Agency <' . $admin_email . '>\r\n';

    $subject = 'Your new Login and Password';
    
    $message = read_email_content(true);
    if($message == 'empty'){
        $message = 'Hello, we generated new login and password for you at RB Agency\n\n[login]\n[password]\n\nYou can login [url]\n\nThanks.';
    }

    $message = str_replace('[login]', 'Login: <strong>' . $login . '</strong>', $message);
    $message = str_replace('[password]', 'Password: <strong>' . $password . '</strong>', $message);
    $message = str_replace('[url]', '<a href="' . site_url('profile-login') . '">login</a>', $message);
    
    $message = nl2br($message);

    add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
    wp_mail($email, $subject, $message, $headers);
    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
    
}

add_action('wp_ajax_write_email_cnt', 'write_email_content');

function write_email_content(){
    $email_message = $_POST['email_message'];
    update_option( 'rb_email_content', $email_message );
    die;
}

add_action('wp_ajax_read_email_cnt', 'read_email_content');

function read_email_content($ret = false){ 
    if($ret){
        return $email_message = get_option( 'rb_email_content', 'empty' );
    }
    else {
        echo $email_message = get_option( 'rb_email_content', 'empty' );
    }
    
    die;
}

/*
 * Function to retrieve
 * featured widget profile
 *
 * @parm: none
 * @return:  		
 * Profile Name = array[0];
 * Gender = array[1];
 * Custom Fields = array[2];
 * Gallery Folder = array[3];
 * Profile Pic URL = array[4];
 */
function featured_homepage(){
	            
				global $wpdb;
				
				/*
				 * Get details for profile
				 * featured
				 */
				$count = 1;
				 
				$q = "SELECT profile.*,
		
				(SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media 
				 WHERE profile.ProfileID = media.ProfileID 
				 AND media.ProfileMediaType = \"Image\" 
				 AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL 
				
				 FROM ". table_agency_profile ." profile 
				 WHERE profile.ProfileIsActive = 1 ".(isset($sql) ? $sql : "") ."
				 AND profile.ProfileIsFeatured = 1  
				 ORDER BY RAND() LIMIT 0,$count";						

				$r = mysql_query($q);
				
				$countList = mysql_num_rows($r);
				
				$array_data = array();
				
				while ($dataList = mysql_fetch_assoc($r)) {
					
					/*
					 * Get From Custom Fields
					 * per profile
					 */
					$get_custom = 'SELECT * FROM ' . table_agency_customfield_mux .
								  ' WHERE ProfileID = ' . $dataList["ProfileID"]; 
								  
					$result = mysql_query($get_custom);
					
					$desc_list = array('shoes', 'eyes', 'shoes', 'skin');
					
					$a_male = array('height','weight','waist', 'skin tone',
									'eye color',  'shoe size', 'shirt');
					
					$array_male = array();
					$array_female = array();

					$a_female = array('bust', 'waist', 'hips', 'dress',
										  'shoe size','hair', 'eye color');
                    
					$name = ucfirst($dataList["ProfileContactNameFirst"]) ." ". strtoupper($dataList["ProfileContactNameLast"][0]); ;
					
					while ($custom = mysql_fetch_assoc($result)) {
                         
						 $get_title = 'SELECT ProfileCustomTitle FROM ' . table_agency_customfields .
						 ' WHERE ProfileCustomID = ' . $custom["ProfileCustomID"] ; 
						 
						 $result2 = mysql_query($get_title);
						 
						 $custom2 = mysql_fetch_assoc($result2);
						 
						 if(strtolower(RBAgency_Common::profile_meta_gendertitle($dataList['ProfileGender'])) == "male"){
							 
							 if(in_array(strtolower($custom2['ProfileCustomTitle']),$a_male)){
								 $array_male[$custom2['ProfileCustomTitle']] = $custom['ProfileCustomValue'];
							 }
						 
						 } else if(strtolower(RBAgency_Common::profile_meta_gendertitle($dataList['ProfileGender'])) == "female"){
							 
							 if(in_array(strtolower($custom2['ProfileCustomTitle']),$a_female)){
								 $array_female[$custom2['ProfileCustomTitle']] = $custom['ProfileCustomValue'];
							 }				 
						 }
					
					}
					
					if(strtolower(RBAgency_Common::profile_meta_gendertitle($dataList['ProfileGender'])) == "male"){
						
						$array_data = array($name,'male',$array_male,$dataList["ProfileGallery"],$dataList["ProfileMediaURL"]);
						
					} else if(strtolower(RBAgency_Common::profile_meta_gendertitle($dataList['ProfileGender'])) == "female"){
						
						$array_data = array($name,'female',$array_female,$dataList["ProfileGallery"],$dataList["ProfileMediaURL"]);
								 
					}
					
				}
	
	return $array_data;
	
}

// *************************************************************************************************** //
/*
 *  Shortcodes
 */
	// Search Form
	function rb_agency_searchform($DataTypeID) {
		$profilesearch_layout = "simple";
		include("theme/include-profile-search.php");
	}

// 5/15/2013 sverma@ Home page
function featured_homepage_profile($count){
	global $wpdb;
	$dataList = array();
	$query = "SELECT profile.*,
	(SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media 
	 WHERE profile.ProfileID = media.ProfileID 
	 AND media.ProfileMediaType = \"Image\" 
	 AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL 

	 FROM ". table_agency_profile ." profile 
	 WHERE profile.ProfileIsActive = 1 ".(isset($sql) ? $sql : "") ."
	 AND profile.ProfileIsFeatured = 1  
	 ORDER BY RAND() LIMIT 0,".$count;						

	$result = mysql_query($query);
	$i=0;
	while ($row = mysql_fetch_assoc($result)) {
		$dataList[$i] = $row ;
		$i++;
	}
	return $dataList ;

}

function primary_class(){
	return $class = "col_8";
}

function secondary_class(){
	return $class = "col_4";
}

function fullwidth_class(){
	return $class = "col_12";
}
/*
 * recreate custom field search
 */
function recreate_custom_search($GET){
// TODO REMOVED


}

/*
 * load script for printing profile pdf 
 */
function rb_load_profile_pdf($row = 0, $logo = NULL){?>
	
<!-- ajax submit login -->
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#print_pr_pdf').on('click', function(){
		jQuery(this).text("PRINTING...");
		jQuery.ajax({
			type: 'POST',
			dataType: 'html',
			url: '<?php echo plugins_url("rb-agency/theme/print_profile_pdf.php"); ?>',
			data: { 
				pid : <?php echo get_current_profile_info("ProfileID"); ?>, 
				row : <?php echo $row; ?>, 
				logo : "<?php echo $logo; ?>"},
			success: function(response){
				if (response){
				        jQuery('#print_pr_pdf').text("PRINT PDF");
					var lnk = response;
					var st = lnk.indexOf("http");
					lnk =  lnk.substring(st);
					document.location.href = lnk;
				} else {
					jQuery(this).text("ERROR...");
				}
			}
		});
	 });
 });
 </script>        
 
<?php        
}

/*
 * current profile. 
 * this is for files when profile ID is not available
 * specially "header files"
 * to get the current profile info not user info
 * TODO, should also return any info needed for profiles
 */
function get_current_profile_info($data=NULL){
        
        global $wpdb;
    
        $uri = $_SERVER['REQUEST_URI'];

        if(!rb_is_page("rb_profile")) return false;
        
        $contact = strpos($uri,"/profile/");
        $contact = substr($uri,$contact+9);
        $contact = str_replace("/","",$contact);
        
        $query = "SELECT * FROM " . table_agency_profile .
                 " WHERE ProfileGallery = '" . $contact . "'";
        
        $contact = $wpdb->get_results($query);
        
        if(count($contact)>0){
            foreach($contact as $c){
                if(is_null($data)){
                	return ucwords(str_replace("-"," ",$c->ProfileContactDisplay));
		} else {
			return $c->$data;
		}
            }
        }
        
        return false;
        
}

/*
* Get primary image for profiles
*/
function rb_get_primary_image($PID){
	
	if(empty($PID) or is_null($PID)) return false;
	
	$get_image = "SELECT ProfileMediaURL FROM ". table_agency_profile_media .
				 " WHERE ProfileID = " .$PID . " AND ProfileMediaPrimary = 1";
	
	$get_res = mysql_query($get_image);
	
	if(mysql_num_rows($get_res) > 0){
		while($data = mysql_fetch_assoc($get_res)){
			return $data['ProfileMediaURL'];
		}
	}			
	
	return false;
}
/*
* check page
*/
function rb_is_page($page){

        if(empty($page)){ return false; }
	
	$uri = $_SERVER['REQUEST_URI'];

	if((strpos($uri,"/profile/") > -1 && $page == "rb_profile" ) ||
           (strpos($uri,"/dashboard/") > -1 && $page == "rb_dashboard") ||
           (strpos($uri,"/profile-category/") > -1 && $page == "rb_category") ||
           (strpos($uri,"/profile-register/") > 1 && $page == "rb_register") ||
           (strpos($uri,"/profile-search/") > -1 ||
			  strpos($uri,"/search/") > -1 ||
			  strpos($uri,"/search") > -1 &&
                          $page == "rb_search")	||
           (strpos($uri,"/profile-print/") > -1 && $page == "rb_print") ||
           (strpos($uri,"/profile-casting/") > -1 && $page == "rb_casting") ||
           (strpos($uri,"/profile-favorites/") > -1 && $page == "rb_favorites" )) {

            return true;	
            
        } 

        return false;
}
/*
 *	Rb Agency login checker 
 */
function rb_agency_log(){
    check_ajax_referer( 'ajax-login-nonce', 'security' );
    $login_info = array();
    $login_info['user_login'] = $_POST['username'];
    $login_info['user_password'] = $_POST['password'];
    $login_info['remember'] = true;
    $user_login = wp_signon( $login_info, false );
    if ( is_wp_error($user_login) ){
       echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
       echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }
    die();
}
/*
 * Add action hook if interact is inactive
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if(!is_plugin_active(ABSPATH . 'wp-content/plugins/rb-agency-interact/rb-agency-interact.php')){
    add_action('wp_ajax_rb_agency_log', 'rb_agency_log');
    add_action('wp_ajax_nopriv_rb_agency_log', 'rb_agency_log');
    add_action('wp_logout','redirect_to_home');
	function redirect_to_home(){
		wp_redirect(home_url());
		exit();
	}
} 

/*
 * WP Login Form
 * TODO REMOVE
 */
function rb_loginform($redirect){?>
		<!-- ajax submit login -->
		<script type="text/javascript">
        jQuery(document).ready(function(){
		    // Perform AJAX login on form submit
			jQuery('#submit_login').on('click', function(){
				jQuery('#rbsign-in p.status').show().text("logging in...");
				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data: { 
						'action': 'rb_agency_log', 
						'username': jQuery('#username').val(), 
						'password': jQuery('#password').val(), 
						'security': jQuery('#security').val() },
					success: function(data){
						if (data.loggedin == true){
							document.location.href = "<?php echo $redirect; ?>";
						} else {
                                                    jQuery('#rbsign-in p.status').text(data.message);
						}
					}
				});
		     });
         });
         </script>
		<?php	
		//
		// Login Form
		//
		echo '	<div id="rbsignin-register" class="rbinteract">';	
		echo '        <div id="rbsign-in" class="inline-block">';
		echo '          <h1>'. __("Sign in", rb_agencyinteract_TEXTDOMAIN). '</h1>';
		echo '          <p class="status" style="display:none"></p>'; 
		echo '          <form name="loginform" id="login_ajax" action="login" method="post">';
		echo '            <div class="field-row">';
		echo '              <label for="user-name">'. __("Username", rb_agencyinteract_TEXTDOMAIN). '</label><input type="text" name="user-name" value="'. wp_specialchars( $_POST['user-name'], 1 ) .'" id="username" />';
		echo '            </div>';
		echo '            <div class="field-row">';
		echo '              <label for="password">'. __("Password", rb_agencyinteract_TEXTDOMAIN). '</label><input type="password" name="password" value="" id="password" /> <a href="'. get_bloginfo('wpurl') .'/wp-login.php?action=lostpassword">'. __("forgot password", rb_agencyinteract_TEXTDOMAIN). '?</a>';
		echo '            </div>';
		echo '            <div class="field-row submit-row">';
		echo '              <input type="button" id="submit_login" value="'. __("Sign In", rb_agencyinteract_TEXTDOMAIN).'" /><br />';
		echo '            </div>';
							wp_nonce_field( 'ajax-login-nonce', 'security' );
		echo '          </form>';
		echo '        </div> <!-- rbsign-in -->';
		echo '      <div class="clear line"></div>';
		echo '      </div>';
} 

/*
 * Get Current URl for redirection
 */
function rb_current_url(){
	
	$URL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$URL .= "s";}
	$URL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$URL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$URL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $URL;
}

/*
 * Check casting cart / add fav if permitted to be displayed
 */
function is_permitted($type){
    
                $rb_agency_options_arr = get_option('rb_agency_options');
                $rb_agency_option_privacy = $rb_agency_options_arr['rb_agency_option_privacy'];
                $rb_agency_option_profilelist_castingcart  = isset($rb_agency_options_arr['rb_agency_option_profilelist_castingcart']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_castingcart'] : 0;
				$rb_agency_option_profilelist_favorite	 = isset($rb_agency_options_arr['rb_agency_option_profilelist_favorite']) ? (int)$rb_agency_options_arr['rb_agency_option_profilelist_favorite'] : 0;
            
                if($type=="casting" && !$rb_agency_option_profilelist_castingcart) return false;
                if($type=="favorite" && !$rb_agency_option_profilelist_favorite) return false;
                if(!is_user_logged_in()) return false;
				
                if($type == "casting" || $type == "favorite" ){
                        
                     if ( ($rb_agency_option_privacy == 2) || 
			 
                           // Model list public. Must be logged to view profile information
                           ($rb_agency_option_privacy == 1) ||
							
							// Model list public and information
                           ($rb_agency_option_privacy == 0) ||
			 				
							//admin users
							(current_user_can( 'manage_options' )) ||
			 
							//  Must be logged as "Client" to view model list and profile information
							($rb_agency_option_privacy == 3 && is_client_profiletype()) ) {
                        
                         return true;
                       }
                 }
        return false;
}
 /**
     * Clean String, remove extra quotes
     *
     * @param string $string
     */
	function rb_agency_cleanString($string) {
		// Remove trailing dingleberry
		if (substr($string, -1) == ",") {  $string = substr($string, 0, strlen($string)-1); }
		if (substr($string, 0, 1) == ",") { $string = substr($string, 1, strlen($string)-1); }
		// Just Incase
		$string = str_replace(",,", ",", $string);

		return $string;
	}
	
	
/*
 * Check if profilet type ID is "Client" type
 */
function is_client_profiletype(){
	
	global $current_user;
	
	$query = "SELECT ProfileType FROM ". table_agency_profile ." WHERE ProfileUserLinked = ". rb_agency_get_current_userid();
	$results = mysql_query($query);
	
	if(mysql_num_rows($results)){
		$id = mysql_fetch_assoc($results);
		$id = $id['ProfileType'];
		$queryList = "SELECT DataTypeTitle FROM ". table_agency_data_type ." WHERE DataTypeID = ". $id;
		$resultsList = mysql_query($queryList);
		while ($d = mysql_fetch_array($resultsList)) {
			if(strtolower($d["DataTypeTitle"]) == "client"){
				return true;
			}
		}	
		/*
		 * lets check postmeta field
		 */
		$check_type = get_user_meta($current_user->ID, 'rb_agency_interact_clientdata', true);
		if($check_type != ""){
		   return true;
		} 
	} 
	/*
	 * lets check postmeta field
	 */
	else {
		$check_type = get_user_meta($current_user->ID, 'rb_agency_interact_clientdata', true);
		if($check_type != ""){
		   return true;
		} 	
	}	
	
	return false;
}

/*
 * Self Delete Process for 
 * Users
 */

$rb_profile_delete = isset($rb_agency_options_arr['rb_agency_option_profiledeletion']) ? $rb_agency_options_arr['rb_agency_option_profiledeletion'] : 1;
 
if($rb_profile_delete == 2 || $rb_profile_delete == 3){
		
		add_action('admin_menu', 'Delete_Owner');	
		
		add_action('wp_before_admin_bar_render', 'self_delete');
		if(is_admin()){
			add_action( 'admin_print_footer_scripts', 'delete_script' );
		} else {
			add_action('wp_footer', 'delete_script');
		}
}

function Delete_Owner(){
	
	$page_title = 'RB Account';
 	$menu_title = 'Account';
	$capability = 'subscriber';
	$menu_slug = 'delete_profile';

	add_object_page( $page_title, 
	                 $menu_title, 
			 $capability, 
			 $menu_slug,
			 'Profile_Account');
	
}

function Profile_Account(){  
    global $rb_profile_delete;
    echo "<h2>Account Settings</h2><br/>";
	echo "<input type='hidden' id='delete_opt' value='".$rb_profile_delete."'>";
    echo "<input id='self_del' type='button' name='remove' value='Remove My Profile' class='btn-primary'>";
	
}

function delete_script() {?>

    <script type="text/javascript">
        jQuery(document).ready(function(){

            jQuery("#self_del").click(function(){

                var continue_delete = confirm("Are you sure you want to delete your profile?");

                if (continue_delete) {	
                        // ajax delete
					alert(jQuery('#delete_opt').val());	
                    jQuery.ajax({
                        type: "POST",
                        url: '<?php echo plugins_url( 'rb-agency/tasks/userdelete.php' , dirname(__FILE__) ); ?>',
                        dataType: "html",
                        data: { ID : "<?php echo rb_agency_get_current_userid(); ?>", OPT: jQuery('#delete_opt').val() },

                        beforeSend: function() {
                        },

                        error: function() {
                            setTimeout(function(){
                            alert("Process Failed. Please try again later.");	
                            }, 1000);
                        },	

                        success: function(data) {
                            if (data != "") {
                                setTimeout(function(){
                                	alert(data);
									//alert("Deletion success! You will now be redirected to our homepage.");
                                    window.location.href = "<?php echo get_bloginfo('wpurl'); ?>";
                                }, 1000);
                            } else {
                                setTimeout(function(){
                                    alert("Failed. Please try again later.");
                                }, 1000);
                            }
                        }
                    });
                }
            });	
        });
    </script>
	<?php
}

function self_delete() {

	global $wp_admin_bar;

	$href = get_bloginfo('wpurl');
	$title = '<div>' . '<div class="ab-item">User Profile</div></div>';
	$prof_href = $href . '/wp-admin/profile.php'; 
	$account = $href . '/wp-admin/admin.php?page=delete_profile';

	$wp_admin_bar->add_menu( array(
		'parent' => false,
		'id' => 'self_delete',
		'title' => __($title)
	));

	$wp_admin_bar->add_menu(array(
		'parent' => 'self_delete',
		'id' => 'profile_manage',
		'title' => __('<a class="ab-item" href="'.$prof_href.'">Manage Profile</a>'),
	)); 
	$wp_admin_bar->add_menu(array(
		'parent' => 'self_delete',
		'id' => 'actual_delete',
		'title' => __('<a class="ab-item"  href="'.$account.'">Account Settings</a>'),
	));

}
?>