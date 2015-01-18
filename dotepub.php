<?php
/*
Plugin Name: dotEPUB
Plugin URI: http://dotepub.com/widget/wp/
Description: Allows your users to download your blog posts in e-book form for standard EPUB e-readers and Amazon Kindle.
Version: 1.1
Author: Xavier Badosa
Author URI: http://xavierbadosa.com
License: GPLv2
*/
/*  Copyright 2013 Xavier Badosa (email : info@dotepub.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function dotEPUB_plugin( $content , $shortcode=false, $shortcontent='' ) {
	if ( is_single() ){
		$onclick='';
		if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")!==false){
			preg_match("/MSIE\s([\d.]+)/", $_SERVER['HTTP_USER_AGENT'], $matches);
			/* Si versió MSIE inferior a 10 */
			if(intval($matches[1])<10){
				$onclick=" onclick=\"alert('".__( 'Sorry, this feature is not available on MS Internet Explorer prior to version 10. Please, use a different browser.', 'dotepub' )."');return false;\"";
			}
		}

		$title=the_title_attribute('echo=0');
		$author=get_the_author_meta('display_name');
		$content='<div id="dotEPUBcontent">'.$content.'</div>';

		$options=get_dotEPUB_plugin_options();
		$immersive=($options['links']=='0') ? '0' : '1';
		$img='<img alt="'.$options['text'].'" title="'.$options['text'].'" class="dotEPUBimg" src="'.plugins_url('i/but62x20.png',__FILE__).'" />';

		$style='dotEPUBremove';
		switch($options['icon']){
			case 'icon':
				$dotepub=$img;
			break;
			case 'icontext':
				$dotepub=$img.$options['text'];
			break;
			case 'button':
				$dotepub=$options['text'];
				if ( $shortcontent=='' ) {
					$style.=' dotEPUBbutton';
				}
			break;
			default:
				$dotepub=$options['text'];
		}
		
		$openlink='<a'.$onclick.' class="dotEPUB" data-dotepublang="'.$options['lang'].'" data-dotepublinks="'.$immersive.'" data-dotepubtitle="'.$title.'" data-dotepubauthor="'.$author.'" href="'.$_SERVER['REQUEST_URI'].'">';

		if ($shortcode) {
			$linkcontent=( $shortcontent=='' ) ? $dotepub : $shortcontent;
			return '<span class="'.$style.'">'.$openlink.$linkcontent.'</a></span>';
		}

		if( $options['align']!='def' ) {
			$style.=' dotEPUB'.$options['align'];
		}

		$out='<p class="'.$style.'">'.$openlink.$dotepub.'</a></p>';
		$script='<script type="text/javascript" src="//dotepub.com/p/widget.php?lang='.$options['lang'].'&amp;img=f"></script>';

		switch($options['pos']){
			case 'none': return $content.$script;
			case 'top': return $out.$content.$script;
			case 'topbot': return $out.$content.$out.$script;
			default: return $content.$out.$script;
		}
	}
	return $content;
}

function get_dotEPUB_plugin_options(){
	$lang=substr( WPLANG, 0, 2 );
	switch( $lang ){
		case 'es':
		case 'ca':
			$deflang=$lang;
		break;
		default: $deflang='en';
	}
	$options=get_option( 
		'dotEPUB_options',
		array(
			'text' => __( 'Download this article as an e-book', 'dotepub' ),
			'lang' => $deflang,
			'links' => '0',
			'icon' => 'text',
			'pos' => 'bot',
			'align' => 'def'
		)
	);
	if(!isset($options['links'])) $options['links']='1';
	if(!isset($options['text']) || $options['text']=='') $options['text']=__( 'Download this article as an e-book', 'dotepub' );

	$options['text']=htmlspecialchars($options['text']);

	if(!isset($options['lang']) || !in_array($options['lang'],array('en','es','ca'))) $options['lang']='en';
	if(!isset($options['pos']) || !in_array($options['pos'],array('top','bot','topbot','none'))) $options['pos']='bot';
	if(!isset($options['icon']) || !in_array($options['icon'],array('text','icon','icontext','button'))) $options['icon']='text';
	if(!isset($options['align']) || !in_array($options['align'],array('right','left','center','def'))) $options['align']='def';

	return $options;
}

function dotEPUB_plugin_menu() {
	add_options_page( 'dotEPUB Options', 'dotEPUB', 'manage_options', 'dotEPUB_options', 'dotEPUB_plugin_options' );
}
function dotEPUB_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'dotepub' ) );
	}
	echo '<div class="wrap">';
	screen_icon();
	echo '<h2>'.__( 'dotEPUB Settings', 'dotepub' ).'</h2>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'dotEPUB_options' );
	do_settings_sections( 'dotEPUB_options' );
	submit_button();
	echo '</form>';
	echo '</div>';
}

function dotEPUB_plugin_admin_init(){
	register_setting( 'dotEPUB_options', 'dotEPUB_options' /*, 'dotEPUB_options_validate'*/ );
	add_settings_section('blog_settings', __( 'Blog Settings','dotepub' ), 'dotEPUB_blog_settings_text', 'dotEPUB_options');
	add_settings_field('plugin_icon', __( 'Appearance', 'dotepub' ), 'dotEPUB_setting_icon', 'dotEPUB_options', 'blog_settings');
	add_settings_field('plugin_text', __( 'Text', 'dotepub' ), 'dotEPUB_setting_string', 'dotEPUB_options', 'blog_settings');
	add_settings_field('plugin_align', __( 'Alignment', 'dotepub' ), 'dotEPUB_setting_align', 'dotEPUB_options', 'blog_settings');
	add_settings_field('plugin_pos', __( 'Position', 'dotepub' ), 'dotEPUB_setting_pos', 'dotEPUB_options', 'blog_settings');
	add_settings_section('eb_settings', __( 'E-book Settings','dotepub' ), 'dotEPUB_eb_settings_text', 'dotEPUB_options');
	add_settings_field('plugin_imm', __( 'Mode', 'dotepub' ), 'dotEPUB_setting_imm', 'dotEPUB_options', 'eb_settings');
	add_settings_field('plugin_lang', __( 'Language to be used in the e-book', 'dotepub' ), 'dotEPUB_setting_lang', 'dotEPUB_options', 'eb_settings');
}

function dotEPUB_blog_settings_text() {
	echo '<p>'.__( 'These options affect how your blog posts will be modified. They allow you to set up how the e-book download link will look like.', 'dotepub' ).'</p>';
}
function dotEPUB_eb_settings_text() {
	echo '<p>'.__( 'These options affect the e-book conversion process.', 'dotepub' ).'</p>';
}

function dotEPUB_setting_align() {
	$check=array('right'=>'', 'left'=>'', 'center'=>'', 'def'=>'');
	$options=get_dotEPUB_plugin_options();
	$check[$options['align']]='checked="checked"';
	echo '<div><label><input type="radio" name="dotEPUB_options[align]" id="dotEPUB_left" value="left" '.$check['left'].' /> '.__( 'Left', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[align]" id="dotEPUB_center" value="center" '.$check['center'].' /> '.__( 'Center', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[align]" id="dotEPUB_right" value="right" '.$check['right'].' /> '.__( 'Right', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[align]" id="dotEPUB_def" value="def" '.$check['def'].' /> '.__( 'Same as post', 'dotepub' ).'</label></div>';
}

function dotEPUB_setting_pos() {
	$check=array('top'=>'', 'bot'=>'', 'topbot'=>'','none'=>'');
	$options=get_dotEPUB_plugin_options();
	$check[$options['pos']]='checked="checked"';
	echo '<div><label><input type="radio" name="dotEPUB_options[pos]" id="dotEPUB_pos1" value="top" '.$check['top'].' /> '.__( 'At the top of the post', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[pos]" id="dotEPUB_pos2" value="bot" '.$check['bot'].' /> '.__( 'At the bottom of the post', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[pos]" id="dotEPUB_pos3" value="topbot" '.$check['topbot'].' /> '.__( 'At the top and at the bottom', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[pos]" id="dotEPUB_pos4" value="none" '.$check['none'].' /> '.__( 'None (I will use the [dotepub] shortcode instead)', 'dotepub' ).'</label></div>';
}

function dotEPUB_setting_string() {
	$options=get_dotEPUB_plugin_options();
	echo "<input id='plugin_text' name='dotEPUB_options[text]' size='40' type='text' value='{$options['text']}' />";
}

function dotEPUB_setting_lang() {
	$check=array('en'=>'', 'es'=>'', 'ca'=>'');
	$options=get_dotEPUB_plugin_options();
	$check[$options['lang']]='selected="selected"';
	echo "<select name='dotEPUB_options[lang]' id='plugin_lang'><option value='en' {$check['en']}>".__( 'English', 'dotepub' )."</option><option value='es' {$check['es']}>".__( 'Spanish', 'dotepub' )."</option><option value='ca' {$check['ca']}>".__( 'Catalan', 'dotepub' )."</option></select>";
}

function dotEPUB_setting_imm() {
	$options=get_dotEPUB_plugin_options();
	$check=($options['links']) ? '' : 'checked="checked"';
	echo '<label><input type="checkbox" name="dotEPUB_options[links]" value="0" id="dotEPUB_imm" '.$check.' /> '.__( 'Immersive', 'dotepub' ).'</label>';
	echo '<p>'.__( 'In the immersive mode, links will be removed, you will not be offered the possibility of keeping images and there will be no indication where removed images and videos were. This is the recommended mode.', 'dotepub' ).'</p>';
}

function dotEPUB_setting_icon() {
	$check=array('text'=>'', 'icon'=>'', 'icontext'=>'', 'button'=>'');
	$options=get_dotEPUB_plugin_options();
	$check[$options['icon']]='checked="checked"';
	echo '<div><label><input type="radio" name="dotEPUB_options[icon]" id="dotEPUB_icon1" value="text" '.$check['text'].' /> '.__( 'Show only text', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[icon]" id="dotEPUB_icon2" value="icon" '.$check['icon'].' /> '.__( 'Show only an e-book icon', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[icon]" id="dotEPUB_icon3" value="icontext" '.$check['icontext'].' /> '.__( 'Show an e-book icon and text', 'dotepub' ).'</label></div>';
	echo '<div><label><input type="radio" name="dotEPUB_options[icon]" id="dotEPUB_icon4" value="button" '.$check['button'].' /> '.__( 'Show text as button', 'dotepub' ).'</label></div>';
}

function dotEPUB_shortcode( $atts, $content='' ){
	return dotEPUB_plugin( null, true, $content );
}

function dotEPUB_enqueue_style() {
	wp_enqueue_style( 'dotepubcss', plugins_url('s/dotepub.css',__FILE__), false ); 
}

function dotEPUB_init() {
	load_plugin_textdomain('dotepub', false, basename( dirname( __FILE__ ) ) . '/lang' );
}

add_action( 'plugins_loaded', 'dotEPUB_init' );
if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'dotEPUB_plugin_menu' );
	add_action( 'admin_init', 'dotEPUB_plugin_admin_init' );
} else {
	add_action( 'wp_enqueue_scripts', 'dotEPUB_enqueue_style' );
	add_filter( 'the_content', 'dotEPUB_plugin' );
	add_shortcode( 'dotepub', 'dotEPUB_shortcode' );
}

?>