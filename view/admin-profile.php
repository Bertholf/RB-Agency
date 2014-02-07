<?php

global $wpdb;
define("LabelPlural", "Profile");
define("LabelSingular", "Profiles");

/*
 * Pull Options
 */

	$rb_agency_options_arr = get_option('rb_agency_options');

	$rb_agency_option_unittype = $rb_agency_options_arr['rb_agency_option_unittype'];
	$rb_agency_option_showsocial = $rb_agency_options_arr['rb_agency_option_showsocial'];
	$rb_agency_option_agencyimagemaxheight = $rb_agency_options_arr['rb_agency_option_agencyimagemaxheight'];

	if (empty($rb_agency_option_agencyimagemaxheight) || $rb_agency_option_agencyimagemaxheight < 500) {
		$rb_agency_option_agencyimagemaxheight = 800;
	}

	$rb_agency_option_profilenaming = (int) $rb_agency_options_arr['rb_agency_option_profilenaming'];
	$rb_agency_option_locationtimezone = (int) $rb_agency_options_arr['rb_agency_option_locationtimezone'];

	if (function_exists(rb_agencyinteract_approvemembers)) {
		// Load Interact Settings
		$rb_agencyinteract_options_arr = get_option('rb_agencyinteract_options');
		$rb_agency_option_useraccountcreation = (int) $rb_agency_options_arr['rb_agency_option_useraccountcreation'];
	}


// *************************************************************************************************** //
// Handle Post Actions
$errorValidation = array();
if (isset($_POST['action'])) {

	/*
	 * Pull Post Values
	 */

	$ProfileID = $_POST['ProfileID'];
	$ProfileUserLinked = $_POST['ProfileUserLinked'];
	$ProfileContactNameFirst = trim($_POST['ProfileContactNameFirst']);
	$ProfileContactNameLast = trim($_POST['ProfileContactNameLast']);
	$ProfileContactDisplay = trim($_POST['ProfileContactDisplay']);
	if (empty($ProfileContactDisplay)) {  // Probably a new record... 
		if ($rb_agency_option_profilenaming == 0) {
			$ProfileContactDisplay = $ProfileContactNameFirst . " " . $ProfileContactNameLast;
		} elseif ($rb_agency_option_profilenaming == 1) {
			// If John-D already exists, make John-D-1
			for ($i = 'a', $j = 1; $j <= 26; $i++, $j++) {
				if (in_array($i, $ar)){
					$ProfileContactDisplay = $ProfileContactNameFirst . " " . $i .'-'. $j;
				} else {
					$ProfileContactDisplay = $ProfileContactNameFirst . " " . substr($ProfileContactNameLast, 0, 1);
				}
			}

		} elseif ($rb_agency_option_profilenaming == 2) {
			$errorValidation['rb_agency_option_profilenaming'] = "<b><i>" . __(LabelSingular . " must have a display name identified", rb_agency_TEXTDOMAIN) . ".</i></b><br>";
			$have_error = true;
		} elseif ($rb_agency_option_profilenaming == 3) {
			$ProfileContactDisplay = "ID " . $ProfileID;
		} elseif ($rb_agency_option_profilenaming == 4) {
			$ProfileContactDisplay = $ProfileContactNameFirst;
		} elseif ($rb_agency_option_profilenaming == 5) {
			$ProfileContactDisplay = $ProfileContactNameLast;
		}
	}

	$ProfileGallery = $_POST['ProfileGallery'];

	if (empty($ProfileGallery)) {  // Probably a new record... 
		$ProfileGallery = RBAgency_Common::format_stripchars($ProfileContactDisplay);
	}

	$ProfileGender = $_POST['ProfileGender'];
	$ProfileDateBirth = $_POST['ProfileDateBirth'];
	$ProfileContactEmail = $_POST['ProfileContactEmail'];
	$ProfileUsername = $_POST["ProfileUsername"];
	$ProfilePassword = $_POST['ProfilePassword'];
	$ProfileContactWebsite = $_POST['ProfileContactWebsite'];
	$ProfileContactPhoneHome = $_POST['ProfileContactPhoneHome'];
	$ProfileContactPhoneCell = $_POST['ProfileContactPhoneCell'];
	$ProfileContactPhoneWork = $_POST['ProfileContactPhoneWork'];
	$ProfileLocationStreet = $_POST['ProfileLocationStreet'];
	$ProfileLocationCity = RBAgency_Common::format_propercase($_POST['ProfileLocationCity']);
	$ProfileLocationState = strtoupper($_POST['ProfileLocationState']);
	$ProfileLocationZip = $_POST['ProfileLocationZip'];
	$ProfileLocationCountry = $_POST['ProfileLocationCountry'];
	$ProfileLanguage = $_POST['ProfileLanguage'];
	$ProfileDateUpdated = $_POST['ProfileDateUpdated'];
	$ProfileDateViewLast = $_POST['ProfileDateViewLast'];
	$ProfileType = $_POST['ProfileType'];

	if (is_array($ProfileType)) {
		$ProfileType = implode(",", $ProfileType);
	}

	$ProfileIsActive = $_POST['ProfileIsActive']; // 0 Inactive | 1 Active | 2 Archived | 3 Pending Approval
	$ProfileIsFeatured = $_POST['ProfileIsFeatured'];
	$ProfileIsPromoted = $_POST['ProfileIsPromoted'];
	$ProfileStatHits = $_POST['ProfileStatHits'];

	// Get Primary Image
	$ProfileMediaPrimaryID = $_POST['ProfileMediaPrimary'];

	// Notify User and Admin
	$ProfileNotifyUser = $_POST["ProfileNotifyUser"];

	// Error checking

	$have_error = false;
	if (trim($ProfileContactNameFirst) == "") {
		$errorValidation['ProfileContactNameFirst']= "<b><i>The " . LabelSingular . " must have a name.</i></b><br>";
		$have_error = true;
	}

	if ($_GET["action"] == "add") {
		$userdata = array(
			'user_pass' => esc_attr($ProfilePassword),
			'user_login' => esc_attr($ProfileUsername),
			'first_name' => esc_attr($ProfileContactNameFirst),
			'last_name' => esc_attr($ProfileContactNameLast),
			'user_email' => esc_attr($ProfileContactEmail),
			'role' => get_option('default_role')
		);

		if(function_exists(rb_agencyinteract_approvemembers)){
				if (empty($userdata['user_login'])) {
					   $errorValidation['user_login'] = __("A username is required for registration.<br />", rb_agencyinteract_TEXTDOMAIN);
						$have_error = true;
				}
				if (username_exists($userdata['user_login'])) {
						$errorValidation['user_login'] = __("Sorry, that username already exists!<br />", rb_agencyinteract_TEXTDOMAIN);
						$have_error = true;
				}
				if (!$userdata['user_password'] && count($userdata['user_password']) > 5) {
						$errorValidation['user_password']= __("A password is required for registration and must have 6 characters.<br />", rb_agencyinteract_TEXTDOMAIN);
						$have_error = true;
				}
		}
		if (!is_email($userdata['user_email'], true)) {
			$errorValidation['ProfileContactEmail']= __("You must enter a valid email address.<br />", rb_agencyinteract_TEXTDOMAIN);
			$have_error = true;
		}
		if (email_exists($userdata['user_email'])) {
			$errorValidation['ProfileContactEmail']= __("Sorry, that email address is already used!<br />", rb_agencyinteract_TEXTDOMAIN);
			$have_error = true;
		} else {
			if (rb_check_exists($ProfileContactEmail,'ProfileContactEmail','text')) {
				$errorValidation['ProfileContactEmail']= __("Sorry, that email address is already used!<br />", rb_agencyinteract_TEXTDOMAIN);
				$have_error = true;
			}
		}

	}

	if ($_POST["action"] == "editRecord") {
		if($ProfileContactEmail != $_POST['HiddenContactEmail']){
			if (!is_email($ProfileContactEmail, true)) {
				$errorValidation['ProfileContactEmail']= __("You must enter a valid email address.<br />", rb_agencyinteract_TEXTDOMAIN);
				$have_error = true;
			}
			if (rb_check_exists($ProfileContactEmail,'ProfileContactEmail','text')) {
				$errorValidation['ProfileContactEmail']= __("Sorry, that email address is already used!<br />", rb_agencyinteract_TEXTDOMAIN);
				$have_error = true;
			}
		}
	}

	// Get Post State
	$action = $_POST['action'];
	switch ($action) {

		// *************************************************************************************************** //
		// Add Record
		case 'addRecord':
			if (!$have_error) {

				// Bug Free!
				if ($have_error == false) {
					if (function_exists(rb_agencyinteract_approvemembers)) {
						$new_user = wp_insert_user($userdata);
					}

					// Check Directory - create directory if does not exist, rename if does
					$ProfileGallery = rb_agency_createdir($ProfileGallery);

					// Create Record
					$insert = "INSERT INTO " . table_agency_profile .
						" (ProfileGallery,
						   ProfileContactDisplay,
						   ProfileUserLinked,
						   ProfileContactNameFirst,
						   ProfileContactNameLast,
						   ProfileContactEmail,
						   ProfileContactWebsite,
						   ProfileGender,
						   ProfileDateBirth,
						   ProfileLocationStreet,
						   ProfileLocationCity,
						   ProfileLocationState,
						   ProfileLocationZip,
						   ProfileLocationCountry,
						   ProfileContactPhoneHome, 
						   ProfileContactPhoneCell, 
						   ProfileContactPhoneWork,
						   ProfileDateUpdated,
						   ProfileType,
						   ProfileIsActive,
						   ProfileIsFeatured,
						   ProfileIsPromoted,
						   ProfileStatHits,
						   ProfileDateViewLast)" .
						"VALUES (
							'" . $wpdb->escape($ProfileGallery) . "',
							'" . $wpdb->escape($ProfileContactDisplay) . "',
							'" . $wpdb->escape($new_user) . "',
							'" . $wpdb->escape($ProfileContactNameFirst) . "',
							'" . $wpdb->escape($ProfileContactNameLast) . "',
							'" . $wpdb->escape($ProfileContactEmail) . "',
							'" . $wpdb->escape($ProfileContactWebsite) . "',
							'" . $wpdb->escape($ProfileGender) . "',
							'" . $wpdb->escape($ProfileDateBirth) . "',
							'" . $wpdb->escape($ProfileLocationStreet) . "',
							'" . $wpdb->escape($ProfileLocationCity) . "',
							'" . $wpdb->escape($ProfileLocationState) . "',
							'" . $wpdb->escape($ProfileLocationZip) . "',
							'" . $wpdb->escape($ProfileLocationCountry) . "',
							'" . $wpdb->escape($ProfileContactPhoneHome) . "',
							'" . $wpdb->escape($ProfileContactPhoneCell) . "',
							'" . $wpdb->escape($ProfileContactPhoneWork) . "',
							now(),
							'" . $ProfileType . "',
							'" . $wpdb->escape($ProfileIsActive) . "',
							'" . $wpdb->escape($ProfileIsFeatured) . "',
							'" . $wpdb->escape($ProfileIsPromoted) . "',
							'" . $wpdb->escape($ProfileStatHits) . "',
							'" . $wpdb->escape($ProfileDateViewLast) . "'
						)";
					$results = $wpdb->query($insert) or die("Add Record: " . mysql_error());
					$ProfileID = $wpdb->insert_id;


					// Notify admin and user
					if ($ProfileNotifyUser <> "yes" && function_exists(rb_agencyinteract_approvemembers)) {
						wp_new_user_notification($new_user, $ProfilePassword);
					}
					// Set Display Name as Record ID (We have to do this after so we know what record ID to use... right ;)
					if ($rb_agency_option_profilenaming == 3) {
						$ProfileContactDisplay = "ID-" . $ProfileID;
						$ProfileGallery = "ID" . $ProfileID;

						$update = $wpdb->query("UPDATE " . table_agency_profile . " SET ProfileContactDisplay='" . $ProfileContactDisplay . "', ProfileGallery='" . $ProfileGallery . "' WHERE ProfileID='" . $ProfileID . "'");
						$updated = $wpdb->query($update);
					}

					// Add Custom Field Values stored in Mux
					foreach ($_POST as $key => $value) {
						if ((substr($key, 0, 15) == "ProfileCustomID") && (isset($value) && !empty($value))) {
							$ProfileCustomID = substr($key, 15);
							if (is_array($value)) {
								$value = implode(",", $value);
							}
							$insert1 = "INSERT INTO " . table_agency_customfield_mux . " (ProfileID,ProfileCustomID,ProfileCustomValue)" . "VALUES ('" . $ProfileID . "','" . $ProfileCustomID . "','" . $value . "')";
							$results1 = $wpdb->query($insert1);
						}
					}

					echo ('<div id="message" class="updated"><p>' . __("New Profile added successfully!", rb_agency_TEXTDOMAIN) . ' <a href="' . admin_url("admin.php?page=" . $_GET['page']) . '&action=editRecord&ProfileID=' . $ProfileID . '">' . __("Update and add media", rb_agency_TEXTDOMAIN) . '</a></p></div>');
					// We can edit it now
					// rb_display_manage($ProfileID);
					// exit;
				}
			} else {
				echo ('<div id="message" class="error"><p>' . __("Error creating record, please ensure you have filled out all required fields.", rb_agency_TEXTDOMAIN) . '</p></div>');
				rb_display_manage($ProfileID,$errorValidation);

			}

			break;

		// *************************************************************************************************** //
		// Edit Record
		case 'editRecord':
			if (!empty($ProfileContactNameFirst) && !empty($ProfileID)) {

				// Update Record
				$update = "UPDATE " . table_agency_profile . " SET 
					ProfileGallery='" . $wpdb->escape($ProfileGallery) . "',
					ProfileContactDisplay='" . $wpdb->escape($ProfileContactDisplay) . "',
					ProfileContactNameFirst='" . $wpdb->escape($ProfileContactNameFirst) . "',
					ProfileContactNameLast='" . $wpdb->escape($ProfileContactNameLast) . "',
					ProfileContactEmail='" . $wpdb->escape($ProfileContactEmail) . "',
					ProfileContactWebsite='" . $wpdb->escape($ProfileContactWebsite) . "',
					ProfileContactPhoneHome='" . $wpdb->escape($ProfileContactPhoneHome) . "',
					ProfileContactPhoneCell='" . $wpdb->escape($ProfileContactPhoneCell) . "',
					ProfileContactPhoneWork='" . $wpdb->escape($ProfileContactPhoneWork) . "',
					ProfileGender='" . $wpdb->escape($ProfileGender) . "',
					ProfileGender='" . $wpdb->escape($ProfileGender) . "',
					ProfileDateBirth ='" . $wpdb->escape($ProfileDateBirth) . "',
					ProfileLocationStreet='" . $wpdb->escape($ProfileLocationStreet) . "',
					ProfileLocationCity='" . $wpdb->escape($ProfileLocationCity) . "',
					ProfileLocationState='" . $wpdb->escape($ProfileLocationState) . "',
					ProfileLocationZip ='" . $wpdb->escape($ProfileLocationZip) . "',
					ProfileLocationCountry='" . $wpdb->escape($ProfileLocationCountry) . "',
					ProfileDateUpdated=now(),
					ProfileType='" . $ProfileType . "',
					ProfileIsActive='" . $wpdb->escape($ProfileIsActive) . "',
					ProfileIsFeatured='" . $wpdb->escape($ProfileIsFeatured) . "',
					ProfileIsPromoted='" . $wpdb->escape($ProfileIsPromoted) . "',
					ProfileStatHits='" . $wpdb->escape($ProfileStatHits) . "'
					WHERE ProfileID=$ProfileID";
				$results = $wpdb->query($update) or die(mysql_error());

					update_usermeta($_REQUEST['wpuserid'], 'rb_agency_interact_profiletype', $ProfileType);
					update_usermeta($_REQUEST['wpuserid'], 'rb_agency_interact_pgender', esc_attr($ProfileGender));

				if ($ProfileUserLinked > 0) {
					/* Update WordPress user information. */
					update_usermeta($ProfileUserLinked, 'first_name', esc_attr($ProfileContactNameFirst));
					update_usermeta($ProfileUserLinked, 'last_name', esc_attr($ProfileContactNameLast));
					update_usermeta($ProfileUserLinked, 'nickname', esc_attr($ProfileContactDisplay));
					update_usermeta($ProfileUserLinked, 'display_name', esc_attr($ProfileContactDisplay));
					update_usermeta($ProfileUserLinked, 'user_email', esc_attr($ProfileContactEmail));
				}

				// Remove Old Custom Field Values
				$delete1 = "DELETE FROM " . table_agency_customfield_mux . " WHERE ProfileID = \"" . $ProfileID . "\"";
				$results1 = $wpdb->query($delete1);

				// Add New Custom Field Values
				foreach ($_POST as $key => $value) {
					if ((substr($key, 0, 15) == "ProfileCustomID") && (isset($value) && !empty($value))) {
						$ProfileCustomID = substr($key, 15);
						if (is_array($value)) {
							$value = implode(",", $value);
						}
						$insert1 = "INSERT INTO " . table_agency_customfield_mux . " (ProfileID,ProfileCustomID,ProfileCustomValue)" . "VALUES ('" . $ProfileID . "','" . $ProfileCustomID . "','" . $value . "')";
						$results1 = $wpdb->query($insert1);
					}
				}

				// Check Directory - create directory if does not exist
				$ProfileGallery = rb_agency_checkdir($ProfileGallery);

				// Upload Image & Add to Database
				$i = 1;

				while ($i <= 10) {

					if ($_FILES['profileMedia' . $i]['tmp_name'] != "") {
						$uploadMediaType = $_POST['profileMedia' . $i . 'Type'];
						if ($have_error != true) {
							// Upload if it doesnt exist already
							$path_parts = pathinfo($_FILES['profileMedia' . $i]['name']);
							$safeProfileMediaFilename = RBAgency_Common::format_stripchars($path_parts['filename'] ."_". RBAgency_Common::generate_random_string(6) . "." . $path_parts['extension']);
							$results = mysql_query("SELECT * FROM " . table_agency_profile_media . " WHERE ProfileID='" . $ProfileID . "' AND ProfileMediaURL = '" . $safeProfileMediaFilename . "'") or die(mysql_error());
							$count = mysql_num_rows($results);

							if ($count < 1) {
								if ($uploadMediaType == "Image" || $uploadMediaType == "Polaroid") {

									if ($_FILES['profileMedia' . $i]['type'] == "image/pjpeg" || $_FILES['profileMedia' . $i]['type'] == "image/jpeg" || $_FILES['profileMedia' . $i]['type'] == "image/gif" || $_FILES['profileMedia' . $i]['type'] == "image/png") {

										$image = new rb_agency_image();
										$image->load($_FILES['profileMedia' . $i]['tmp_name']);

										if ($image->getHeight() > $rb_agency_option_agencyimagemaxheight) {
											$image->resizeToHeight($rb_agency_option_agencyimagemaxheight);
										}
										$image->save(rb_agency_UPLOADPATH . $ProfileGallery . "/" . $safeProfileMediaFilename);

										// Add to database
										$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL) VALUES ('" . $ProfileID . "','" . $uploadMediaType . "','" . $safeProfileMediaFilename . "','" . $safeProfileMediaFilename . "')");
									} else {
										$errorValidation['profileMedia'] = "<b><i>Please upload an image file only</i></b><br />";
										$have_error = true;
									}
								} else if ($uploadMediaType == "VoiceDemo") {
									// Add to database
									$MIME = array('audio/mpeg', 'audio/mp3');
									if (in_array($_FILES['profileMedia' . $i]['type'], $MIME)) {
										$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL) VALUES ('" . $ProfileID . "','" . $uploadMediaType . "','" . $safeProfileMediaFilename . "','" . $safeProfileMediaFilename . "')");
										move_uploaded_file($_FILES['profileMedia' . $i]['tmp_name'], rb_agency_UPLOADPATH . $ProfileGallery . "/" . $safeProfileMediaFilename);
									} else {
										$errorValidation['profileMedia'] = "<b><i>Please upload a mp3 file only</i></b><br />";
										$have_error = true;
									}
								} else if ($uploadMediaType == "Resume") {
									// Add to database
									if ($_FILES['profileMedia' . $i]['type'] == "application/msword" || $_FILES['profileMedia' . $i]['type'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" || $_FILES['profileMedia' . $i]['type'] == "application/pdf" || $_FILES['profileMedia' . $i]['type'] == "application/rtf") {
										$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL) VALUES ('" . $ProfileID . "','" . $uploadMediaType . "','" . $safeProfileMediaFilename . "','" . $safeProfileMediaFilename . "')");
										move_uploaded_file($_FILES['profileMedia' . $i]['tmp_name'], rb_agency_UPLOADPATH . $ProfileGallery . "/" . $safeProfileMediaFilename);
									} else {
										$errorValidation['profileMedia'] = "<b><i>Please upload PDF/MSword/RTF files only</i></b><br />";
										$have_error = true;
									}
								} else if ($uploadMediaType == "Headshot") {
									// Add to database
									if ($_FILES['profileMedia' . $i]['type'] == "application/msword" || $_FILES['profileMedia' . $i]['type'] == "application/pdf" || $_FILES['profileMedia' . $i]['type'] == "application/rtf" || $_FILES['profileMedia' . $i]['type'] == "image/jpeg" || $_FILES['profileMedia' . $i]['type'] == "image/gif" || $_FILES['profileMedia' . $i]['type'] == "image/png") {
										$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL) VALUES ('" . $ProfileID . "','" . $uploadMediaType . "','" . $safeProfileMediaFilename . "','" . $safeProfileMediaFilename . "')");
										move_uploaded_file($_FILES['profileMedia' . $i]['tmp_name'], rb_agency_UPLOADPATH . $ProfileGallery . "/" . $safeProfileMediaFilename);
									} else {
										$errorValidation['profileMedia'] = "<b><i>Please upload PDF/MSWord/RTF/Image files only</i></b><br />";
										$have_error = true;
									}
								} else if ($uploadMediaType == "Compcard") {
									// Add to database
									if ($_FILES['profileMedia' . $i]['type'] == "image/jpeg" || $_FILES['profileMedia' . $i]['type'] == "image/png") {
										$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL) VALUES ('" . $ProfileID . "','" . $uploadMediaType . "','" . $safeProfileMediaFilename . "','" . $safeProfileMediaFilename . "')");
										move_uploaded_file($_FILES['profileMedia' . $i]['tmp_name'], rb_agency_UPLOADPATH . $ProfileGallery . "/" . $safeProfileMediaFilename);
									} else {
										$errorValidation['profileMedia'] = "<b><i>Please upload jpeg or png files only</i></b><br />";
										$have_error = true;
									}
								} else {
									// Add to database
									if ($_FILES['profileMedia' . $i]['type'] == "image/pjpeg" || $_FILES['profileMedia' . $i]['type'] == "image/jpeg" || $_FILES['profileMedia' . $i]['type'] == "image/gif" || $_FILES['profileMedia' . $i]['type'] == "image/png") {
										$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL) VALUES ('" . $ProfileID . "','" . $uploadMediaType . "','" . $safeProfileMediaFilename . "','" . $safeProfileMediaFilename . "')");
										move_uploaded_file($_FILES['profileMedia' . $i]['tmp_name'], rb_agency_UPLOADPATH . $ProfileGallery . "/" . $safeProfileMediaFilename);
									} else {
										$errorValidation['profileMedia'] = "<b><i>" . __("Please upload jpeg or png files only", rb_agencyinteract_TEXTDOMAIN) . "</i></b><br />";
										$have_error = true;
									}
								}
							} // End count
						} // End have error = false
					} //End:: if profile media is not empty.
					$i++;
				} // endwhile           
				// Upload Videos to Database
								if (isset($_POST['profileMediaV1']) && !empty($_POST['profileMediaV1'])) {
									$profileMediaType = $_POST['profileMediaV1Type'];
									$profileMediaTitle = $_POST['media1_title'] ."<br>". $_POST['media1_caption'];
									$profileMediaURL = rb_agency_get_VideoFromObject($_POST['profileMediaV1']);
									$profileVideoType = $_POST['media1_vtype'];
									$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL, ProfileVideoType) VALUES ('" . $ProfileID . "','" . $profileMediaType . "','" . $profileMediaTitle . "','" . $profileMediaURL . "','".$profileVideoType."')");
								}
								if (isset($_POST['profileMediaV2']) && !empty($_POST['profileMediaV2'])) {
									$profileMediaType = $_POST['profileMediaV2Type'] ;
									$profileMediaTitle = $_POST['media2_title'] ."<br>". $_POST['media2_caption'];
									$profileMediaURL = rb_agency_get_VideoFromObject($_POST['profileMediaV2']);
									$profileVideoType = $_POST['media2_vtype'];
									$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL, ProfileVideoType) VALUES ('" . $ProfileID . "','" . $profileMediaType . "','" . $profileMediaTitle . "','" . $profileMediaURL . "','".$profileVideoType."')");
								}
								if (isset($_POST['profileMediaV3']) && !empty($_POST['profileMediaV3'])) {
									$profileMediaType = $_POST['profileMediaV3Type'] ;
									$profileMediaURL = rb_agency_get_VideoFromObject($_POST['profileMediaV3']);
									$profileMediaTitle = $_POST['media3_title'] ."<br>". $_POST['media3_caption'];
									$profileVideoType = $_POST['media3_vtype'];
									$results = $wpdb->query("INSERT INTO " . table_agency_profile_media . " (ProfileID, ProfileMediaType, ProfileMediaTitle, ProfileMediaURL, ProfileVideoType) VALUES ('" . $ProfileID . "','" . $profileMediaType . "','" . $profileMediaTitle . "','" . $profileMediaURL . "','".$profileVideoType."')");
								}

				/* --------------------------------------------------------- CLEAN THIS UP -------------- */
				// Do we have a custom image yet? Lets just set the first one as primary.
				$results = mysql_query("SELECT * FROM " . table_agency_profile_media . " WHERE ProfileID='" . $ProfileID . "' AND ProfileMediaType = 'Image' AND ProfileMediaPrimary='1'");
				$count = mysql_num_rows($results);
				if ($count < 1) {
					$resultsNeedOne = mysql_query("SELECT * FROM " . table_agency_profile_media . " WHERE ProfileID='" . $ProfileID . "' AND ProfileMediaType = 'Image' LIMIT 0, 1");
					while ($dataNeedOne = mysql_fetch_array($resultsNeedOne)) {
						$resultsFoundOne = $wpdb->query("UPDATE " . table_agency_profile_media . " SET ProfileMediaPrimary='1' WHERE ProfileID='" . $ProfileID . "' AND ProfileMediaID = '" . $dataNeedOne['ProfileMediaID'] . "'");
						break;
					}
				}
				if ($ProfileMediaPrimaryID > 0) {
					// Update Primary Image
					$results = $wpdb->query("UPDATE " . table_agency_profile_media . " SET ProfileMediaPrimary='0' WHERE ProfileID=$ProfileID");
					$results = $wpdb->query("UPDATE " . table_agency_profile_media . " SET ProfileMediaPrimary='1' WHERE ProfileID=$ProfileID AND ProfileMediaID=$ProfileMediaPrimaryID");
				}
				// Update Image order 
				foreach ($_POST as $key => $val) {
					if (substr($key,0,18) == "ProfileMediaOrder_") {
						$pieces = explode("_", $key);
						if($pieces[1]>0 && $val!=""){
								$results = $wpdb->query("UPDATE " . table_agency_profile_media . " SET ProfileMediaOrder='".$val."' WHERE ProfileID=$ProfileID AND ProfileMediaID=$pieces[1]");
						}
					}

				}
				/* --------------------------------------------------------- CLEAN THIS UP -------------- */

				echo ("<div id=\"message\" class=\"updated\"><p>" . __("Profile updated successfully", rb_agency_TEXTDOMAIN) . "! <a href=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&action=editRecord&ProfileID=" . $ProfileID . "\">" . __("Continue editing the record", rb_agency_TEXTDOMAIN) . "?</a></p></div>");
			} else {
				echo ("<div id=\"message\" class=\"error\"><p>" . __("Error updating record, please ensure you have filled out all required fields.", rb_agency_TEXTDOMAIN) . "</p></div>");
			}

			rb_display_list();
			exit;
			break;

		// *************************************************************************************************** //
		// Delete bulk
		case 'deleteRecord':
			foreach ($_POST as $ProfileID) {

				// Verify Record
				$queryDelete = "SELECT * FROM " . table_agency_profile . " WHERE ProfileID =  \"" . $ProfileID . "\"";
				$resultsDelete = mysql_query($queryDelete);

				while ($dataDelete = mysql_fetch_array($resultsDelete)) {
					$ProfileGallery = $dataDelete['ProfileGallery'];

					// Remove Profile
					$delete = "DELETE FROM " . table_agency_profile . " WHERE ProfileID = \"" . $ProfileID . "\"";
					$results = $wpdb->query($delete);
					// Remove Media
					$delete = "DELETE FROM " . table_agency_profile_media . " WHERE ProfileID = \"" . $ProfileID . "\"";
					$results = $wpdb->query($delete);

					if (isset($ProfileGallery)) {
						// Remove Folder
						$dir = rb_agency_UPLOADPATH . $ProfileGallery . "/";
						$mydir = opendir($dir);
						while (false !== ($file = readdir($mydir))) {
							if ($file != "." && $file != "..") {
								unlink($dir . $file) or DIE("<div id=\"message\" class=\"error\"><p>" . __("Error removing:", rb_agency_TEXTDOMAIN) . $dir . $file . "</p></div>");
							}
						}
						// Remove Directory
						if (is_dir($dir)) {
							rmdir($dir) or DIE("<div id=\"message\" class=\"error\"><p>" . __("Error removing:", rb_agency_TEXTDOMAIN) . $dir . $file . "</p></div>");
						}
						closedir($mydir);
					} else {
						echo ("<div id=\"message\" class=\"error\"><p>" . __("No Valid Record Found.", rb_agency_TEXTDOMAIN) . "</p></div>");
					}

					echo ('<div id="message" class="updated"><p>' . __("Profile deleted successfully!", rb_agency_TEXTDOMAIN) . '</p></div>');
				} // is there record?
				//---------- Delete users but re-assign to Admin User -------------//
				// Gimme an admin:
				$AdminID = $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users WHERE user_login = 'admin'");
				if ($AdminID > 0) {

				} else {
					$AdminID = 1;
				}
				/// Now delete
				wp_delete_user($dataDelete["ProfileUserLinked"], $AdminID);
			}
			rb_display_list();
			exit;
			break;
	}
}

// *************************************************************************************************** //
// Delete Single
elseif ($_GET['action'] == "deleteRecord") {

	$ProfileID = $_GET['ProfileID'];
	// Verify Record
	$queryDelete = "SELECT * FROM " . table_agency_profile . " WHERE ProfileID =  \"" . $ProfileID . "\"";
	$resultsDelete = mysql_query($queryDelete);
	while ($dataDelete = mysql_fetch_array($resultsDelete)) {
		$ProfileGallery = $dataDelete['ProfileGallery'];

		// Remove Profile
		$delete = "DELETE FROM " . table_agency_profile . " WHERE ProfileID = \"" . $ProfileID . "\"";
		$results = $wpdb->query($delete);
		// Remove Media
		$delete = "DELETE FROM " . table_agency_profile_media . " WHERE ProfileID = \"" . $ProfileID . "\"";
		$results = $wpdb->query($delete);

		if (isset($ProfileGallery)) {
			// Remove Folder
			$dir = rb_agency_UPLOADPATH . $ProfileGallery . "/";
			$mydir = @opendir($dir);
			while (false !== ($file = @readdir($mydir))) {
				if ($file != "." && $file != "..") {
					@unlink($dir . $file) or DIE("couldn't delete $dir$file<br />");
				}
			}
			// remove dir
			if (is_dir($dir)) {
				rmdir($dir) or DIE("couldn't delete $dir$file folder not exist<br />");
			}
			closedir($mydir);
		} else {
			echo __("No valid record found.", rb_agency_TEXTDOMAIN);
		}

		wp_delete_user($dataDelete["ProfileUserLinked"]);
		echo ('<div id="message" class="updated"><p>' . __("Profile deleted successfully!", rb_agency_TEXTDOMAIN) . '</p></div>');
	} // is there record?
	rb_display_list();
}

// *************************************************************************************************** //
// Show Edit Record
elseif (($_GET['action'] == "editRecord") || ($_GET['action'] == "add")) {

	$action = $_GET['action'];
	$ProfileID = $_GET['ProfileID'];

	rb_display_manage($ProfileID,$errorValidation);
} else {
// *************************************************************************************************** //
// Show List
	rb_display_list();
}

// *************************************************************************************************** //
// Manage Record
function rb_display_manage($ProfileID, $errorValidation) {
	global $wpdb;
	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_unittype = $rb_agency_options_arr['rb_agency_option_unittype'];
	$rb_agency_option_showsocial = $rb_agency_options_arr['rb_agency_option_showsocial'];
	$rb_agency_option_agencyimagemaxheight = $rb_agency_options_arr['rb_agency_option_agencyimagemaxheight'];
	if (empty($rb_agency_option_agencyimagemaxheight) || $rb_agency_option_agencyimagemaxheight < 500) {
		$rb_agency_option_agencyimagemaxheight = 800;
	}
	$rb_agency_option_profilenaming = (int) $rb_agency_options_arr['rb_agency_option_profilenaming'];
	$rb_agency_option_locationcountry = $rb_agency_options_arr['rb_agency_option_locationcountry'];
	?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.imperial_metrics').keyup(function(){
			var vals = jQuery(this).val();
			var new_val = extractNumber(vals,2,false);
			if(new_val !== true){
				jQuery(this).nextAll('.error_msg').eq(0).html('*Non numeric value is not accepted');
				new_val.replace(/[^/\d*\.*]/g,'');
				jQuery(this).val(new_val);
			}
		});
		jQuery('.imperial_metrics').focusout(function(){
			var vals = jQuery(this).val();
			var new_val = extractNumber(vals,2,false);
			if(new_val !== true){
				jQuery(this).nextAll('.error_msg').eq(0).html('*Non numeric value is not accepted');
				new_val.replace(/[^/\d*\.*]/g,'');
				jQuery(this).val(new_val);
			} else {
				jQuery(this).nextAll('.error_msg').eq(0).html('');
			}
		});		
	});
	function extractNumber(obj, decimalPlaces, allowNegative)
	{
		var temp = obj; var reg0Str = '[0-9]*';
		if (decimalPlaces > 0) { 
			reg0Str += '\\.?[0-9]{0,' + decimalPlaces + '}';
		} else if (decimalPlaces < 0) {
			reg0Str += '\\.?[0-9]*';
		}
		reg0Str = allowNegative ? '^-?' + reg0Str : '^' + reg0Str;
		reg0Str = reg0Str + '$';
		var reg0 = new RegExp(reg0Str);
		if (reg0.test(temp)) return true;
		var reg1Str = '[^0-9' + (decimalPlaces != 0 ? '.' : '') + (allowNegative ? '-' : '') + ']';
		var reg1 = new RegExp(reg1Str, 'g');
		temp = temp.replace(reg1, '');
		if (allowNegative) {
			var hasNegative = temp.length > 0 && temp.charAt(0) == '-';
			var reg2 = /-/g;
			temp = temp.replace(reg2, '');
			if (hasNegative) temp = '-' + temp;
		}
		if (decimalPlaces != 0) {
			var reg3 = /\./g;
			var reg3Array = reg3.exec(temp);
			if (reg3Array != null) {
				var reg3Right = temp.substring(reg3Array.index + reg3Array[0].length);
				reg3Right = reg3Right.replace(reg3, '');
				reg3Right = decimalPlaces > 0 ? reg3Right.substring(0, decimalPlaces) : reg3Right;
				temp = temp.substring(0,reg3Array.index) + '.' + reg3Right;
			}
		}
		
		return temp;
	}
	</script>
	<?php
	// Add Header
	echo "<div class=\"wrap\">\n";
	// Include Admin Menu
	include ("admin-include-menu.php");

	if (!empty($ProfileID) && ($ProfileID > 0)) {

		$query = "SELECT * FROM " . table_agency_profile . " WHERE ProfileID='$ProfileID'";
		$results = mysql_query($query) or die(__("Error, query failed", rb_agency_TEXTDOMAIN));
		$count = mysql_num_rows($results);

		while ($data = mysql_fetch_array($results)) {

			$ProfileID = $data['ProfileID'];
			$ProfileUserLinked = $data['ProfileUserLinked'];
			$ProfileGallery = stripslashes($data['ProfileGallery']);
			$ProfileContactDisplay = stripslashes($data['ProfileContactDisplay']);
			$ProfileContactNameFirst = stripslashes($data['ProfileContactNameFirst']);
			$ProfileContactNameLast = stripslashes($data['ProfileContactNameLast']);
			$ProfileContactEmail = stripslashes($data['ProfileContactEmail']);
			$ProfileContactWebsite = stripslashes($data['ProfileContactWebsite']);
			$ProfileContactLinkFacebook = stripslashes($data['ProfileContactLinkFacebook']);
			$ProfileContactLinkTwitter = stripslashes($data['ProfileContactLinkTwitter']);
			$ProfileContactLinkYouTube = stripslashes($data['ProfileContactLinkYouTube']);
			$ProfileContactLinkFlickr = stripslashes($data['ProfileContactLinkFlickr']);
			$ProfileContactPhoneHome = stripslashes($data['ProfileContactPhoneHome']);
			$ProfileContactPhoneCell = stripslashes($data['ProfileContactPhoneCell']);
			$ProfileContactPhoneWork = stripslashes($data['ProfileContactPhoneWork']);
			$ProfileGender = stripslashes($data['ProfileGender']);
			$ProfileTypeArray = stripslashes($data['ProfileType']);
			$ProfileDateBirth = stripslashes($data['ProfileDateBirth']);
			$ProfileLocationStreet = stripslashes($data['ProfileLocationStreet']);
			$ProfileLocationCity = stripslashes($data['ProfileLocationCity']);
			$ProfileLocationState = stripslashes($data['ProfileLocationState']);
			$ProfileLocationZip = stripslashes($data['ProfileLocationZip']);
			$ProfileLocationCountry = stripslashes($data['ProfileLocationCountry']);
			$ProfileDateUpdated = stripslashes($data['ProfileDateUpdated']);
			$ProfileType = $data['ProfileType'];
			$ProfileIsActive = stripslashes($data['ProfileIsActive']);
			$ProfileIsFeatured = stripslashes($data['ProfileIsFeatured']);
			$ProfileIsPromoted = stripslashes($data['ProfileIsPromoted']);
			$ProfileStatHits = stripslashes($data['ProfileStatHits']);
			$ProfileDateViewLast = stripslashes($data['ProfileDateViewLast']);
		}

		$caption_header = __("Edit", rb_agency_TEXTDOMAIN) . " " . LabelSingular;
		$caption_text = __("Make changes in the form below to edit a", rb_agency_TEXTDOMAIN) . " " . LabelSingular . ".";
	} else {

		// Set default values for new records
		$ProfilesModelDate = $date;
		$ProfileType = 1;
		$ProfileGender = "Unknown";
		$ProfileIsActive = 1;

		/*
		 * Pull Post Values and  Form value should not lost on error @Satya 12/12/2013
		 */
		if (isset($_POST['action'])) {
			$ProfileID = $_POST['ProfileID'];
			$ProfileUserLinked = $_POST['ProfileUserLinked'];
			$ProfileContactNameFirst = trim($_POST['ProfileContactNameFirst']);
			$ProfileContactNameLast = trim($_POST['ProfileContactNameLast']);
			$ProfileContactDisplay = trim($_POST['ProfileContactDisplay']);
			$ProfileGallery = $_POST['ProfileGallery'];
			$ProfileGender = $_POST['ProfileGender'];
			$ProfileDateBirth = $_POST['ProfileDateBirth'];
			$ProfileContactEmail = $_POST['ProfileContactEmail'];
			$ProfileUsername = $_POST["ProfileUsername"];
			$ProfilePassword = $_POST['ProfilePassword'];
			$ProfileContactWebsite = $_POST['ProfileContactWebsite'];
			$ProfileContactPhoneHome = $_POST['ProfileContactPhoneHome'];
			$ProfileContactPhoneCell = $_POST['ProfileContactPhoneCell'];
			$ProfileContactPhoneWork = $_POST['ProfileContactPhoneWork'];
			$ProfileLocationStreet = $_POST['ProfileLocationStreet'];
			$ProfileLocationCity = RBAgency_Common::format_propercase($_POST['ProfileLocationCity']);
			$ProfileLocationState = strtoupper($_POST['ProfileLocationState']);
			$ProfileLocationZip = $_POST['ProfileLocationZip'];
			$ProfileLocationCountry = $_POST['ProfileLocationCountry'];
			$ProfileLanguage = $_POST['ProfileLanguage'];
			$ProfileDateUpdated = $_POST['ProfileDateUpdated'];
			$ProfileDateViewLast = $_POST['ProfileDateViewLast'];
			$ProfileType = $_POST['ProfileType'];
			if (is_array($ProfileType)) {
				$ProfileType = implode(",", $ProfileType);
			}
			$ProfileIsActive = $_POST['ProfileIsActive']; // 0 Inactive | 1 Active | 2 Archived | 3 Pending Approval
			$ProfileIsFeatured = $_POST['ProfileIsFeatured'];
			$ProfileIsPromoted = $_POST['ProfileIsPromoted'];
			$ProfileStatHits = $_POST['ProfileStatHits'];

			// Get Primary Image
			$ProfileMediaPrimaryID = $_POST['ProfileMediaPrimary'];

			// Notify User and Admin
			$ProfileNotifyUser = $_POST["ProfileNotifyUser"];

		}

		$caption_header = __("Add New ", rb_agency_TEXTDOMAIN) . " " . LabelSingular;
		$caption_text = __("Fill in the form below to add a new", rb_agency_TEXTDOMAIN) . " " . LabelSingular . ".";
	}
	if ($_GET["action"] == "add") {
		echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&action=add&ProfileGender=" . $_GET["ProfileGender"] . "\">\n";
	} else {
		echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	}
?>
	<div id="welcome-panel" class="welcome-panel">
		<div class="welcome-panel-content">
			<?php if (!empty($ProfileID) && ($ProfileID > 0)) { ?>
			<a class="button button-primary button-hero" style="float: right; margin-top: 0px;" href="<?php echo rb_agency_PROFILEDIR . $rb_agency_UPLOADDIR . $ProfileGallery; ?>" target="_blank">Preview Model</a>
			<?php } ?>
			<h3><?php echo $caption_header; ?> <a class="button button-secondary" href="<?php echo admin_url("admin.php?page=" . $_GET['page']); ?>"><?php echo __("Back to " . LabelSingular . " List", rb_agency_TEXTDOMAIN); ?></a></h3>
			<p class="about-description"><?php echo $caption_text; ?> <strong><?php echo __("Required fields are marked", rb_agency_TEXTDOMAIN); ?> *</strong></p>
		</div>
	</div>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder columns-2">

			<!-- Row 1: Column Left Start -->

			<div id="postbox-container-1" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">

					<div id="dashboard_rbagency_profile_glance" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php echo __("Contact Information", rb_agency_TEXTDOMAIN); ?></span></h3>
						<div class="inside">
							<div class="main">
								<?php

								echo " <table class=\"form-table\">\n";
								echo "  <tbody>\n";
								if ((!empty($ProfileID) && ($ProfileID > 0)) || ($rb_agency_option_profilenaming == 2)) { // Editing Record
									echo "    <tr valign=\"top\">\n";
									echo "      <th scope=\"row\">" . __("Display Name", rb_agency_TEXTDOMAIN) . "</th>\n";
									echo "      <td>\n";
									echo "          <input type=\"text\" id=\"ProfileContactDisplay\" name=\"ProfileContactDisplay\" value=\"" . $ProfileContactDisplay . "\" />\n";
									if(isset($errorValidation['rb_agency_option_profilenaming'])){ echo "<p style='background-color: #FFEBE8; border-color: #CC0000;margin: 5px 0 15px;' >".$errorValidation['rb_agency_option_profilenaming']."</p>\n";} 
									echo "      </td>\n";
									echo "    </tr>\n";
								}
								if (!empty($ProfileID) && ($ProfileID > 0)) { // Editing Record
									echo "    <tr valign=\"top\">\n";
									echo "      <th scope=\"row\">" . __("Gallery Folder", rb_agency_TEXTDOMAIN) . "</th>\n";
									echo "      <td>\n";

									if (!empty($ProfileGallery) && is_dir(rb_agency_UPLOADPATH . $ProfileGallery)) {
										echo "<div id=\"message\"><span class=\"updated\">" . __("Folder", rb_agency_TEXTDOMAIN) . " <strong>" . $ProfileGallery . "</strong> " . __("Exists", rb_agency_TEXTDOMAIN) . "</span></div>\n";
										echo "<input type=\"hidden\" id=\"ProfileGallery\" name=\"ProfileGallery\" value=\"" . $ProfileGallery . "\" />\n";
									} else {
										echo "<input type=\"text\" id=\"ProfileGallery\" name=\"ProfileGallery\" value=\"" . $ProfileGallery . "\" />\n";
										echo "<div id=\"message\"><span class=\"error\">" . __("No Folder Exists", rb_agency_TEXTDOMAIN) . "</span>\n";
									}
									echo "              </div>\n";
									echo "      </td>\n";
									echo "  </tr>\n";
								}
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("First Name", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileContactNameFirst\" name=\"ProfileContactNameFirst\" value=\"" . $ProfileContactNameFirst . "\" />\n";
								if(isset($errorValidation['ProfileContactNameFirst'])){ echo "<p style='background-color: #FFEBE8; border-color: #CC0000;margin: 5px 0 15px;' >".$errorValidation['ProfileContactNameFirst']."</p>\n";} 
								echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Last Name", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileContactNameLast\" name=\"ProfileContactNameLast\" value=\"" . $ProfileContactNameLast . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";

								// password
								if ((isset($_GET["action"]) && $_GET["action"] == "add")) {
									$active = get_option('active_plugins');
									foreach($active as $act){
										if(preg_match('/rb-agency-interact\.php/',$act)){
											echo "    <tr valign=\"top\">\n";
											echo "      <th scope=\"row\">" . __("Username", rb_agency_TEXTDOMAIN) . "</th>\n";
											echo "      <td>\n";
											echo "          <input type=\"text\" id=\"ProfileUsername\" name=\"ProfileUsername\" />\n";
											if(isset($errorValidation['user_login'])){ echo "<p style='background-color: #FFEBE8; border-color: #CC0000;margin: 5px 0 15px;' >".$errorValidation['user_login']."</p>\n";} 
											echo "      </td>\n";
											echo "    </tr>\n";
											echo "    <tr valign=\"top\">\n";
											echo "      <th scope=\"row\">" . __("Password", rb_agency_TEXTDOMAIN) . "</th>\n";
											echo "      <td>\n";
											echo "          <input type=\"text\" id=\"ProfilePassword\" name=\"ProfilePassword\" />\n";
											echo "          <input type=\"button\" onclick=\"javascript:document.getElementById('ProfilePassword').value=Math.random().toString(36).substr(2,6);\" value=\"Generate Password\"  name=\"GeneratePassword\" />\n";
											if(isset($errorValidation['user_password'])){ echo "<p style='background-color: #FFEBE8; border-color: #CC0000;margin: 5px 0 15px;' >".$errorValidation['user_password']."</p>\n";} 
											echo "      </td>\n";
											echo "    </tr>\n";
											echo "    <tr valign=\"top\">\n";
											echo "      <th scope=\"row\">" . __("Send Login details?", rb_agency_TEXTDOMAIN) . "</th>\n";
											echo "      <td>\n";
											echo "          <input type=\"checkbox\"  name=\"ProfileNotifyUser\" /> Send login details to the new user and admin by email.\n";
											echo "      </td>\n";
											echo "    </tr>\n";
											break;
										}
									}
								}
								echo " </table>\n";

								?>
							</div>
						</div>
					</div>

					<div id="dashboard_account_information" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php echo  __("Account Information", rb_agency_TEXTDOMAIN); ?></span></h3>
						<div class="inside">
							<div class="main">
							<?php
							echo "<table class=\"form-table\">\n";
							echo " <tbody>\n";

							echo "    <tr valign=\"top\">\n";
							echo "      <th scope=\"row\">" . __("Classification", rb_agency_TEXTDOMAIN) . "</th>\n";
							echo "      <td>\n";
							echo "      <fieldset>\n";
							$ProfileType = (strpos(",", $ProfileType)!= -1) ? explode(",", $ProfileType) : $ProfileType;

							$query3 = "SELECT * FROM " . table_agency_data_type . " ORDER BY DataTypeTitle";
							$results3 = mysql_query($query3);
							$count3 = mysql_num_rows($results3);
							$action = @$_GET["action"];
							while ($data3 = mysql_fetch_array($results3)) {
								if ($action == "add") {
									echo "<input type=\"checkbox\" name=\"ProfileType[]\" value=\"" . $data3['DataTypeID'] . "\" id=\"ProfileType[]\"";
									if(is_array($ProfileType)){
											if (in_array($data3['DataTypeID'], $ProfileType)) {
												echo " checked=\"checked\"";
											} echo "/> " . $data3['DataTypeTitle'] . "<br />\n";
									} else {
											if ($data3['DataTypeID'] == $ProfileType) {
												echo " checked=\"checked\"";
											} echo "/> " . $data3['DataTypeTitle'] . "<br />\n";
									}
								}
								if ($action == "editRecord") {
									echo "<input type=\"checkbox\" name=\"ProfileType[]\" id=\"ProfileType[]\" value=\"" . $data3['DataTypeID'] . "\"";
									if(is_array($ProfileType)){
											if (in_array($data3['DataTypeID'], $ProfileType)) {
												echo " checked=\"checked\"";
											} echo "/> " . $data3['DataTypeTitle'] . "<br />\n";
									} else {
											if ($data3['DataTypeID'] == $ProfileType) {
												echo " checked=\"checked\"";
											} echo "/> " . $data3['DataTypeTitle'] . "<br />\n";
									}
								}
							}
							echo "      </fieldset>\n";
							if ($count3 < 1) {
								echo "" . __("No items to select", rb_agency_TEXTDOMAIN) . ". <a href='" . admin_url("admin.php?page=rb_agency_settings&ConfigID=5") . "'>" . __("Setup Options", rb_agency_TEXTDOMAIN) . "</a>\n";
							}

							echo "      </td>\n";
							echo "    </tr>\n";
							echo "    <tr valign=\"top\">\n";
							echo "        <th scope=\"row\">" . __("Status", rb_agency_TEXTDOMAIN) . ":</th>\n";
							echo "        <td><select id=\"ProfileIsActive\" name=\"ProfileIsActive\">\n";
							echo "            <option value=\"1\"" . selected(1, $ProfileIsActive) . ">" . __("Active", rb_agency_TEXTDOMAIN) . "</option>\n";
							echo "            <option value=\"4\"" . selected(4, $ProfileIsActive) . ">" . __("Active - Not Visible On Website", rb_agency_TEXTDOMAIN) . "</option>\n";
							echo "            <option value=\"0\"" . selected(0, $ProfileIsActive) . ">" . __("Inactive", rb_agency_TEXTDOMAIN) . "</option>\n";
							echo "            <option value=\"2\"" . selected(2, $ProfileIsActive) . ">" . __("Archived", rb_agency_TEXTDOMAIN) . "</option>\n";
							echo "            <option value=\"3\"" . selected(3, $ProfileIsActive) . ">" . __("Pending Approval", rb_agency_TEXTDOMAIN) . "</option>\n";
							echo "          </select></td>\n";
							echo "    </tr>\n";
							echo "    <tr valign=\"top\">\n";
							echo "        <th scope=\"row\">" . __("Promotion", rb_agency_TEXTDOMAIN) . ":</th>\n";
							echo "        <td>\n";
							echo "          <input type=\"checkbox\" name=\"ProfileIsFeatured\" id=\"ProfileIsFeatured\" value=\"1\"". checked($ProfileIsFeatured, 1, false) . " /> Featured<br />\n";
							echo "        </td>\n";
							echo "    </tr>\n";
							/*
							if (function_exists(rb_agencyinteract_approvemembers)) {
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Membership", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"checkbox\" name=\"ProfileIsPromoted\" id=\"ProfileIsPromoted\" value=\"1\"". checked($ProfileIsPromoted, 1, false) ." /> Rising Star<br />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
							}
							*/

							if (isset($ProfileUserLinked) && $ProfileUserLinked > 0) {
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("WordPress User", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "        <a href=\"". admin_url("user-edit.php") ."?user_id=". $ProfileUserLinked ."&wp_http_referer=%2Fwp-admin%2Fadmin.php%3Fpage%3Drb_agency_profiles\">ID# ". $ProfileUserLinked ."</a>";
								echo "        <input type='hidden' name='wpuserid' value='".$ProfileUserLinked."' />";
								echo "      </td>\n";
								echo "    </tr>\n";
							}


							// Hidden Settings
							if ($_GET["mode"] == "override") {
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Date Updated", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileDateUpdated\" name=\"ProfileDateUpdated\" value=\"" . $ProfileDateUpdated . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Profile Views", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileStatHits\" name=\"ProfileStatHits\" value=\"" . $ProfileStatHits . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Profile Viewed Last", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileDateViewLast\" name=\"ProfileDateViewLast\" value=\"" . $ProfileDateViewLast . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
							} else {
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\"></th>\n";
								echo "      <td>\n";
								echo "          <input type=\"hidden\" id=\"ProfileDateUpdated\" name=\"ProfileDateUpdated\" value=\"" . $ProfileDateUpdated . "\" />\n";
								echo "          <input type=\"hidden\" id=\"ProfileStatHits\" name=\"ProfileStatHits\" value=\"" . $ProfileStatHits . "\" />\n";
								echo "          <input type=\"hidden\" id=\"ProfileDateViewLast\" name=\"ProfileDateViewLast\" value=\"" . $ProfileDateViewLast . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
							}
							echo "  </tbody>\n";
							echo "</table>\n";

?>
							</div>
						</div>
					</div>

					<div id="dashboard_private_information" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php echo  __("Private Information", rb_agency_TEXTDOMAIN); ?></span></h3>
						<div class="inside">
							<div class="main">
								<?php

								echo " <table class=\"form-table\">\n";
								// Private Information
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Birthdate", rb_agency_TEXTDOMAIN) . " <em>YYYY-MM-DD</em></th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileDateBirth\" name=\"ProfileDateBirth\" value=\"" . $ProfileDateBirth . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Email Address", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileContactEmail\" name=\"ProfileContactEmail\" value=\"" . $ProfileContactEmail . "\" />\n";
								if(isset($errorValidation['ProfileContactEmail'])){ echo "<p style='background-color: #FFEBE8; border-color: #CC0000;margin: 5px 0 15px;' >".$errorValidation['ProfileContactEmail']."</p>\n";} 
									echo "          <input type=\"hidden\" id=\"ProfileContactEmail\" name=\"HiddenContactEmail\" value=\"" . $ProfileContactEmail . "\" />\n";
									echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Website", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileContactWebsite\" name=\"ProfileContactWebsite\" value=\"" . $ProfileContactWebsite . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Phone", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "      <fieldset>\n";
								echo "          <label>Home:</label><br /><input type=\"text\" id=\"ProfileContactPhoneHome\" name=\"ProfileContactPhoneHome\" value=\"" . $ProfileContactPhoneHome . "\" /><br />\n";
								echo "          <label>Cell:</label><br /><input type=\"text\" id=\"ProfileContactPhoneCell\" name=\"ProfileContactPhoneCell\" value=\"" . $ProfileContactPhoneCell . "\" /><br />\n";
								echo "          <label>Work:</label><br /><input type=\"text\" id=\"ProfileContactPhoneWork\" name=\"ProfileContactPhoneWork\" value=\"" . $ProfileContactPhoneWork . "\" /><br />\n";
								echo "      </fieldset>\n";
								echo "      </td>\n";
								echo "    </tr>\n";

								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Country", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";

								$query_get ="SELECT * FROM `". table_agency_data_country ."`" ;
								$result_query_get = $wpdb->get_results($query_get);
								$location= site_url();

								echo '<input type="hidden" id="url" value="'.$location.'">';
								echo "<select name=\"ProfileLocationCountry\" id=\"ProfileLocationCountry\"  onchange='javascript:populateStates(\"ProfileLocationCountry\",\"ProfileLocationState\");'>";
								echo '<option value="">'. __("Select country", rb_agency_TEXTDOMAIN) .'</option>';
								 foreach($result_query_get as $r){
									  $selected =$ProfileLocationCountry==$r->CountryID?"selected=selected":"";
									echo '<option '.$selected.' value='.$r->CountryID.' >'.$r->CountryTitle.'</option>';
								 }
								echo '</select>';
								echo "      </td>\n";
								echo "    </tr>\n";


								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("State", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								$query_get ="SELECT * FROM `".table_agency_data_state."`" ;
								$result_query_get = $wpdb->get_results($query_get);
								echo '<select name="ProfileLocationState" id="ProfileLocationState">';
								echo '<option value="">'. __("Select state", rb_agency_TEXTDOMAIN) .'</option>';
								 foreach($result_query_get as $r){
									 $selected =$ProfileLocationState==$r->StateID?"selected=selected":"";
									echo '<option '.$selected.' value='.$r->StateID.' >'.$r->StateTitle.'</option>';
								 }
								echo '</select>';

								echo "      </td>\n";
								echo "    </tr>\n";

								// Address
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Street", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileLocationStreet\" name=\"ProfileLocationStreet\" value=\"" . $ProfileLocationStreet . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";
								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("City", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileLocationCity\" name=\"ProfileLocationCity\" value=\"" . $ProfileLocationCity . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";


								echo "    <tr valign=\"top\">\n";
								echo "      <th scope=\"row\">" . __("Zip", rb_agency_TEXTDOMAIN) . "</th>\n";
								echo "      <td>\n";
								echo "          <input type=\"text\" id=\"ProfileLocationZip\" name=\"ProfileLocationZip\" value=\"" . $ProfileLocationZip . "\" />\n";
								echo "      </td>\n";
								echo "    </tr>\n";

								// Custom Admin Fields
								// ProfileCustomView = 1 , Private
								if (isset($_GET["ProfileGender"])) {
									$ProfileGender = $_GET["ProfileGender"];
									rb_custom_fields(1, 0, $ProfileGender, true);
								} else {
									rb_custom_fields(1, $ProfileID, $ProfileGender, true);
								}
								echo " </table>\n";

								?>
							</div>
						</div>
					</div>


				</div>
			</div>

			<!-- Row 1: Column Left End -->

			<!-- Row 1: Column Right Start -->

			<div id="postbox-container-2" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">

					<div id="dashboard_at_a_glance" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span>At a Glance</span></h3>
						<div class="inside">
							<div class="main">


								<ul>
									<li class="post-count"><a href="edit.php?post_type=post">7 Posts</a></li>
									<li class="page-count"><a href="edit.php?post_type=page">1 Page</a></li>
									<li class="comment-count"><a href="edit-comments.php">17 Comments</a></li>
									<li class="comment-mod-count"><a href="edit-comments.php?comment_status=moderated">15 in moderation</a></li>
								</ul>
								<p>WordPress 3.8.1 running <a href="themes.php">Twenty Eleven</a> theme.</p>


							</div>
						</div>
					</div>

					<div id="dashboard_public_information" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php echo  __("Public Information", rb_agency_TEXTDOMAIN); ?></span></h3>
						<div class="inside">
							<div class="main">
<?php
							// Public Information
							echo "    <table class=\"rbform-table\">\n";
							echo "    <tr valign=\"top\">\n";
							echo "      <th scope=\"row\">" . __("Gender", rb_agency_TEXTDOMAIN) . "</th>\n";
							echo "      <td>";
							echo "			<select name=\"ProfileGender\" id=\"ProfileGender\">\n";

							$ProfileGender1 = get_user_meta($ProfileUserLinked, "rb_agency_interact_pgender", true);

							if($ProfileGender==""){
								$ProfileGender = $_GET["ProfileGender"];
							}elseif($ProfileGender1!=""){
								$ProfileGender =$ProfileGender1 ;
							}

							$query1 = "SELECT GenderID, GenderTitle FROM " . table_agency_data_gender . "";
							$results1 = mysql_query($query1);
							$count1 = mysql_num_rows($results1);
							if ($count1 > 0) {
								if (empty($GenderID) || ($GenderID < 1)) {
									echo " <option value=\"0\" selected>--</option>\n";
								}
								while ($data1 = mysql_fetch_array($results1)) {
									echo " <option value=\"" . $data1["GenderID"] . "\" " . selected($ProfileGender, $data1["GenderID"]) . ">" . $data1["GenderTitle"] . "</option>\n";
								}
								echo "</select>\n";
							} else {
								echo "" . __("No items to select", rb_restaurant_TEXTDOMAIN) . ".";
							}
							echo "        </td>\n";
							echo "    </tr>\n";
							// Load custom fields , Public  = 0, ProfileCustomGender = true
							// ProfileCustomView = 1 , Private
							if (isset($_GET["ProfileGender"])) {
								$ProfileGender = $_GET["ProfileGender"];
								rb_custom_fields(0, 0, $ProfileGender, true);
							} else {
								rb_custom_fields(0, $ProfileID, $ProfileGender, true);
							}

							echo "  </tbody>\n";
							echo " </table>\n";

?>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- Row 1: Column Right End -->


		</div>

		<div id="dashboard-widgets" class="metabox-holder columns-1">

			<!-- Row 2: Column Left Start -->

			<div id="postbox-container-3" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">

					<div id="dashboard_gallery" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span>Gallery</span></h3>
						<div class="inside">
							<div class="main">
							<?php 

							if (!empty($ProfileID) && ($ProfileID > 0)) { // Editing Record
								//echo "      <h3>" . __("Gallery", rb_agency_TEXTDOMAIN) . "</h3>\n";

								echo "<script type='text/javascript'>\n";
								echo "function confirmDelete(delMedia,mediaType) {\n";
								echo "  if (confirm('Are you sure you want to delete this '+mediaType+'?')) {\n";
								echo "  document.location= '" . admin_url("admin.php?page=" . $_GET['page']) . "&action=editRecord&ProfileID=" . $ProfileID . "&actionsub=photodelete&targetid='+delMedia;";
								echo "  }\n";
								echo "}\n";
								echo "</script>\n";

								//mass delte
								if ($_GET["actionsub"] == "massphotodelete" && is_array($_GET['targetids'])) {
									$massmediaids = '';
									$massmediaids = implode(",", $_GET['targetids']);
									//get all the images

									$queryImgConfirm = "SELECT ProfileMediaID,ProfileMediaURL FROM " . table_agency_profile_media . " WHERE ProfileID = $ProfileID AND ProfileMediaID IN ($massmediaids) AND ProfileMediaType = 'Image'";
									$resultsImgConfirm = mysql_query($queryImgConfirm);
									$countImgConfirm = mysql_num_rows($resultsImgConfirm);
									$mass_image_data = array();
									while ($dataImgConfirm = mysql_fetch_array($resultsImgConfirm)) {
										$mass_image_data[$dataImgConfirm['ProfileMediaID']] = $dataImgConfirm['ProfileMediaURL'];
									}
									//delete all the images from database
									$massmediaids = implode(",", array_keys($mass_image_data));
									$queryMassImageDelete = "DELETE FROM " . table_agency_profile_media . " WHERE ProfileID = $ProfileID AND ProfileMediaID IN ($massmediaids) AND ProfileMediaType = 'Image'";
									$resultsMassImageDelete = $wpdb->query($queryMassImageDelete);
									//delete images on the disk
									$dirURL = rb_agency_UPLOADPATH . $ProfileGallery;
									foreach ($mass_image_data as $mid => $ProfileMediaURL) {
										if (!unlink($dirURL . "/" . $ProfileMediaURL)) {
											echo ("<div id=\"message\" class=\"error\"><p>" . __("Error removing", rb_agency_TEXTDOMAIN) . " <strong>" . $ProfileMediaURL . "</strong>. " . __("File did not exist.", rb_agency_TEXTDOMAIN) . ".</p></div>");
										} else {
											echo ("<div id=\"message\" class=\"updated\"><p>File <strong>'. $ProfileMediaURL .'</strong> " . __("successfully removed", rb_agency_TEXTDOMAIN) . ".</p></div>");
										}
									}
								}

								// Are we deleting?
								if ($_GET["actionsub"] == "photodelete") {
									$deleteTargetID = $_GET["targetid"];

									// Verify Record
									$queryImgConfirm = "SELECT * FROM " . table_agency_profile_media . " WHERE ProfileID =  \"" . $ProfileID . "\" AND ProfileMediaID =  \"" . $deleteTargetID . "\"";
									$resultsImgConfirm = mysql_query($queryImgConfirm);
									$countImgConfirm = mysql_num_rows($resultsImgConfirm);
									while ($dataImgConfirm = mysql_fetch_array($resultsImgConfirm)) {
										$ProfileMediaID = $dataImgConfirm['ProfileMediaID'];
										$ProfileMediaType = $dataImgConfirm['ProfileMediaType'];
										$ProfileMediaURL = $dataImgConfirm['ProfileMediaURL'];

										// Remove Record
										$delete = "DELETE FROM " . table_agency_profile_media . " WHERE ProfileID =  \"" . $ProfileID . "\" AND ProfileMediaID=$ProfileMediaID";
										$results = $wpdb->query($delete);

										if ($ProfileMediaType == "Demo Reel" || $ProfileMediaType == "Video Monologue" || $ProfileMediaType == "Video Slate") {
											echo ("<div id=\"message\" class=\"updated\"><p>File <strong>'. $ProfileMediaURL .'</strong> " . __("successfully removed", rb_agency_TEXTDOMAIN) . ".</p></div>");
										} else {
											// Remove File
											$dirURL = rb_agency_UPLOADPATH . $ProfileGallery;
											if (!unlink($dirURL . "/" . $ProfileMediaURL)) {
												echo ("<div id=\"message\" class=\"error\"><p>" . __("Error removing", rb_agency_TEXTDOMAIN) . " <strong>" . $ProfileMediaURL . "</strong>. " . __("File did not exist.", rb_agency_TEXTDOMAIN) . ".</p></div>");
											} else {
												echo ("<div id=\"message\" class=\"updated\"><p>File <strong>'. $ProfileMediaURL .'</strong> " . __("successfully removed", rb_agency_TEXTDOMAIN) . ".</p></div>");
											}
										}
									} // is there record?
								}
								// Go about our biz-nazz
								# rb_agency_option_galleryorder
								# 1 - recent 0 - chronological
								$rb_agency_options_arr = get_option('rb_agency_options');
								$order = $rb_agency_options_arr['rb_agency_option_galleryorder'];
								$queryImg = rb_agency_option_galleryorder_query($order ,$ProfileID,"Image");
								$resultsImg = mysql_query($queryImg);
								$countImg = mysql_num_rows($resultsImg);
								while ($dataImg = mysql_fetch_array($resultsImg)) {
									if ($dataImg['ProfileMediaPrimary']) {
										$styleBackground = "#900000";
										$isChecked = " checked";
										$isCheckedText = " Primary";
										if ($countImg == 1) {
											$toDelete = "  <div class=\"delete\"><a href=\"javascript:confirmDelete('" . $dataImg['ProfileMediaID'] . "','" . $dataImg['ProfileMediaType'] . "')\"><span>Delete</span> &raquo;</a></div>\n";
										} else {
											$toDelete = "";
											$massDelete = "";
										}
									} else {
										$styleBackground = "#fff";
										$isChecked = "";
										$isCheckedText = " Set Primary";
										$toDelete = "  <div class=\"delete\"><a href=\"javascript:confirmDelete('" . $dataImg['ProfileMediaID'] . "','" . $dataImg['ProfileMediaType'] . "')\"><span>Delete</span> &raquo;</a></div>\n";
										$massDelete = '<input type="checkbox" name="massgaldel" value="' . $dataImg['ProfileMediaID'] . '"> Select';
									}
									echo "<div class=\"profileimage\" style=\"height: auto !important; background: " . $styleBackground . "; \">\n" . $toDelete . "";
									echo "  <img src=\"" . rb_agency_UPLOADDIR . $ProfileGallery . "/" . $dataImg['ProfileMediaURL'] . "\" style=\"width: 100px; z-index: 1; \" />\n"; 
									echo "  <div class=\"primary\" style=\"background: " . $styleBackground . "; \">";

									echo "    <input type=\"radio\" name=\"ProfileMediaPrimary\" value=\"" . $dataImg['ProfileMediaID'] . "\" " . $isChecked . " /> " . $isCheckedText . "";
									echo "    <div>$massDelete</div>";
									echo "    Order: <input type=\"text\" name=\"ProfileMediaOrder_" . $dataImg['ProfileMediaID'] . "\" style=\"width: 25px\" value=\"" . $dataImg['ProfileMediaOrder'] . "\" />";
									echo "  </div>\n";
									echo "  </div>\n";

								}
								if ($countImg < 1) {
									echo "<div>" . __("There are no images loaded for this profile yet.", rb_agency_TEXTDOMAIN) . "</div>\n";
								}

								echo "      <div style=\"clear: both;\"></div>\n";
								echo '<a href="javascript:confirm_mass_gallery_delete();">Delete Selected Images</a>';
								echo '<script language="javascript">';
								echo 'function confirm_mass_gallery_delete(){';
								echo 'jQuery(document).ready(function() {';
								echo "var mas_del_ids = '&';";
								echo 'jQuery("input:checkbox[name=massgaldel]:checked").each(function() {';
								echo "if(mas_del_ids != '&'){";
								echo "mas_del_ids += '&';";
								echo '}';

								echo "mas_del_ids += 'targetids[]='+jQuery(this).val();";
								echo "});";

								echo "if( mas_del_ids != '&'){ ";
								echo 'if(confirm("Do you want to delete all the selected images?")){';



								echo "urlmassdelete = '" . admin_url("admin.php?page=" . $_GET['page']) . "&action=editRecord&ProfileID=" . $ProfileID . "&actionsub=massphotodelete' + mas_del_ids;";
								echo 'document.location = urlmassdelete;';
								echo '}
								}
								else{
									alert("You have to select images to delete");
								}
							});

							}
							</script>';

							?>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- Row 1: Column Right End -->


		</div>

		<div id="dashboard-widgets" class="metabox-holder columns-1">

			<!-- Row 2: Column Left Start -->

			<div id="postbox-container-3" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">

					<div id="dashboard_media" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span>Media</span></h3>
						<div class="inside">
							<div class="main">

							<?php
							echo "      <p>" . __("The following files (pdf, audio file, etc.) are associated with this record", rb_agencyinteract_TEXTDOMAIN) . ".</p>\n";

							$queryMedia = "SELECT * FROM " . table_agency_profile_media . " WHERE ProfileID =  \"" . $ProfileID . "\" AND ProfileMediaType <> \"Image\"";
							$resultsMedia = mysql_query($queryMedia);
							$countMedia = mysql_num_rows($resultsMedia);
							while ($dataMedia = mysql_fetch_array($resultsMedia)) {
								if ($dataMedia['ProfileMediaType'] == "Demo Reel" || $dataMedia['ProfileMediaType'] == "Video Monologue" || $dataMedia['ProfileMediaType'] == "Video Slate") {
									if($dataMedia['ProfileVideoType'] == "" || $dataMedia['ProfileVideoType'] == "youtube"){
										$outVideoMedia .= "<div style=\"float: left; width: 120px; text-align: center; padding: 10px; \">" . $dataMedia['ProfileMediaType'] . "<br />" . rb_agency_get_videothumbnail($dataMedia['ProfileMediaURL']) . "<br /><a href=\"http://www.youtube.com/watch?v=" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">Link to Video</a><br />[<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
									}elseif($dataMedia['ProfileVideoType'] == "vimeo"){
										$json = file_get_contents('http://vimeo.com/api/v2/video/'.$dataMedia['ProfileMediaURL'].'.json');
										$data = json_decode($json,true);
			 							$outVideoMedia .= "<div style=\"float: left; width: 120px; text-align: center; padding: 10px; \">" . $dataMedia['ProfileMediaType'] . "<br /><img src='" . $data[0]['thumbnail_small'] . "'><br /><a href=\"http://vimeo.com/" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">Link to Video</a><br />[<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
			 						}
			 					} elseif ($dataMedia['ProfileMediaType'] == "VoiceDemo") {
									$outLinkVoiceDemo .= "<div>" . $dataMedia['ProfileMediaType'] . ": <a href=\"" . rb_agency_UPLOADDIR . $ProfileGallery . "/" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">" . $dataMedia['ProfileMediaTitle'] . "</a> [<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
								} elseif ($dataMedia['ProfileMediaType'] == "Resume") {
									$outLinkResume .= "<div>" . $dataMedia['ProfileMediaType'] . ": <a href=\"" . rb_agency_UPLOADDIR . $ProfileGallery . "/" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">" . $dataMedia['ProfileMediaTitle'] . "</a> [<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
								} elseif ($dataMedia['ProfileMediaType'] == "Headshot") {
									$outLinkHeadShot .= "<div>" . $dataMedia['ProfileMediaType'] . ": <a href=\"" . rb_agency_UPLOADDIR . $ProfileGallery . "/" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">" . $dataMedia['ProfileMediaTitle'] . "</a> [<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
								} elseif ($dataMedia['ProfileMediaType'] == "CompCard") {
									$outLinkComCard .= "<div>" . $dataMedia['ProfileMediaType'] . ": <a href=\"" . rb_agency_UPLOADDIR . $ProfileGallery . "/" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">" . $dataMedia['ProfileMediaTitle'] . "</a> [<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
								} else {
									$outCustomMediaLink .= "<div>" . $dataMedia['ProfileMediaType'] . ": <a href=\"" . rb_agency_UPLOADDIR . $ProfileGallery . "/" . $dataMedia['ProfileMediaURL'] . "\" target=\"_blank\">" . $dataMedia['ProfileMediaTitle'] . "</a> [<a href=\"javascript:confirmDelete('" . $dataMedia['ProfileMediaID'] . "','" . $dataMedia['ProfileMediaType'] . "')\">DELETE</a>]</div>\n";
								}
							}
							echo '<div class="media-files">';
							echo '<div class="media-file">';
							echo $outLinkVoiceDemo;
							echo '</div>';
							echo '<div class="media-file">';
							echo $outLinkResume;
							echo '</div>';
							echo '<div class="media-file">';
							echo $outLinkHeadShot;
							echo '</div>';
							echo '<div class="media-file">';
							echo $outLinkComCard;
							echo '</div>';
							echo '<div class="media-file">';
							echo $outCustomMediaLink;
							echo '</div>';
							echo '</div>';
							
							echo $outVideoMedia;

							if ($countMedia < 1) {
								echo "<div><em>" . __("There are no additional media linked", rb_agencyinteract_TEXTDOMAIN) . "</em></div>\n";
							}
							?>
							</div>
						</div>
					</div>

				</div>
			</div>

		</div>

		<div id="dashboard-widgets" class="metabox-holder columns-2">

			<!-- Row 4: Column Left Start -->

			<div id="postbox-container-5" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">

					<div id="dashboard_upload_images" class="postbox ">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php echo __("Upload Images", rb_agency_TEXTDOMAIN); ?></span></h3>
						<div class="inside">
							<div class="main">
								<?php
											// Upload Images
								echo "      <p>" . __("Upload new media using the forms below", rb_agency_TEXTDOMAIN) . ".</p>\n";
								if(isset($errorValidation['profileMedia'])){ echo "<p style='background-color: #FFEBE8; border-color: #CC0000;margin: 5px 0 15px;' >".$errorValidation['profileMedia']."</p>\n";} 
								echo "<table class=\"rbform-table\">";
								for ($i = 1; $i < 10; $i++) {
									echo "<tr><th colspan=\"2\">Type:</th></tr><tr><td><select name=\"profileMedia" . $i . "Type\"><option value=\"Image\">Image</option><option value=\"Headshot\">Headshot</option><option value=\"CompCard\">Comp Card</option><option value=\"Resume\">Resume</option><option value=\"VoiceDemo\">Voice Demo</option><option value=\"Polaroid\">Polaroid</option>";
									rb_agency_getMediaCategories($ProfileGender);
									echo"</select></td><td><input type='file' id='profileMedia" . $i . "' name='profileMedia" . $i . "' /></td></tr>\n";
								}
								echo "</table>";

								?>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div id="postbox-container-6" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">


					<div id="dashboard_line_to_videos" class="postbox ">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle"><span><?php echo  __("Link to Videos", rb_agency_TEXTDOMAIN); ?></span></h3>
						<div class="inside">
							<div class="main">
								<?php
								// Add Videos
								echo "      <p>" . __("Paste the video URL below", rb_agency_TEXTDOMAIN) . ".</p>\n";

								echo "<table class=\"rbform-table\">
										<tr valign=\"top\">
										<th>
										Type: 
											<select name=\"profileMediaV1Type\">
												<option selected>" . __("Video Slate", rb_agency_TEXTDOMAIN) . "</option>
												<option>" . __("Video Monologue", rb_agency_TEXTDOMAIN) . "</option>
												<option>" . __("Demo Reel", rb_agency_TEXTDOMAIN) . "</option></select>
										</th>
										<td>
											<table>
												<tr><td>Video ID: </td><td><input type='text' id='profileMediaV1' name='profileMediaV1'></td></tr>
												<tr><td>Title: </td><td><input type='text' name='media1_title'></td></tr>
												<tr><td>Caption </td><td><input type='text' name='media1_caption'></td></tr>
												<tr><td>Video Type </td><td><input type='radio' name='media1_vtype' value='youtube' checked>&nbsp; Youtube <br/>
													<input type='radio' name='media1_vtype' value='vimeo' >&nbsp; Vimeo</td></tr>
											</table>
										</td>
										</tr>
											</table>";

								echo "<table class=\"rbform-table\">
										<tr valign=\"top\">
										<th>
										Type: 
											<select name=\"profileMediaV2Type\">
												<option>" . __("Video Slate", rb_agency_TEXTDOMAIN) . "</option>
												<option selected>" . __("Video Monologue", rb_agency_TEXTDOMAIN) . "</option>
												<option>" . __("Demo Reel", rb_agency_TEXTDOMAIN) . "</option></select>
										</th>	
										<td>
											<table>
												<tr><td>Video ID: </td><td><input type='text' id='profileMediaV2' name='profileMediaV2'></td></tr>
												<tr><td>Title: </td><td><input type='text' name='media2_title'></td></tr>
												<tr><td>Caption</td><td><input type='text' name='media2_caption'></td></tr>
												<tr><td>Video Type</td><td><input type='radio' name='media2_vtype' value='youtube' checked>&nbsp; Youtube <br/>
													<input type='radio' name='media2_vtype' value='vimeo'>&nbsp; Vimeo </td>
												</tr>				
											</table>
										</td>
										</table>";
								echo "<table class=\"rbform-table\">
										<tr valign=\"top\">
										<th>
										Type: 
											<select name=\"profileMediaV3Type\">
												<option>" . __("Video Slate", rb_agency_TEXTDOMAIN) . "</option>
												<option selected>" . __("Video Monologue", rb_agency_TEXTDOMAIN) . "</option>
												<option>" . __("Demo Reel", rb_agency_TEXTDOMAIN) . "</option></select>
										</th>
										<td>
											<table>
												<tr><td>Video ID: </td><td><input type='text' id='profileMediaV3' name='profileMediaV3'></td></tr>
												<tr><td>Title: </td><td><input type='text' name='media3_title'></td></tr>
												<tr><td>Caption</td><td><input type='text' name='media3_caption'></td></tr>
												<tr><td>Video Type</td><td><input type='radio' name='media3_vtype' value='youtube' checked>&nbsp; Youtube <br/>
													<input type='radio' name='media3_vtype' value='vimeo'>&nbsp; Vimeo </td></tr>
											</table>
										</td>
										</table>";
								}
							?>
							</div>
						</div>
					</div>

					<!--  -->

				</div>
			</div>

			<!-- Row 2: Column Right End -->

		</div>




<?php


	if (!empty($ProfileID) && ($ProfileID > 0)) {

		echo "<div class=\"rbtool-box\">\n";
		echo "<p class=\"submit\">\n";
		echo "" . __("Last updated on", rb_agency_TEXTDOMAIN) . ": " . $ProfileDateUpdated . "\n";
		echo "     <input type=\"hidden\" name=\"ProfileID\" value=\"" . $ProfileID . "\" />\n";
		echo "     <input type=\"hidden\" name=\"action\" value=\"editRecord\" />\n";
		echo "     <br /><br /><input type=\"submit\" name=\"submit\" value=\"" . __("Update Record", rb_agency_TEXTDOMAIN) . "\" class=\"button-primary\" />\n";
		echo "</p>\n";
		echo "</div>\n";
	} else {
		echo "<div class=\"rbtool-box\">\n";
		echo "<p class=\"submit\">\n";
		echo "     <input type=\"hidden\" name=\"action\" value=\"addRecord\" />\n";
		echo "     <input type=\"submit\" name=\"submit\" value=\"" . __("Create Record", rb_agency_TEXTDOMAIN) . "\" class=\"button-primary\" />\n";
		echo "</p>\n";
		echo "</div>\n";
	}
	echo "</form>\n";
}

// End Manage



/* List Records **************************************************** */

function rb_display_list() {
	global $wpdb;
	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_locationtimezone = (int) $rb_agency_options_arr['rb_agency_option_locationtimezone'];
	echo "<div class=\"wrap\">\n";
	// Include Admin Menu
	include ("admin-include-menu.php");

	// Sort By
	$sort = "";
	if (isset($_GET['sort']) && !empty($_GET['sort'])) {
		$sort = $_GET['sort'];
	} else {
		$sort = "profile.ProfileContactNameFirst";
	}

	// Sort Order
	$dir = "";
	if (isset($_GET['dir']) && !empty($_GET['dir'])) {
		$dir = $_GET['dir'];
		if ($dir == "desc" || !isset($dir) || empty($dir)) {
			$sortDirection = "asc";
		} else {
			$sortDirection = "desc";
		}
	} else {
		$sortDirection = "desc";
		$dir = "asc";
	}

	  // Filter
	  $filter = "WHERE ";
		if ((isset($_GET['ProfileContactNameFirst']) && !empty($_GET['ProfileContactNameFirst'])) || isset($_GET['ProfileContactNameLast']) && !empty($_GET['ProfileContactNameLast'])){
			if (isset($_GET['ProfileContactNameFirst']) && !empty($_GET['ProfileContactNameFirst'])){
			$selectedNameFirst = $_GET['ProfileContactNameFirst'];
			$query .= "&ProfileContactNameFirst=". $selectedNameFirst ."";

			  if(strpos($filter,'profile') > 0){
					$filter .= " AND profile.ProfileContactNameFirst LIKE '". $selectedNameFirst ."%'";
			  } else {
					$filter .= " profile.ProfileContactNameFirst LIKE '". $selectedNameFirst ."%'";
			  }
			}
			if (isset($_GET['ProfileContactNameLast']) && !empty($_GET['ProfileContactNameLast'])){
			$selectedNameLast = $_GET['ProfileContactNameLast'];
			$query .= "&ProfileContactNameLast=". $selectedNameLast ."";
				if(strpos($filter,'profile') > 0){
					   $filter .= " AND profile.ProfileContactNameLast LIKE '". $selectedNameLast ."%'";
				} else {
					   $filter .= " profile.ProfileContactNameLast LIKE '". $selectedNameLast ."%'";
				}
			}
		}
		if (isset($_GET['ProfileLocationCity']) && !empty($_GET['ProfileLocationCity'])){
			$selectedCity = $_GET['ProfileLocationCity'];
			$query .= "&ProfileLocationCity=". $selectedCity ."";
			if(strpos($filter,'profile') > 0){
					$filter .= " AND profile.ProfileLocationCity='". $selectedCity ."'";
			} else {
					$filter .= " profile.ProfileLocationCity='". $selectedCity ."'";
			}
		}
		if (isset($_GET['ProfileType']) && !empty($_GET['ProfileType'])){
			$selectedType = strtolower($_GET['ProfileType']);
			$query .= "&ProfileType=". $selectedType ."";
						if(strpos($filter,'profile') > 0){
							$filter .= " AND FIND_IN_SET('". $selectedType ."', profile.ProfileType)";
						} else {
							$filter .= " FIND_IN_SET('". $selectedType ."', profile.ProfileType)";
						}
		}
		if (isset($_GET['ProfileVisible'])){
			$selectedVisible = $_GET['ProfileVisible'];
			$query .= "&ProfileVisible=". $selectedVisible ."";
			if($_GET['ProfileVisible'] != ""){
					if(strpos($filter,'profile') > 0){
							$filter .= " AND profile.ProfileIsActive = '". $selectedVisible ."'" ;
					} else {
							$filter .= " profile.ProfileIsActive = '". $selectedVisible . "'" ;
					}
			}
		}
		if (isset($_GET['ProfileGender']) && !empty($_GET['ProfileGender'])){
			$ProfileGender = (int)$_GET['ProfileGender'];
			if($ProfileGender)
			  if(strpos($filter,'profile') > 0){
					$filter .= " AND profile.ProfileGender='".$ProfileGender."'";
			  } else {
					$filter .= " profile.ProfileGender='".$ProfileGender."'";
			  }
		}

		/*
		 * Trap WHERE 
		 */
		if(!strpos($filter, 'profile') > 0){
				$filter = "";
		}


	//Paginate
	$items = mysql_num_rows(mysql_query("SELECT * FROM " . table_agency_profile . " profile LEFT JOIN " . table_agency_data_type . " profiletype ON profile.ProfileType = profiletype.DataTypeID " . $filter . "")); // number of total rows in the database
	if ($items > 0) {
		$p = new rb_agency_pagination;
		$p->items($items);
		$p->limit(50); // Limit entries per page
		$p->target("admin.php?page=" . $_GET['page'] . $query);
		$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
		$p->calculate(); // Calculates what to show
		$p->parameterName('paging');
		$p->adjacents(1); //No. of page away from the current page

		if (!isset($_GET['paging'])) {
			$p->page = 1;
		} else {
			$p->page = $_GET['paging'];
		}

		//Query for limit paging
		$limit = "LIMIT " . ($p->page - 1) * $p->limit . ", " . $p->limit;
	} else {
		$limit = "";
	}

	/*
	 * Add New Records
	 */
?>
<script type="text/javascript">
jQuery(document).ready(function(){
		jQuery('.imperial_metrics').keyup(function(){
				var vals = jQuery(this).val();
				var new_val = extractNumber(vals,2,false);
				if(new_val !== true){
						jQuery(this).nextAll('.error_msg').eq(0).html('*Non numeric value is not accepted');
						new_val.replace(/[^/\d*\.*]/g,'');
						jQuery(this).val(new_val);
				}
		});
		jQuery('.imperial_metrics').focusout(function(){
				var vals = jQuery(this).val();
				var new_val = extractNumber(vals,2,false);
				if(new_val !== true){
						jQuery(this).nextAll('.error_msg').eq(0).html('*Non numeric value is not accepted');
						new_val.replace(/[^/\d*\.*]/g,'');
						jQuery(this).val(new_val);
				} else {
						jQuery(this).nextAll('.error_msg').eq(0).html('');
				}
		});		
});
function extractNumber(obj, decimalPlaces, allowNegative)
{
		var temp = obj; var reg0Str = '[0-9]*';
		if (decimalPlaces > 0) { 
				reg0Str += '\\.?[0-9]{0,' + decimalPlaces + '}';
		} else if (decimalPlaces < 0) {
				reg0Str += '\\.?[0-9]*';
		}
		reg0Str = allowNegative ? '^-?' + reg0Str : '^' + reg0Str;
		reg0Str = reg0Str + '$';
		var reg0 = new RegExp(reg0Str);
		if (reg0.test(temp)) return true;
		var reg1Str = '[^0-9' + (decimalPlaces != 0 ? '.' : '') + (allowNegative ? '-' : '') + ']';
		var reg1 = new RegExp(reg1Str, 'g');
		temp = temp.replace(reg1, '');
		if (allowNegative) {
				var hasNegative = temp.length > 0 && temp.charAt(0) == '-';
				var reg2 = /-/g;
				temp = temp.replace(reg2, '');
				if (hasNegative) temp = '-' + temp;
		}
		if (decimalPlaces != 0) {
				var reg3 = /\./g;
				var reg3Array = reg3.exec(temp);
				if (reg3Array != null) {
						var reg3Right = temp.substring(reg3Array.index + reg3Array[0].length);
						reg3Right = reg3Right.replace(reg3, '');
						reg3Right = decimalPlaces > 0 ? reg3Right.substring(0, decimalPlaces) : reg3Right;
						temp = temp.substring(0,reg3Array.index) + '.' + reg3Right;
				}
		}

		return temp;
}
</script>

	<div id="dashboard-widgets" class="metabox-holder columns-2">

		<div id="postbox-container-1" class="postbox-container" style="width: 29%;">
			<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="margin: 0px;">

				<div id="dashboard_right_now" class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php echo __("Create New Profile", rb_agency_TEXTDOMAIN); ?></span></h3>
					<div class="inside-x" style="padding: 10px 10px 0px 10px; ">
						<?php echo __("Currently " . $items . " Profiles", rb_agency_TEXTDOMAIN); ?><br />
						<?php

							$queryGenderResult = mysql_query("SELECT GenderID, GenderTitle FROM " . table_agency_data_gender . " ");
							$queryGenderCount = mysql_num_rows($queryGenderResult);
							echo "<p>";
							while ($fetchGender = mysql_fetch_assoc($queryGenderResult)) {
								echo "<a class=\"button-primary\" href=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&action=add&ProfileGender=" . $fetchGender["GenderID"] . "\">" . __("Create New " . ucfirst($fetchGender["GenderTitle"]) . "", rb_agency_TEXTDOMAIN) . "</a>\n";
							}
							echo "</p>";
							if ($queryGenderCount < 1) {
								echo "<p>" . __("No Gender Found. <a href=\"" . admin_url("admin.php?page=rb_agency_settings&ampConfigID=5") . "\">Create New Gender</a>", rb_agency_TEXTDOMAIN) . "</p>\n";
							}
						?>

					</div>
				</div>

			</div>
		</div>

		<div id="postbox-container-2" class="postbox-container" style="width: 70%">
			<div id="side-sortables" class="meta-box-sortables ui-sortable" style="margin: 0px;">

				<div id="dashboard_recent_drafts" class="postbox" style="display: block;">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php echo __("Filter Profiles", rb_agency_TEXTDOMAIN ) ?></span></h3>
					<div class="inside">

<?php

	/*
	 * Filtering Records
	 */

	echo "          <form style=\"display: inline;\" method=\"GET\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	echo "              <input type=\"hidden\" name=\"page_index\" id=\"page_index\" value=\"" . $_GET['page_index'] . "\" />\n";
	echo "              <input type=\"hidden\" name=\"page\" id=\"page\" value=\"" . $_GET['page'] . "\" />\n";
	echo "              <input type=\"hidden\" name=\"type\" value=\"name\" />\n";
	echo "              <p id=\"filter-profiles\">\n";
	echo "              <span>" . __("<label>First Name:</label>", rb_agency_TEXTDOMAIN) . "<input type=\"text\" name=\"ProfileContactNameFirst\" value=\"" . $selectedNameFirst . "\" /></span>\n";
	echo "              <span>" . __("<label>Last Name:</label>", rb_agency_TEXTDOMAIN) . "<input type=\"text\" name=\"ProfileContactNameLast\" value=\"" . $selectedNameLast . "\" /></span>\n";

	echo "              <span>" . __("<label>Category:</label>", rb_agency_TEXTDOMAIN) . "\n";
	echo "              <select name=\"ProfileType\">\n";
	echo "                <option value=\"\">" . __("Any Category", rb_agency_TEXTDOMAIN) . "</option>";

	$query = "SELECT DataTypeID, DataTypeTitle FROM " . table_agency_data_type . " ORDER BY DataTypeTitle ASC";
	$results = mysql_query($query);
	$count = mysql_num_rows($results);
	while ($data = mysql_fetch_array($results)) {
		echo "<option value=\"" . $data['DataTypeID'] . "\" " . selected($_GET['ProfileType'], $data["DataTypeID"]) . "\">" . $data['DataTypeTitle'] . "</option>\n";
	}
	echo "              </select></span>\n";
	echo "              <span>" . __("Status", rb_agency_TEXTDOMAIN) . ":\n";
	echo "              <select name=\"ProfileVisible\">\n";
	echo "                <option value=\"\">" . __("Any Status", rb_agency_TEXTDOMAIN) . "</option>";
	echo "                <option value=\"1\"" . selected(1, $selectedVisible) . ">" . __("Active", rb_agency_TEXTDOMAIN) . "</option>\n";
	echo "                <option value=\"4\"" . selected(4, $selectedVisible) . ">" . __("Not Visible", rb_agency_TEXTDOMAIN) . "</option>\n";
	echo "                <option value=\"0\"" . selected(0, $selectedVisible) . ">" . __("Inactive", rb_agency_TEXTDOMAIN) . "</option>\n";
	echo "                <option value=\"2\"" . selected(2, $selectedVisible) . ">" . __("Archived", rb_agency_TEXTDOMAIN) . "</option>\n";
	echo "              </select></span>\n";
	echo "              <span>" . __("Location", rb_agency_TEXTDOMAIN) . ": \n";
	echo "              <select name=\"ProfileLocationCity\">\n";
	echo "                <option value=\"\">" . __("Any Location", rb_agency_TEXTDOMAIN) . "</option>";

	$query = "SELECT DISTINCT ProfileLocationCity, ProfileLocationState FROM " . table_agency_profile . " ORDER BY ProfileLocationState, ProfileLocationCity ASC";
	$results = mysql_query($query);
	$count = mysql_num_rows($results);
	while ($data = mysql_fetch_array($results)) {
		if (isset($data['ProfileLocationCity']) && !empty($data['ProfileLocationCity'])) {
			echo "<option value=\"" . $data['ProfileLocationCity'] . "\" " . selected($selectedCity, $data["ProfileLocationCity"]) . "\">" . $data['ProfileLocationCity'] . ", " . strtoupper($dataLocation["ProfileLocationState"]) . "</option>\n";
		}
	}
	echo "              </select></span>\n";
	echo "              <span>" . __("Gender", rb_agency_TEXTDOMAIN) . ":\n";
	echo "              <select name=\"ProfileGender\">\n";
	echo "                  <option value=\"\">" . __("Any Gender", rb_agency_TEXTDOMAIN) . "</option>\n";
	$query2 = "SELECT GenderID, GenderTitle FROM " . table_agency_data_gender . " ORDER BY GenderID";
	$results2 = mysql_query($query2);
	while ($dataGender = mysql_fetch_array($results2)) {
		echo "<option value=\"" . $dataGender["GenderID"] . "\"" . selected($_GET["ProfileGender"], $dataGender["GenderID"], false) . ">" . $dataGender["GenderTitle"] . "</option>";
	}
	echo "              </select></span>\n";
	echo "              <span class=\"submit\"><input type=\"submit\" value=\"" . __("Filter", rb_agency_TEXTDOMAIN) . "\" class=\"button-primary\" /></span>\n";
	echo "          </p></form>\n";
	echo "          <form style=\"display: inline; float: left; margin: 17px 5px 0px 0px;\" method=\"GET\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	echo "              <input type=\"hidden\" name=\"page_index\" id=\"page_index\" value=\"" . $_GET['page_index'] . "\" />  \n";
	echo "              <input type=\"hidden\" name=\"page\" id=\"page\" value=\"" . $_GET['page'] . "\" />\n";
	echo "              <input type=\"submit\" value=\"" . __("Clear Filters", rb_agency_TEXTDOMAIN) . "\" class=\"button-secondary\" />\n";
	echo "          </form>\n";
	echo "          <a  style=\"display: inline; float: left; margin: 17px 5px 0px 0px;\" href=\"" . admin_url("admin.php?page=rb_agency_search") . "\" class=\"button-secondary\">" . __("Advanced Search", rb_agency_TEXTDOMAIN) . "</a>\n";


?>

					</div>
				</div>

			</div>
		</div>
		</div>
<?php
	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if ($items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";

	echo "<form method=\"post\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
	echo " <thead>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column-ProfileID\" id=\"ProfileID\" scope=\"col\" style=\"width:50px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileID&dir=" . $sortDirection) . "\">ID</a></th>\n";
	echo "        <th class=\"column-ProfileContactNameFirst\" id=\"ProfileContactNameFirst\" scope=\"col\" style=\"width:150px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileContactNameFirst&dir=" . $sortDirection) . "\">First Name</a></th>\n";
	echo "        <th class=\"column-ProfileContactNameLast\" id=\"ProfileContactNameLast\" scope=\"col\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileContactNameLast&dir=" . $sortDirection) . "\">Last Name</a></th>\n";
	echo "        <th class=\"column-ProfileGender\" id=\"ProfileGender\" scope=\"col\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileGender&dir=" . $sortDirection) . "\">Gender</a></th>\n";
	echo "        <th class=\"column-ProfilesProfileDate\" id=\"ProfilesProfileDate\" scope=\"col\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileDateBirth&dir=" . $sortDirection) . "\">Age</a></th>\n";
	echo "        <th class=\"column-ProfileLocationCity\" id=\"ProfileLocationCity\" scope=\"col\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileLocationCity&dir=" . $sortDirection) . "\">City</a></th>\n";
	echo "        <th class=\"column-ProfileLocationState\" id=\"ProfileLocationState\" scope=\"col\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileLocationState&dir=" . $sortDirection) . "\">State</a></th>\n";
	echo "        <th class=\"column-ProfileDetails\" id=\"ProfileDetails\" scope=\"col\">Category</th>\n";
	echo "        <th class=\"column-ProfileDetails\" id=\"ProfileDetails\" scope=\"col\">Images</th>\n";
	echo "        <th class=\"column-ProfileStatHits\" id=\"ProfileStatHits\" scope=\"col\">Views</th>\n";
	echo "        <th class=\"column-ProfileDateViewLast\" id=\"ProfileDateViewLast\" scope=\"col\" style=\"width:125px;\">Last Viewed Date</th>\n";
	echo "    </tr>\n";
	echo " </thead>\n";
	echo " <tfoot>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column\" scope=\"col\">ID</th>\n";
	echo "        <th class=\"column\" scope=\"col\">First Name</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Last Name</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Gender</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Age</th>\n";
	echo "        <th class=\"column\" scope=\"col\">City</th>\n";
	echo "        <th class=\"column\" scope=\"col\">State</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Category</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Images</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Views</th>\n";
	echo "        <th class=\"column\" scope=\"col\">Last Viewed</th>\n";
	echo "    </tr>\n";
	echo " </tfoot>\n";
	echo " <tbody>\n";

	$query = "SELECT * FROM " . table_agency_profile . " profile LEFT JOIN " . table_agency_data_type . " profiletype ON profile.ProfileType = profiletype.DataTypeID " . $filter . " ORDER BY $sort $dir $limit";
	$results2 = mysql_query($query);
	$count = mysql_num_rows($results2);
	while ($data = mysql_fetch_array($results2)) {

		$ProfileID = $data['ProfileID'];
		$ProfileGallery = stripslashes($data['ProfileGallery']);
		$ProfileContactNameFirst = stripslashes($data['ProfileContactNameFirst']);
		$ProfileContactNameLast = stripslashes($data['ProfileContactNameLast']);
		$ProfileLocationCity = RBAgency_Common::format_propercase(stripslashes($data['ProfileLocationCity']));
		$ProfileLocationState = stripslashes(get_state_by_id($data['ProfileLocationState']));
		$ProfileGender = stripslashes($data['ProfileGender']);
		$ProfileDateBirth = stripslashes($data['ProfileDateBirth']);
		$ProfileStatHits = stripslashes($data['ProfileStatHits']);
		$ProfileDateViewLast = stripslashes($data['ProfileDateViewLast']);
		if ($data['ProfileIsActive'] == 0) {
			// Inactive
			$rowColor = " style=\"background: #FFEBE8\"";
		} elseif ($data['ProfileIsActive'] == 1) {
			// Active
			$rowColor = "";
		} elseif ($data['ProfileIsActive'] == 2) {
			// Archived
			$rowColor = " style=\"background: #dadada\"";
		} elseif ($data['ProfileIsActive'] == 3) {
			// Pending Approval
			$rowColor = " style=\"background: #DD4B39\"";
		}

		/*
		 * Get Data Type Title
		 */
		if(strpos($data['ProfileType'], ",") != -1){
			$title = explode(",",$data['ProfileType']);
			$new_title = "";
			foreach($title as $t){
				$id = (int)$t;
				$get_title = "SELECT DataTypeTitle FROM " . table_agency_data_type .  
							 " WHERE DataTypeID = " . $id;   
				$resource = mysql_query($get_title);             
				$get = mysql_fetch_assoc($resource);
				if (mysql_num_rows($resource) > 0 ){
					$new_title .= "," . $get['DataTypeTitle']; 
				}
			}
			$new_title = substr($new_title,1);
		} else {
				$new_title = "";
				$id = (int)$data['ProfileType'];
				$get_title = "SELECT DataTypeTitle FROM " . table_agency_data_type .  
							 " WHERE DataTypeID = " . $id;   
				$resource = mysql_query($get_title);             
				$get = mysql_fetch_assoc($resource);
				if (mysql_num_rows($resource) > 0 ){
					$new_title = $get['DataTypeTitle']; 
				}
		}

		$DataTypeTitle = $new_title;

		$resultImageCount = mysql_query("SELECT * FROM " . table_agency_profile_media . " WHERE ProfileID='" . $ProfileID . "' AND ProfileMediaType = 'Image'");
		$profileImageCount = mysql_num_rows($resultImageCount);


		$resultProfileGender = mysql_query("SELECT * FROM " . table_agency_data_gender . " WHERE GenderID = '" . $ProfileGender . "' ");
		$fetchProfileGender = mysql_fetch_assoc($resultProfileGender);
		$ProfileGender = $fetchProfileGender["GenderTitle"];

		echo "    <tr" . $rowColor . ">\n";
		echo "        <th class=\"check-column\" scope=\"row\">\n";
		echo "          <input type=\"checkbox\" value=\"" . $ProfileID . "\" class=\"administrator\" id=\"" . $ProfileID . "\" name=\"" . $ProfileID . "\"/>\n";
		echo "        </th>\n";
		echo "        <td class=\"ProfileID column-ProfileID\">" . $ProfileID . "</td>\n";
		echo "        <td class=\"ProfileContactNameFirst column-ProfileContactNameFirst\">\n";
		echo "          " . $ProfileContactNameFirst . "\n";
		echo "          <div class=\"row-actions\">\n";
		echo "            <span class=\"edit\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&amp;action=editRecord&amp;ProfileID=" . $ProfileID . "\" title=\"" . __("Edit this Record", rb_agency_TEXTDOMAIN) . "\">" . __("Edit", rb_agency_TEXTDOMAIN) . "</a> | </span>\n";
		echo "            <span class=\"edit\"><a href=\"" . rb_agency_PROFILEDIR . $rb_agency_UPLOADDIR . $ProfileGallery . "/\" title=\"" . __("View", rb_agency_TEXTDOMAIN) . "\" target=\"_blank\">" . __("View", rb_agency_TEXTDOMAIN) . "</a> | </span>\n";
		echo "            <span class=\"delete\"><a class=\"submitdelete\" href=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&amp;action=deleteRecord&amp;ProfileID=" . $ProfileID . "\"  onclick=\"if ( confirm('" . __("You are about to delete the profile for ", rb_agency_TEXTDOMAIN) . " " . $ProfileContactNameFirst . " " . $ProfileContactNameLast . "'" . __("Cancel", rb_agency_TEXTDOMAIN) . "\' " . __("to stop", rb_agency_TEXTDOMAIN) . ", \'" . __("OK", rb_agency_TEXTDOMAIN) . "\' " . __("to delete", rb_agency_TEXTDOMAIN) . ".') ) { return true;}return false;\" title=\"" . __("Delete this Record", rb_agency_TEXTDOMAIN) . "\">" . __("Delete", rb_agency_TEXTDOMAIN) . "</a> </span>\n";
		echo "          </div>\n";
		echo "        </td>\n";
		echo "        <td class=\"ProfileContactNameLast column-ProfileContactNameLast\">" . $ProfileContactNameLast . "</td>\n";
		echo "        <td class=\"ProfileGender column-ProfileGender\">" . $ProfileGender . "</td>\n";
		echo "        <td class=\"ProfilesProfileDate column-ProfilesProfileDate\">" . rb_agency_get_age($ProfileDateBirth) . "</td>\n";
		echo "        <td class=\"ProfileLocationCity column-ProfileLocationCity\">" . $ProfileLocationCity . "</td>\n";
		echo "        <td class=\"ProfileLocationCity column-ProfileLocationState\">" . $ProfileLocationState . "</td>\n";
		echo "        <td class=\"ProfileDetails column-ProfileDetails\">" . $DataTypeTitle . "</td>\n";
		echo "        <td class=\"ProfileDetails column-ProfileDetails\">" . $profileImageCount . "</td>\n";
		echo "        <td class=\"ProfileStatHits column-ProfileStatHits\">" . $ProfileStatHits . "</td>\n";
		echo "        <td class=\"ProfileDateViewLast column-ProfileDateViewLast\">\n";
		echo "           " . rb_agency_makeago(rb_agency_convertdatetime($ProfileDateViewLast), $rb_agency_option_locationtimezone);
		echo "        </td>\n";
		echo "    </tr>\n";
	}
	mysql_free_result($results2);
	if ($count < 1) {
		if (isset($filter)) {
			echo "    <tr>\n";
			echo "        <th class=\"check-column\" scope=\"row\"></th>\n";
			echo "        <td class=\"name column-name\" colspan=\"5\">\n";
			echo "           <p>No profiles found with this criteria.</p>\n";
			echo "        </td>\n";
			echo "    </tr>\n";
		} else {
			echo "    <tr>\n";
			echo "        <th class=\"check-column\" scope=\"row\"></th>\n";
			echo "        <td class=\"name column-name\" colspan=\"5\">\n";
			echo "            <p>There aren't any profiles loaded yet!</p>\n";
			echo "        </td>\n";
			echo "    </tr>\n";
		}
	}
	echo " </tbody>\n";
	echo "</table>\n";

	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if ($items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";

	// Show Actions
	echo "<p class=\"submit\">\n";
	echo "  <input type=\"hidden\" value=\"deleteRecord\" name=\"action\" />\n";
	echo "  <input type=\"submit\" value=\"" . __('Delete Profiles') . "\" class=\"button-primary\" name=\"submit\" />   \n";
	echo "</p>\n";
	echo "</form>\n";
}

echo "</div>\n";
echo "</div>\n";
?>