<?php
/**
 * Main class to load taxonomies and register within Wordpress
 */
class EreAttachmentTaxonomies
{
	/**
	 * Filters and Actions
	 */
//	private $filterGeneralSettings = '_ere_at_get_general_settings';
	private $filterTaxonomiesArray = '_ere_at_get_taxonomies_array';


	/**
	 * Settings for the plugin
	 *
	 * @var array $settings
	 */
	private $settingsArray = array();


	/**
	 * Array of taxonomies
	 *
	 * @var array $taxonomiesArray
	 */
	private $taxonomiesArray = array();





	/**
	 * EreAttachmentTaxonomies constructor.
	 *
	 * Load class' properties
	 */
	public function __construct()
	{
		//load plugin settings
		$this->settingsArray = $this->get_settings();

		//load taxonomies
		$this->taxonomiesArray = $this->get_taxonomies();

		//load plugin's text domain
		add_action('init', array($this, 'i18n'));
	}




	/**
	 * Translations readyness
	 */
	function i18n()
	{
		load_plugin_textdomain(
			'ere-attachment-taxonomies',
			false,
			basename(dirname(__FILE__)) . '/languages'
		);
	}





	/**
	 * Method to init taxonomies
	 */
	public function init()
	{
		//add taxonomies to wordpress' attachments
		$this->register_custom_taxonomies();
	}





	/**
	 * Method to add taxonomies to attachments runtime
	 */
	protected function register_custom_taxonomies()
	{
		//use filters to load taxonomies runtime (and let other plugins hook in)
//		$pluginSettingsArray = apply_filters($this->filterGeneralSettings, $this->settingsArray);
		$taxonomiesArray = apply_filters($this->filterTaxonomiesArray, $this->taxonomiesArray);

		if (
			//no taxonomies..
			empty($taxonomiesArray)
//			//..or no settings..
//			|| empty($pluginSettingsArray)
		)
		{
			//DEBUG
			//error_log("Taxonomies array is empty: " . var_export($taxonomiesArray, true));

			//..no need to add anything
			return;
		}

		//parse taxonomies
		foreach ($taxonomiesArray as $tempSlug => $tempTax)
		{
			//check taxonomy existance (thus, no duplicates)
			if (
				empty($tempTax['tax_slug'])
				|| taxonomy_exists($tempTax['tax_slug'])
			)
			{
				//DEBUG
				error_log("taxonomy " . var_export(taxonomy_exists($tempTax['tax_slug'])) . " already exists or is empty)!!");

				//skip this one
				continue;
			}

			//taxonomy's file path
			$taxFilePath = __DIR__ . '/../taxonomies/tax_' . $tempTax['tax_slug'] . '.php';

			//check taxonomy data
			if (
				empty($tempTax['singular'])
				|| empty($tempTax['plural'])
				|| empty($tempTax['gender'])
				|| !in_array($tempTax['gender'], array('M', 'F', 'N'))
				|| !isset($tempTax['hierarchical']) //false would be evaluated as "empty"
				|| !isset($tempTax['query_var']) //false would be evaluated as "empty"
				|| !isset($tempTax['rewrite']) //false would be evaluated as "empty"
				|| !isset($tempTax['show_admin_column']) //false would be evaluated as "empty"
				|| !file_exists($taxFilePath)
			)
			{
				//taxonomy not valid
				continue;
			}

			//create taxonomy (depending on gender for translation purposes
			include_once($taxFilePath);
//			if ($tempTax['gender'] === 'M')
//			{
//				$labels = array(
//					'name'              => $tempTax['plural'],
//					'singular_name'     => $tempTax['singular'],
//					'search_items'      => sprintf(_x("Search %s", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['plural']),
//					'all_items'         => sprintf(_x("All %s", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['plural']),
//					'parent_item'       => sprintf(_x("Parent %s", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'parent_item_colon' => sprintf(_x("Parent %s:", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'edit_item'         => sprintf(_x("Edit %s", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'update_item'       => sprintf(_x("Update %s", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'add_new_item'      => sprintf(_x("Add New %s", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'new_item_name'     => sprintf(_x("New %s Name", "Masculine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'menu_name'         => $tempTax['singular'],
//				);
//			}
//			else if ($tempTax['gender'] === 'F')
//			{
//				$labels = array(
//					'name'              => $tempTax['plural'],
//					'singular_name'     => $tempTax['singular'],
//					'search_items'      => sprintf(_x("Search %s", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['plural']),
//					'all_items'         => sprintf(_x("All %s", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['plural']),
//					'parent_item'       => sprintf(_x("Parent %s", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'parent_item_colon' => sprintf(_x("Parent %s:", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'edit_item'         => sprintf(_x("Edit %s", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'update_item'       => sprintf(_x("Update %s", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'add_new_item'      => sprintf(_x("Add New %s", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'new_item_name'     => sprintf(_x("New %s Name", "Feminine label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'menu_name'         => $tempTax['singular'],
//				);
//			}
//			else //neutral
//			{
//				$labels = array(
//					'name'              => $tempTax['plural'],
//					'singular_name'     => $tempTax['singular'],
//					'search_items'      => sprintf(_x("Search %s", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['plural']),
//					'all_items'         => sprintf(_x("All %s", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['plural']),
//					'parent_item'       => sprintf(_x("Parent %s", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'parent_item_colon' => sprintf(_x("Parent %s:", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'edit_item'         => sprintf(_x("Edit %s", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'update_item'       => sprintf(_x("Update %s", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'add_new_item'      => sprintf(_x("Add New %s", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'new_item_name'     => sprintf(_x("New %s Name", "Neutral gender label", 'ere-attachment-taxonomies'), $tempTax['singular']),
//					'menu_name'         => $tempTax['singular'],
//				);
//			}
//
//			//prepare arguments using plugin's settings
//			$args = array(
//				'labels' => $labels,
//				'hierarchical' => $tempTax['hierarchical'], //true,
//				'query_var' => $tempTax['query_var'], //'true',
//				'rewrite' => $tempTax['rewrite'], //'true',
//				'show_admin_column' => $tempTax['show_admin_column'], //'true',
//			);
//			//register current taxonomy
//			register_taxonomy($tempTax['tax_slug'], 'attachment', $args);
		}
		unset($tempSlug, $tempTax);
	}





	protected function get_settings()
	{
		//no filter to load only settings saved by this plugin
		return get_option('_ere_at_plugin_settings', array());
	}
	/**
	 * [HELPER] Add setting to current settings
	 *
	 * @param array $settingArray
	 */
	protected function add_setting($settingArray = array())
	{
		//retrieve settings
		$settingsArray = $this->get_settings();
		//add input setting to array (or update existing ones)
		$settingsArray = array_merge($settingsArray, $settingArray);
		//save taxonomies
		$this->set_settings($settingsArray);
	}
	/**
	 * [HELPER] Save settings to WP options
	 *
	 * @param array $settingsArray
	 */
	protected function set_settings($settingsArray = array())
	{
		update_option('_ere_at_plugin_settings', $settingsArray);
	}
	protected function get_taxonomies()
	{
		//no filter to load only taxonomies saved by this plugin
		return get_option('_ere_at_taxonomies', array());
	}
	/**
	 * [HELPER] Add single taxonomy to existing taxonomies in WP options
	 *
	 * @param array $taxonomyArray
	 */
	protected function add_taxonomy($taxonomyArray = array())
	{
		//retrieve taxonomies
		$taxonomiesArray = $this->get_taxonomies();
		//add input taxonomy to array
		$taxonomiesArray[$taxonomyArray['tax_slug']] = $taxonomyArray;
		//save taxonomies
		$this->set_taxonomies($taxonomiesArray);
	}
	/**
	 * [HELPER] Save taxonomies to WP options
	 *
	 * @param array $taxonomiesArray
	 */
	protected function set_taxonomies($taxonomiesArray = array())
	{
		//DEBUG
		//echo '$taxonomiesArray<pre>' . var_export($taxonomiesArray, true) . '</pre>';

		update_option('_ere_at_taxonomies', $taxonomiesArray);
	}

}




/**
 * [NOT USED]Original global function
 * /
function ere_register_custom_taxonomies()
{
	$labels = array(
		'name'              => 'Locations',
		'singular_name'     => 'Location',
		'search_items'      => 'Search Locations',
		'all_items'         => 'All Locations',
		'parent_item'       => 'Parent Location',
		'parent_item_colon' => 'Parent Location:',
		'edit_item'         => 'Edit Location',
		'update_item'       => 'Update Location',
		'add_new_item'      => 'Add New Location',
		'new_item_name'     => 'New Location Name',
		'menu_name'         => 'Location',
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'query_var' => 'true',
		'rewrite' => 'true',
		'show_admin_column' => 'true',
	);

	register_taxonomy( 'location', 'attachment', $args );
}
add_action( 'init', 'ere_register_custom_taxonomies' );
/* */

