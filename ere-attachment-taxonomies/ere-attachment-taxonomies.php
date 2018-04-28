<?php
/**
 * Plugin Name: Ere Taxonomies for Attachments and Medias
 * Description: A plugin that adds taxonomies to attachments and media files
 * Version: 1.0
 * Author: Erenor
 * Author URI: http://erenor.net
 * Requires at least: 4.4
 * Tested up to: 4.9.4
 *
 * Text domain: ere-attachment-taxonomies
 * Domain Path: /languages
 *
 *
 * TODO: create custom capability (and maybe add settings to assign the capability to certain roles)
 * TODO: add legend to explain different columns of the table (what does "hierarchical" mean?)
 * TODO: remove "gender": it should not be used anymore
 * TODO: remove terms when deleting a taxonomy (foreign keys?)
 */



//required files
require_once(__DIR__ . '/classes/main.php');
require_once(__DIR__ . '/classes/admin.php');




//load classes
$ereAttachmentTaxonomies = new EreAttachmentTaxonomies();



//init taxonomies loader
add_action('init', array($ereAttachmentTaxonomies, 'init'));




//back-end: load class only if into administration area
if (
	is_admin()
	&& !wp_doing_ajax()
)
{
	$ereAttachmentTaxonomies_Admin = new EreAttachmentTaxonomies_Admin();
}



