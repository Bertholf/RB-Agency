<?php
/*
Profile View with Scrolling Thumbnails and Primary Image
*/

	echo "<div id=\"profile\">\n";
	echo " <div id=\"profile-layout-zero\">\n";

	echo "	<div id=\"photo\">\n";
	echo "	  <div class=\"inner\">\n";
			// images
		
			$queryImg = "SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Image\" ORDER BY $orderBy";
			$resultsImg = mysql_query($queryImg);
			$countImg = mysql_num_rows($resultsImg);
			while ($dataImg = mysql_fetch_array($resultsImg)) {
			  if ($countImg > 1) { 
				echo "<div class=\"multiple\"><a href=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."\" rel=\"lightbox-profile". $ProfileID ."\" title=\"". $ProfileContactDisplay ."\"><img src=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."\" alt=\"". $ProfileContactDisplay ."\" /></a></div>\n";
			  } else {
				echo "<div class=\"single\"><a href=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."\" rel=\"lightbox-profile". $ProfileID ."\" title=\"". $ProfileContactDisplay ."\"><img src=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."\" alt=\"". $ProfileContactDisplay ."\" /></a></div>\n";
			  }
			}

	echo "	  </div>\n";
	echo "	</div>\n";
	echo "	<div id=\"info\">\n";
	echo "	  <h2>". $ProfileContactDisplay ."</h2>\n";
	echo "	  <div class=\"action\">\n";
	echo "		<div class=\"links\">\n";
	echo "			<h3>". $AgencyName ." ". $ProfileClassification ."</h3>\n";
	echo "			<ul>\n";

				// Resume
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Resume\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
				echo "<li class=\"item resume\"><a href=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataMedia['ProfileMediaURL'] ."\">Print Resume</a></li>\n";
				  }
				}
			
				// Comp Card
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Comp Card\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
				echo "<li class=\"item compcard\"><a href=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataMedia['ProfileMediaURL'] ."\">Download Comp Card</a></li>\n";
				  }
				}
				// Headshots
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Headshot\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
				echo "<li class=\"item headshot\"><a href=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataMedia['ProfileMediaURL'] ."\">Download Headshot</a></li>\n";
				  }
				}
				
				//Voice Demo
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Voice Demo\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
				echo "<li class=\"item voice\"><a href=\"". rb_agency_UPLOADDIR . $ProfileGallery ."/". $dataMedia['ProfileMediaURL'] ."\">Listen to Voice Demo</a></li>\n";
				  }
				}

				//Video Slate
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Video Slate\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
					 $profileVideoEmbed = $dataMedia['ProfileMediaURL'];
				echo "		<li class=\"item video slate\"><a href=\"http://www.youtube.com/watch?v=". $dataMedia['ProfileMediaURL'] ."\" target=\"_blank\">Watch Video Slate</a></li>\n";
				  }
				}

				//Video Monologue
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Video Monologue\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
				echo "		<li class=\"item video monologue\"><a href=\"http://www.youtube.com/watch?v=". $dataMedia['ProfileMediaURL'] ."\" target=\"_blank\">Watch Video Monologue</a></li>\n";
				  }
				}

				//Demo Reel
				$resultsMedia = mysql_query("SELECT * FROM ". table_agency_profile_media ." media WHERE ProfileID =  \"". $ProfileID ."\" AND ProfileMediaType = \"Demo Reel\"");
				$countMedia = mysql_num_rows($resultsMedia);
				if ($countMedia > 0) {
				  while ($dataMedia = mysql_fetch_array($resultsMedia)) {
				echo "		<li class=\"item video demoreel\"><a href=\"http://www.youtube.com/watch?v=". $dataMedia['ProfileMediaURL'] ."\" target=\"_blank\">Watch Demo Reel</a></li>\n";
				  }
				}

				//Contact Profile
				if (isset($rb_agency_option_agency_urlcontact)) {
				echo "		<li class=\"item contact\"><a href=\"". $rb_agency_option_agency_urlcontact ."\">". __("Contact", rb_agency_TEXTDOMAIN). " ". $ProfileClassification ."</a></li>\n";
				}

				//<li class="item"><a href=""><img src="/wp-content/uploads/2010/07/talk.jpg" /></a><a href="">View Video Slate</a></li>
			  //li class="item"><a href=""><img src="/wp-content/uploads/2010/07/talk.jpg" /></a><a href="">View Monolog</a></li>
			  //<li class="item"><a href=""><img src="/wp-content/uploads/2010/07/download.jpg" /></a><a href="">Download Reel</a></li>
				//echo "<li class=\"cart\"><a href=\"\"><img src=\"". get_bloginfo("wpurl") ."/wp-content/uploads/2010/07/cart.jpg\" /></a><a href=\"\">Add to Casting Cart</a></li>\n";
				
				// URL
				//if($ProfileIsModel){ $returnURL = get_bloginfo("url") ."/models/"; } elseif ($ProfileIsTalent){ $returnURL = get_bloginfo("url") ."/talent/"; }
				//echo "<li class=\"return\"><a href=\"\"><img src=\"". get_bloginfo("url") ."/wp-content/uploads/2010/07/return.jpg\" /></a><a href=\"". $returnURL ."\">Return to ". $ProfileClassification ."</a></li>\n";
				
				// Is Logged?
				if (is_user_logged_in()) { 
				echo "		<li class=\"return dashboard\"><a href=\"". get_bloginfo("url") ."/dashboard/\">". __("Access Dashboard", rb_agency_TEXTDOMAIN). "</a></li>\n";
				}

	echo "			</ul>\n";
	echo "		</div>\n";  // Close Links

			if (isset($profileVideoEmbed)) {
	echo "		<div id=\"movie\"><object width=\"250\" height=\"190\"><param name=\"movie\" value=\"http://www.youtube.com/v/". $profileVideoEmbed ."?fs=1&amp;hl=en_US&rel=0&showsearch=0\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube.com/v/". $profileVideoEmbed ."?fs=1&amp;hl=en_US\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"250\" height=\"190\"></embed></object></div>\n";
			}
			
	echo "	  </div>\n"; // Close Action
	
	echo "	  <div class=\"stats\">\n";

		if (!empty($ProfileGender)) {
			echo "<div><strong>". __("Gender", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". __($ProfileGender, rb_agency_TEXTDOMAIN). "</div>\n";
		}
		if (!empty($ProfileStatEthnicity)) {
			echo "<div><strong>". __("Ethnicity", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatEthnicity ."</div>\n";
		}
		if (!empty($ProfileStatSkinColor)) {
			echo "<div><strong>". __("Skin Tone", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatSkinColor ."</div>\n";
		}
		if (!empty($ProfileStatHairColor)) {
			echo "<div><strong>". __("Hair Color", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatHairColor ."</div>\n";
		}
		if (!empty($ProfileStatEyeColor)) {
			echo "<div><strong>". __("Eye Color", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatEyeColor ."</div>\n";
		}
		if (!empty($ProfileStatHeight)) {
			if ($rb_agency_option_unittype == 0) { // Metric
				echo "<div><strong>". __("Height", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatHeight ." ". __("cm", rb_agency_TEXTDOMAIN). "" ."</div>\n";
			} else { // Imperial
				$heightraw = $ProfileStatHeight;
				$heightfeet = floor($heightraw/12);
				$heightinch = $heightraw - floor($heightfeet*12);
				echo "<div><strong>". __("Height", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $heightfeet ." ". __("ft", rb_agency_TEXTDOMAIN). " ". $heightinch ." ". __("in", rb_agency_TEXTDOMAIN). "" ."</div>\n";
			}
		}
		if (!empty($ProfileStatWeight)) {
			if ($rb_agency_option_unittype == 0) { // Metric
				echo "<div><strong>". __("Weight", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatWeight ." ". __("kg", rb_agency_TEXTDOMAIN). "</div>\n";
			} else { // Imperial
				echo "<div><strong>". __("Weight", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatWeight ." ". __("lb", rb_agency_TEXTDOMAIN). "</div>\n";
			}
		}
		if (!empty($ProfileStatBust)) {
			if($ProfileGender == "Male"){ $ProfileStatBustTitle = __("Chest", rb_agency_TEXTDOMAIN); } elseif ($ProfileGender == "Female"){ $ProfileStatBustTitle = __("Bust", rb_agency_TEXTDOMAIN); } else { $ProfileStatBustTitle = __("Chest/Bust", rb_agency_TEXTDOMAIN); }
			echo "<div><strong>". $ProfileStatBustTitle ."</strong> ". $ProfileStatBust ."</div>\n";
		}
		if (!empty($ProfileStatWaist)) {
			echo "<div><strong>". __("Waist", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatWaist ."</div>\n";
		}
		if (!empty($ProfileStatHip)) {
			if($ProfileGender == "Male"){ $ProfileStatHipTitle = __("Inseam", rb_agency_TEXTDOMAIN); } elseif ($ProfileGender == "Female"){ $ProfileStatHipTitle = __("Hips", rb_agency_TEXTDOMAIN); } else { $ProfileStatHipTitle = __("Hips/Inseam", rb_agency_TEXTDOMAIN); }
			echo "<div><strong>". $ProfileStatHipTitle ."<span class=\"divider\">:</span></strong> ". $ProfileStatHip ."</div>\n";
		}
		if (!empty($ProfileStatDress) || ($ProfileStatDress == 0)) {
			if($ProfileGender == "Male"){ $ProfileStatDressTitle = __("Suit Size", rb_agency_TEXTDOMAIN); } elseif ($ProfileGender == "Female"){ $ProfileStatDressTitle = __("Dress Size", rb_agency_TEXTDOMAIN); } else { $ProfileStatDressTitle = __("Suit/Dress Size", rb_agency_TEXTDOMAIN); }
			echo "<div><strong>". $ProfileStatDressTitle ."<span class=\"divider\">:</span></strong> ". $ProfileStatDress ."</div>\n";
		}
		if (!empty($ProfileStatShoe)) {
			echo "<div><strong>". __("Shoe Size", rb_agency_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileStatShoe ."</div>\n";
		}


		$resultsCustom = $wpdb->get_results("SELECT c.ProfileCustomTitle, c.ProfileCustomOrder, cx.ProfileCustomValue FROM ". table_agency_customfield_mux ." cx LEFT JOIN ". table_agency_customfields ." c ON c.ProfileCustomID = cx.ProfileCustomID WHERE c.ProfileCustomView = 0 AND cx.ProfileID = ". $ProfileID ." ORDER BY c.ProfileCustomOrder DESC");
		foreach  ($resultsCustom as $resultCustom) {
			echo "<div><strong>". $resultCustom->ProfileCustomTitle ."<span class=\"divider\">:</span></strong> ". $resultCustom->ProfileCustomValue ."</div>\n";
		}

	echo "	  </div>\n"; // Close Stats
        
	echo "	  <div class=\"experience\">\n";
	echo			$ProfileExperience;
	echo "	  </div>\n"; // Close Experience
	
	
	echo "	  </div>\n";  // Close Info
	
	echo "	  <div style=\"clear: both;\"></div>\n"; // Clear All
	echo "  </div>\n";  // Close Profile Zero
	echo "<div style=\"clear: both;\"></div>\n"; // Clear All
	echo "</div>\n";  // Close Profile
?>