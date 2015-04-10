<?php 
$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
	$rb_agency_option_agencylogo = !empty($rb_agency_options_arr['rb_agency_option_agencylogo'])?$rb_agency_options_arr['rb_agency_option_agencylogo']:get_bloginfo("url")."/wp-content/plugins/rb-agency/assets/img/logo_example.jpg";
	$rb_agency_option_adminprint_hidden = isset($rb_agency_options_arr['rb_agency_option_adminprint_hidden'])?$rb_agency_options_arr['rb_agency_option_adminprint_hidden']:0;


global $wpdb;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php bloginfo('name'); ?> | Print</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Robots" content="noindex, nofollow" />
	<link rel="stylesheet" type="text/css" media="screen, print" href="<?php bloginfo('stylesheet_directory'); ?>/style.css" />
	<script language="Javascript1.2">
		<!--
		function printpage() {
			window.print();
		}
		//-->
	</script>
</head>
<body onload="printpage()" style="background: #fff;">
	<div id="print_wrapper" style="width: 887px;">
		<div id="print_logo" style="float: left; width: 50%;">
			<?php if(!empty($rb_agency_option_agencylogo)){ ?>
			  <img style="height:50px;" src="<?php echo $rb_agency_option_agencylogo; ?>" title="<?php echo $rb_agency_option_agencyname; ?>" />
			<?php }else{ ?>
			<?php echo $rb_agency_option_agencyname; ?>
			<?php } ?>
		</div>
			<div id="print_actions" style="float: left; text-align: right; width: 50%;"><a href="#" onclick="printpage();">Print</a> | <a href="javascript:window.opener='x';window.close();">Close</a></div>
			<div style="clear: both;"></div>
		<?php

		global $wpdb;
		$hasQuery = false;

		// Set Casting Cart Session
		if ($_GET['action'] == "quickPrint") {
			$hasQuery = true;
			extract($_SESSION);
			foreach($_SESSION as $key=>$value) {
				  $$key = $value;
			}
			
			//// Filter
			$filter = " WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1";
			if(isset($_GET['id']) && $_GET['id']){
				$filter .= " AND profile.ProfileID IN (".$_GET['id'].") "; 
			} 
			// Name
			if ((isset($ProfileContactNameFirst) && !empty($ProfileContactNameFirst)) || isset($ProfileContactNameLast) && !empty($ProfileContactNameLast)){
				if (isset($ProfileContactNameFirst) && !empty($ProfileContactNameFirst)){
				$filter .= " AND profile.ProfileContactNameFirst='". $ProfileContactNameFirst ."'";
				}
				if (isset($ProfileContactNameLast) && !empty($ProfileContactNameLast)){
				$filter .= " AND profile.ProfileContactNameLast='". $ProfileContactNameLast ."'";
				}
			}
			// Location
			if (isset($ProfileLocationCity) && !empty($ProfileLocationCity)){
				$filter .= " AND profile.ProfileLocationCity='". $ProfileLocationCity ."'";
			}
			// Type
			if (isset($ProfileType) && !empty($ProfileType)){
				if ($ProfileType == "Model") {
					$selectedIsModel = " selected";
					$filter .= " AND profile.ProfileIsModel='1'";
				} elseif ($ProfileType == "Talent") {
					$selectedIsTalent = " selected";
					$filter .= " AND profile.ProfileIsTalent='1'";
				}
			}
			// Active
			if (isset($ProfileIsActive)){
				if ($ProfileIsActive == "1") {
					$selectedActive = "active";
					$filter .= " AND profile.ProfileIsActive=1";
				} elseif ($ProfileIsActive == "0") {
					$selectedActive = "inactive";
					$filter .= " AND profile.ProfileIsActive=0";
				}
			} else {
				$selectedActive = "";
			}
			// Gender
			if (isset($ProfileGender) && !empty($ProfileGender)){
				$filter .= " AND profile.ProfileGender='".$ProfileGender."'";
			} else {
				$ProfileGender = "";
			}
			// Race
			if (isset($ProfileStatEthnicity) && !empty($ProfileStatEthnicity)){
				$filter .= " AND profile.ProfileStatEthnicity='". $ProfileStatEthnicity ."'";
			}
			// Skin
			if (isset($ProfileStatSkinColor) && !empty($ProfileStatSkinColor)){
				$filter .= " AND profile.ProfileStatSkinColor='". $ProfileStatSkinColor ."'";
			}
			// Eye
			if (isset($ProfileStatEyeColor) && !empty($ProfileStatEyeColor)){
				$filter .= " AND profile.ProfileStatEyeColor='". $ProfileStatEyeColor ."'";
			}
			// Hair
			if (isset($ProfileStatHairColor) && !empty($ProfileStatHairColor)){
				$filter .= " AND profile.ProfileStatHairColor='". $ProfileStatHairColor ."'";
			}
			// Height
			if (isset($ProfileStatHeight_min) && !empty($ProfileStatHeight_min)){
				$filter .= " AND profile.ProfileStatHeight >= '". $ProfileStatHeight_min ."'";
			}
			if (isset($ProfileStatHeight_max) && !empty($ProfileStatHeight_max)){
				$filter .= " AND profile.ProfileStatHeight <= '". $ProfileStatHeight_max ."'";
			}
			// Weight
			if (isset($ProfileStatWeight_min) && !empty($ProfileStatWeight_min)){
				$filter .= " AND profile.ProfileStatWeight >= '". $ProfileStatWeight_min ."'";
			}
			if (isset($ProfileStatWeight_max) && !empty($ProfileStatWeight_max)){
				$filter .= " AND profile.ProfileStatWeight <= '". $ProfileStatWeight_max ."'";
			}
			// Bust/Chest
			if (isset($ProfileStatBust_min) && !empty($ProfileStatBust_min)){
				$filter .= " AND profile.ProfileStatBust >= '". $ProfileStatBust_min ."'";
			}
			if (isset($ProfileStatBust_max) && !empty($ProfileStatBust_max)){
				$filter .= " AND profile.ProfileStatBust <= '". $ProfileStatBust_max ."'";
			}
			// Waist
			if (isset($ProfileStatWaist_min) && !empty($ProfileStatWaist_min)){
				$filter .= " AND profile.ProfileStatWaist >= '". $ProfileStatWaist_min ."'";
			}
			if (isset($ProfileStatWaist_max) && !empty($ProfileStatWaist_max)){
				$filter .= " AND profile.ProfileStatWaist <= '". $ProfileStatWaist_max ."'";
			}
			// Hip
			if (isset($ProfileStatHip_min) && !empty($ProfileStatHip_min)){
				$filter .= " AND profile.ProfileStatHip >= '". $ProfileStatHip_min ."'";
			}
			if (isset($ProfileStatHip_max) && !empty($ProfileStatHip_max)){
				$filter .= " AND profile.ProfileStatHip <= '". $ProfileStatHip_max ."'";
			}
			// Age
			$timezone_offset = -10; // Hawaii Time
			$dateInMonth = gmdate('d', time() + $timezone_offset *60 *60);
			$format = 'Y-m-d';
			$date = gmdate($format, time() + $timezone_offset *60 *60);
			if (isset($ProfileDateBirth_min) && !empty($ProfileDateBirth_min)){
				$selectedYearMin = date($format, strtotime('-'. $ProfileDateBirth_min .' year'. $date));
				$filter .= " AND profile.ProfileDateBirth <= '$selectedYearMin'";
			}
			if (isset($ProfileDateBirth_max) && !empty($ProfileDateBirth_max)){
				$selectedYearMax = date($format, strtotime('-'. $ProfileDateBirth_max-1 .' year'. $date));
				$filter .= " AND profile.ProfileDateBirth >= '$selectedYearMax'";
			}
			
			// Filter Models Already in Cart
			if (isset($_SESSION['cartArray'])) {
				$cartArray = $_SESSION['cartArray'];
				$cartString = implode(",", $cartArray);
				$filter .=  " AND profile.ProfileID NOT IN (". $cartString .")";
			}
			
			// Show Cart
			$query = "SELECT * FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media $filter  GROUP BY  profile.ProfileID ORDER BY ProfileContactNameFirst";
			$results = $wpdb->get_results($query,ARRAY_A);// or die ( __("Error, query failed", RBAGENCY_TEXTDOMAIN ));
			//$wpdb->show_errors();
			//$wpdb->print_error();
			$count =  count($results);
			if ($count < 1) {
				echo "There are currently no profiles in the casting cart.";
				$hasQuery = false;
			}

		// Call Casting Cart Session
		} elseif (($_GET['action'] == "castingCart") && (isset($_SESSION['cartArray']))) {
			$cartArray = $_SESSION['cartArray'];
			$cartString = implode(",", $cartArray);
			$hasQuery = true;


			// Show Cart
			$query = "SELECT * FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (". $cartString .") GROUP BY  profile.ProfileID ORDER BY ProfileContactNameFirst ASC";
			$results = $wpdb->get_results($query,ARRAY_A) or die ( __("Error, query failed", RBAGENCY_TEXTDOMAIN ));
			$count = count($results);

			if ($count < 1) {
				echo "There are currently no profiles in the casting cart.";
				$hasQuery = false;
			}
		} else {
			echo "<p>Nothing to display.  <a href=\"javascript:window.opener='x';window.close();\">Close</a></div></p>";
			$hasQuery = false;
		}
		echo "<table>";
		echo "<tr>";
				
		if ($hasQuery) {
			echo "<div style=\"clear: both;width: 887px; \" class=\"profile\">";
			$ii = 0;
			foreach($results as $data) {
				$ii++;
				
				
				echo "<td style=\" border: 1px solid #e1e1e1; vertical-align: top;\">";
				if (1 == 1) {

					echo "<div style=\"float: left; width: 420px; min-height: 220px; overflow: hidden; margin: 5px; padding:5px;  \">";
					echo " <div style=\"float: left; width: 150px; height: 180px; margin-right: 5px; overflow: hidden; \"><img style=\"width: 150px; \" src=\"". RBAGENCY_UPLOADDIR ."". $data["ProfileGallery"] ."/". $data["ProfileMediaURL"] ."\" /></div>\n";
					echo " <div style=\"float: left; width: 230px; padding: 15px; \">";

					if ($_GET['cD'] == "1") {
						$ProfileID = $data['ProfileID'];
						echo "	<h2 style=\"margin-top: 15px; \">". stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast']) . "</h2>"; 
						// Hide private information from print
							/*
						if ($rb_agency_option_adminprint_hidden == 1 && current_user_can('edit_pages')) {
							if (!empty($data['ProfileContactEmail'])) {
								echo "<div><strong>Email:</strong> ". $data['ProfileContactEmail'] ."</div>\n";
							}
							if (!empty($data['ProfileLocationStreet'])) {
								echo "<div><strong>Address:</strong> ". $data['ProfileLocationStreet'] ."</div>\n";
							}
							if (!empty($data['ProfileLocationCity']) || !empty($data['ProfileLocationState'])) {
								echo "<div><strong>Location:</strong> ". $data['ProfileLocationCity'] .", ". get_state_by_id($data['ProfileLocationState']) ." ". $data['ProfileLocationZip'] ."</div>\n";
							}
							if (!empty($data['ProfileLocationCountry'])) {
								echo "<div><strong>". __("Country", RBAGENCY_TEXTDOMAIN) .":</strong> ". rb_agency_getCountryTitle($data['ProfileLocationCountry']) ."</div>\n";
							}
							if (!empty($data['ProfileDateBirth'])) {
								echo "<div><strong>". __("Age", RBAGENCY_TEXTDOMAIN) .":</strong> ". rb_agency_get_age($data['ProfileDateBirth']) ."</div>\n";
							}
							if (!empty($data['ProfileDateBirth'])) {
								echo "<div><strong>". __("Birthdate", RBAGENCY_TEXTDOMAIN) .":</strong> ". $data['ProfileDateBirth'] ."</div>\n";
							}
							if (!empty($data['ProfileContactWebsite'])) {
								echo "<div><strong>". __("Website", RBAGENCY_TEXTDOMAIN) .":</strong> ". $data['ProfileContactWebsite'] ."</div>\n";
							}
							if (!empty($data['ProfileContactPhoneHome'])) {
								echo "<div><strong>". __("Phone Home", RBAGENCY_TEXTDOMAIN) .":</strong> ". $data['ProfileContactPhoneHome'] ."</div>\n";
							}
							if (!empty($data['ProfileContactPhoneCell'])) {
								echo "<div><strong>". __("Phone Cell", RBAGENCY_TEXTDOMAIN) .":</strong> ". $data['ProfileContactPhoneCell'] ."</div>\n";
							}
							if (!empty($data['ProfileContactPhoneWork'])) {
								echo "<div><strong>". __("Phone Work", RBAGENCY_TEXTDOMAIN) .":</strong> ". $data['ProfileContactPhoneWork'] ."</div>\n";
							}
							
						//$resultsCustomPrivate =  $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle, c.ProfileCustomOrder, c.ProfileCustomView, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = %d GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder DESC", $ProfileID ));
						//foreach  ($resultsCustomPrivate as $resultCustomPrivate) {
						//	echo "				<div><strong>". $resultCustomPrivate->ProfileCustomTitle ."<span class=\"divider\">:</span></strong> ". $resultCustomPrivate->ProfileCustomValue ."</div>\n";
						//}
							*/
						//$resultsCustomPrivate =  $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle, c.ProfileCustomOrder, c.ProfileCustomView, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = %d GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder DESC", $ProfileID ));
						//foreach  ($resultsCustomPrivate as $resultCustomPrivate) {
						//	echo "				<div><strong>". $resultCustomPrivate->ProfileCustomTitle ."<span class=\"divider\">:</span></strong> ". $resultCustomPrivate->ProfileCustomValue ."</div>\n";
						//}
							$resultsCustomPrivate =  $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle, c.ProfileCustomOrder, c.ProfileCustomView, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = %d GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder DESC", $ProfileID ));
							foreach  ($resultsCustomPrivate as $resultCustomPrivate) {
								if (!empty($resultCustomPrivate->ProfileCustomValue)) {
									echo "<div><strong>". $resultCustomPrivate->ProfileCustomTitle ."<span class=\"divider\">:</span></strong> ". $resultCustomPrivate->ProfileCustomValue ."</div>\n";
								}
							}
						} // End Private Fields


						if (!empty($data['ProfileGender'])) {
							if(RBAgency_Common::profile_meta_gendertitle($data['ProfileGender'])){
								echo "<div><strong>". __("Gender", RBAGENCY_TEXTDOMAIN) .":</strong> ".rb_agency_getGenderTitle($data['ProfileGender'])."</div>\n";
							}else{
								echo "<div><strong>". __("Gender", RBAGENCY_TEXTDOMAIN) .":</strong> --</div>\n";	
							}
						}

						$resultsCustom = $wpdb->get_results($wpdb->prepare("SELECT c.ProfileCustomID,c.ProfileCustomTitle, c.ProfileCustomOrder, c.ProfileCustomView, cx.ProfileCustomValue,cx.ProfileCustomDateValue, c.ProfileCustomType FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = %d GROUP BY cx.ProfileCustomID ORDER BY c.ProfileCustomOrder ASC",$ProfileID ));
						foreach  ($resultsCustom as $resultCustom) {
							if(!empty($resultCustom->ProfileCustomValue ) || !empty($resultCustom->ProfileCustomDateValue )){
								if($resultCustom->ProfileCustomType == 10){
									echo "<div><strong>". $resultCustom->ProfileCustomTitle ."<span class=\"divider\">:</span></strong> ". date("F d, Y",strtotime(stripcslashes($resultCustom->ProfileCustomDateValue))) ."</div>\n";
								}else{
									echo "<div><strong>". $resultCustom->ProfileCustomTitle ."<span class=\"divider\">:</span></strong> ". stripcslashes($resultCustom->ProfileCustomValue) ."</div>\n";
								}
							}
						}

						echo " </div>";
						echo " <div style=\"clear: both; text-align: center; padding: 5px; \">\n";
					} else {
						echo "	<h2 style=\"text-align: center; margin-top: 30px; \">". stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast'])  . "</h2>"; 
					}
				
					echo " </div>";
					echo "</div>";
				} // elseif (layout style is another value......) {
				echo "</td>";
				
				if( $ii % 2==0){
					echo "</tr>";
				}

			//}
			echo "<div style=\"clear: both;\"></div>";
			echo "</div>";
			echo "</tr>";
		echo "</table>";
		}
		?>
		<center>
		<img style="width:347px;" src="<?php echo $rb_agency_option_agencylogo;?>"/>
		</center>
		<p style="text-align: center;">Property of <?php echo $rb_agency_option_agencyname; ?>.  All rights reserved.</p>
	</div>
</body>
</html>