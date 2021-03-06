<?php
/*
Title:  Scrolling
Author: RB Plugin
Text:   Profile View with Scrolling Thumbnails and Primary Image
*/

/*
 * Insert Javascript into Head
 */
	wp_register_style( 'rblayout-style', RBAGENCY_PLUGIN_URL .'view/layout/04/css/style.css' );
	wp_enqueue_style( 'rblayout-style' );


/*
 * Layout 
 */
# rb_agency_option_galleryorder
$rb_agency_options_arr = get_option('rb_agency_options');
$order = $rb_agency_options_arr['rb_agency_option_galleryorder'];
$display_gender = isset($rb_agency_options_arr['rb_agency_option_viewdisplay_gender']) ? $rb_agency_options_arr['rb_agency_option_viewdisplay_gender']:false;

echo "	<div id=\"rbprofile\">\n";
echo " 		<div id=\"rblayout-four\" class=\"rblayout\">\n";

echo "			<div class=\"rbcol-12 rbcolumn\">";
echo "				<header class=\"entry-header\">";
echo "						<h1 class=\"entry-title\">". $ProfileContactDisplay ."</h1>\n";
echo "				</header>";

echo '
<style>
div.profiledescription{
    white-space: pre-wrap;       /* Since CSS 2.1 */
    white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
    white-space: -pre-wrap;      /* Opera 4-6 */
    white-space: -o-pre-wrap;    /* Opera 7 */
    word-wrap: break-word;       /* Internet Explorer 5.5+ */
	display:block;
}
</style>
';
echo '<div class="profiledescription">'.$ProfileDescription.'</div>';

echo "			</div>";

echo "			<div class=\"rbcol-4 rbcolumn\">\n";
echo "				<div id=\"profile-info\">\n";

echo "					<h3>Statistics</h3>\n";
echo "						<div class=\"stats\">\n";
echo "							<ul>\n";
								if (!empty($ProfileGender) and $display_gender == true) {
									$fetchGenderData = $wpdb->get_row($wpdb->prepare("SELECT GenderID, GenderTitle FROM ".table_agency_data_gender." WHERE GenderID='%s' ",$ProfileGender),ARRAY_A,0 	);
									echo "<li class=\"rb_gender\" id=\"rb_gender\"><strong>". __("Gender", RBAGENCY_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". __($fetchGenderData["GenderTitle"], RBAGENCY_TEXTDOMAIN). "</li>\n";
								}

								// Insert Custom Fields
								rb_agency_getProfileCustomFields($ProfileID, $ProfileGender);
								get_social_media_links($ProfileID);
								if (!empty($ProfileContactPhoneWork)) {
									echo "<li class=\"rb_contact\" id=\"rb_phone_work\"><strong>". __("Phone", RBAGENCY_TEXTDOMAIN). "<span class=\"divider\">:</span></strong> ". $ProfileContactPhoneWork . "</li>\n";
								}

echo "							</ul>\n"; // Close ul
echo "						<div id=\"book-now\"><a href=\"contact/\" title=\"Book Now!\" class=\"rb_button\">Book Now!</a></div>\n"; // Close Stats
echo "						</div>\n"; // Close Stats
echo "				</div><!-- #profile-info -->\n";
echo "			</div><!-- .rbcol-4 -->\n";

echo "			<div class=\"rbcol-8 rbcolumn\">\n";
echo "					<div id=\"photos\">\n";

						// images
						$private_profile_photo = get_user_meta($ProfileUserLinked,'private_profile_photo',true);
						$private_profile_photo_arr = explode(',',$private_profile_photo);
						$queryImg = rb_agency_option_galleryorder_query($order ,$ProfileID,"Image");
						$resultsImg=  $wpdb->get_results($queryImg,ARRAY_A);
						$countImg  = $wpdb->num_rows;
						foreach($resultsImg as $dataImg ){
							if ($countImg > 1) {
								if(!in_array($dataImg['ProfileMediaID'],$private_profile_photo_arr)){
									echo "<div class=\"photo\"><a href=\"". RBAGENCY_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."\" ". $reltype ." ". $reltarget ."><img src=\"". get_bloginfo("url")."/wp-content/plugins/rb-agency/ext/timthumb.php?src=".RBAGENCY_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."&a=t&w=200&h=250\"  /></a></div>\n";
								}
								
							} else {

								if(!in_array($dataImg['ProfileMediaID'],$private_profile_photo_arr)){
									echo "<div class=\"photo\"><a href=\"". RBAGENCY_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."\" ". $reltype ." ". $reltarget ."><img src=\"". get_bloginfo("url")."/wp-content/plugins/rb-agency/ext/timthumb.php?src=".RBAGENCY_UPLOADDIR . $ProfileGallery ."/". $dataImg['ProfileMediaURL'] ."&a=t&w=200&h=250\" /></a></div>\n";
								}
								
							}
						}
echo "					</div><!-- #photos -->\n";
echo "			</div><!-- .rbcol-8 -->\n";

echo "  		<div class=\"rbclear\"></div>\n"; // Clear All

echo " 		</div>\n";// Close Profile Layout
echo "	</div>\n";// Close Profile
echo "	<div class=\"rbclear\"></div>\n"; // Clear All
?>