<?php
	/*
	Plugin Name: Message Creative Slider
	Plugin URI: http://messagecreative.com
	Description: A slider manager and displayer using slides plugin at http://slidesjs.com/
	Author: Message:Creative Team
	Version: 1.1
	*/
	
	// Make sure we don't expose any info if called directly
	if ( ! function_exists( 'add_action' ) ) {
		_e( "Hi there! I'm just a plugin, not much I can do when called directly." );
		exit;
	}
	
	//load Admin scripts and styles
	function mcSlider_scripts() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('jquery');
    }
	function mcSlider_styles(){
		wp_enqueue_style('thickbox');
	}	
	if (isset($_GET['page']) && $_GET['page'] == 'mc_slider_manager'){
		add_action('admin_print_scripts', 'mcSlider_scripts');
		add_action('admin_print_styles', 'mcSlider_styles');
	}
	
	//add plugin to menu
	add_action('admin_menu','mcSlider_admin_actions');
	function mcSlider_admin_actions(){
		add_submenu_page('upload.php', 'MessageCreative Slider Manager', 'Slider Manager', 'manage_options', 'mc_slider_manager', 'mcSlider_admin');
	}
	
	//display menu page
	function mcSlider_admin(){
		global $wpdb;
		//check if page is loading after form submit or just normally
        if($_POST['mcSlider_hidden'] == 'Y'){  
	        $count = $_POST["count"]; update_option('mcSlider_count', $count);
	        $image = $_POST["mcSlider_image"]; update_option('mcSlider_image', serialize($image));
	        $imageW = $_POST["mcSlider_imageWidth"]; update_option('mcSlider_imageWidth', $imageW);
	        $imageH = $_POST["mcSlider_imageHeight"]; update_option('mcSlider_imageHeight', $imageH);
	    } else {  
	        $count = get_option("mcSlider_count");
	        $imageW = get_option('mcSlider_imageWidth');
	        $imageH = get_option('mcSlider_imageHeight');
	        $image = unserialize(get_option("mcSlider_image"));
	        
	    } ?>
			<script>
				jQuery(document).ready(function(){
					//wrap the slide sections of the admin menu in a div.slider
					jQuery('h4.sliderHeader').css('margin','0').each(function(){
						jQuery(this).nextUntil('h4.sliderHeader, #submit').wrapAll('<div class="slider" />')
					});					
					jQuery('div.slider').not(':first').slideUp();
					
					//make the div.sliders into a clickable accordian
					jQuery('h4.sliderHeader').css('cursor', 'pointer').click(function(){
						jQuery(this).next('div.slider').slideToggle().siblings('div.slider:visible').slideUp();
						return false;
					});
					
					//intercept wordpress 'image uploader' and return the clicked image into our url field
					var imageField;
					jQuery('.upload_image_button').click(function() {
					 formfield = jQuery(this).prev('input').attr('name');
					 tb_show('Add Image to Slider', 'media-upload.php?type=image&amp;TB_iframe=true');
					 imageField = jQuery(this).prev('input');
					 return false;
					});
					window.send_to_editor = function(html) {
					 imgurl = jQuery('img',html).attr('src');
					 jQuery(imageField).val(imgurl);
					 tb_remove();
					}
				});
			</script>
			<style>
				.sliderHeader:hover {
    				background-color: #E0E0E0;
				}
				.sliderHeader {
    				width: <?= $imageW - 10 ?>px;
    				background-color: #EEE;
    				padding: 10px 5px;
				}
			</style>
		
			<div class="wrap">
				<h2>Message:Creative Slider Manager</h2>
				<form name="mcSlider_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					<input type="hidden" name="mcSlider_hidden" value="Y"> 
					<p><label for="count">How many slides would you like:</label> <input style="width:40px;" type="text" name="count" value="<?php echo $count ?>"> <em>Press Return after</em></p>
					<p>
						<label>What size would you like your images:</label> 
						<input style="width:40px;" type="text" name="mcSlider_imageWidth" value="<?php echo $imageW ?>">px
						&nbsp;x&nbsp; 
						<input style="width:40px;" type="text" name="mcSlider_imageHeight" value="<?php echo $imageH ?>">px 
						&nbsp;<em>Press Return after</em><br />
						<em>Don't forget to crop your images to what ever size you choose here!</em> 
					</p>
					<?php for ($i=1; $i <= $count; $i++ ) { ?>
						<h4 class="sliderHeader">Slider Image <?= $i; ?></h4>
						<p><input class="upload_image" type="text" name="mcSlider_image[<?= $i; ?>][image]" value="<?php echo $image[$i]['image']; ?>" style="width:<?= $imageW - 100 ?>px;">
						<input class="upload_image_button" type="button" value="Upload Image"><br />Enter a URL or upload an image
						<br /><?php print_r($id); ?>
						</p>
						<textarea name="mcSlider_image[<?= $i; ?>][text]" rows="11" cols="30" style="float:left;width:190px;"><?php echo $image[$i]['text']; ?></textarea>
						<p style="margin-left:200px;background:#aeaeae;width:<?= $imageW ?>px;height:<?= $imageH ?>px">
							<img src="<?= plugins_url('timthumb.php', __FILE__); ?>?src=<?php echo $image[$i]['image']; ?>&amp;w=<?= $imageW ?>&amp;h=<?= $imageH ?>" width="<?= $imageW ?>" height="<?= $imageH ?>">
						</p>
					<?php } ?>
				
					<p id="submit" style="width:<?= $imageW ?>px;text-align:right;"><input type="submit" name="Submit" value="Update Options" style="margin-top:10px;" /></p>
				</form>
			</div><!-- end wrap -->
	<?php } //end function mcSlider_admin()
	
	//load Page scripts and styles on pages where the  display function is called
	add_action('wp_print_scripts', 'mcSlider_script_load');
	function mcSlider_script_load(){
		if (!is_admin()){
			wp_enqueue_script('slides', plugins_url('slides.min.jquery.js', __FILE__), array('jquery'), '', true);
			wp_enqueue_script('mcSliderjs', plugins_url('mcSlider.js', __FILE__), array('jquery'), '', true);	
		}
	}
	
	//function to print out slides where you want them in your theme
	function mcSlider(){
		$slidesArray = unserialize(get_option('mcSlider_image'));
		$width = get_option('mcSlider_imageWidth');
        $height = get_option('mcSlider_imageHeight');
        $slideCount = 0;
        foreach($slidesArray as $slide){
        	if($slide['image']){$slideCount++;}
        }
        ?>
        <style>
        	#slides {
        		overflow: hidden;
        	}
        	.slides_container {
				width: <?= $width ?>px;
				height: <?= $height ?>px;
			}
			.slides_container div {
				width: <?= $width ?>px;
				height: <?= $height ?>px;
				display: block;
			}
			.slides_container div span {
				position: absolute;
				opacity: 0;
				top: 0;
				left: 0;
				background: rgba(0, 0, 0, 0.5);
				color: #fff;
				height: 300px;
				width: 215px;
				padding: 10px;
				border-top-left-radius: 5px;
			}
		   .slides_container div span {
		       background:#000000;
		       filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#00000050,endColorstr=#00000050);
		       zoom: 1;
		    } 
			ul.pagination {
				margin: 5px auto 0;
				padding: 0;
				text-align: center;
				z-index: 50;
				width: <?= $slideCount * 15 ?>px;
			}
			ul.pagination li {
				float: left;
				margin: 0 3px 0 0;
				list-style: none;
			}
			.pagination li a {
				display:block;
				width:12px;
				height:0;
				padding-top:12px;
				background-image:url(<?= plugins_url('pagination.png', __FILE__) ?>);
				background-position:0 0;
				float:left;
				overflow:hidden;
			}
			.pagination li.current a {
				background-position:0 -12px;
			}
        </style>
        <div id="slides"><!-- a bit of unnecessary markup for use by the slides plugin unfortunately -->
	        <div class="slides_container">
	        	<?php foreach($slidesArray as $slide){ 
	        		if ($slide['image']){ $slideCount++;?>
	        			<div>
				        	<img src="<?= plugins_url('timthumb.php', __FILE__); ?>?src=<?= $slide['image']; ?>&amp;w=<?= $width; ?>&amp;h=<?= $height; ?>">
				        	<span class="caption"><?= $slide['text']; ?></span>
				        </div>   
				    <?php }//end if
				 }//end foreach ?>
		    </div>
		</div>
	    
	    <?php
	}//end function mcSlider()
	