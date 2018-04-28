<?php
/**
 * Class used to manage back-end for the plugin
 */
class EreAttachmentTaxonomies_Admin extends EreAttachmentTaxonomies
{
	/**
	 * Property used to check against to for user access
	 *
	 * @var string $capability
	 */
	private $capability = 'manage_options';
	protected $availableSections = array();
//	protected $settingsArray = array();





	/**
	 * EreAttachmentTaxonomies_Admin constructor.
	 *
	 * Load class' properties using parent's constructor
	 * Add a menu link to Wordpress' back-end
	 */
	public function __construct()
	{
		//set up properties
		$this->availableSections = array(
			'landing' => array(
				'method' => 'taxonomies_landing',
				'navmenu_label' => esc_html_x("How to", "Navigation menu labels", 'ere-attachment-taxonomies'),
				'show_in_menu' => true,
			),
			'list' => array(
				'method' => 'taxonomies_list',
				'navmenu_label' => esc_html_x("View", "Navigation menu labels", 'ere-attachment-taxonomies'),
				'show_in_menu' => true,
			),
			'new' => array(
				'method' => 'taxonomies_new',
				'navmenu_label' => esc_html_x("New / Edit", "Navigation menu labels", 'ere-attachment-taxonomies'),
				'show_in_menu' => true,
			),
			'status' => array(
				'method' => 'taxonomies_status',
				'navmenu_label' => esc_html_x("Status", "Navigation menu labels", 'ere-attachment-taxonomies'),
				'show_in_menu' => true,
			),
		);

		parent::__construct();

		add_action('admin_menu', array($this, 'add_submenu_to_admin_menu'));
	}






	/**
	 * Add entries to back-end administration menu
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	public function add_submenu_to_admin_menu()
	{
		//check user's capability
		if (!current_user_can($this->capability))
		{
			//no access to this menu
			return;
		}




		//WP admin menu link
//		add_menu_page(
//			esc_html_x("Attachments Taxonomies", "Admin Menu page title", 'ere-attachment-taxonomies'),
//			esc_html_x("Attachments Taxonomies", "Testo usato per la voce nel menu di backend", 'ere-attachment-taxonomies'),
//			'manage_options',
//			'ere-attachment-taxonomies-settings',
//			array($this, 'manage_taxonomies'),
//			//si può usare un url assoluto di un'immagine per l'icona del menu
//			'dashicons-images-alt',
//			2
//		);




		//WP admin menu submenu
		add_submenu_page(
			'options-general.php', //'ere-attachment-taxonomies-settings',
			esc_html_x("Attachments Taxonomies", "Admin Menu page title", 'ere-attachment-taxonomies'),
			esc_html_x("Attachments Taxonomies", "Testo usato per la voce nel menu di backend", 'ere-attachment-taxonomies'),
			'manage_options', //capability
			'ere-attachment-taxonomies-settings',
			array($this, 'manage_taxonomies') //callback
		);




	}






	/**
	 * Manage taxonomies for "attachment" post type
	 */
	public function manage_taxonomies()
	{
		//check user's capability
		if (!current_user_can($this->capability))
		{
			//no access to this menu
			return;
		}


		//get section
		if (
			isset($_GET['tab'])
			&& in_array($_GET['tab'], array_keys($this->availableSections))
		)
		{
			$reqSection = $_GET['tab'];
		}
		else
		{
			//get first available tab
			reset($this->availableSections);
			$reqSection = key($this->availableSections);

			//no tab requested: show main page
//			$navMenu = '';
//			$content = $this->landingPage();
		}


		//load menu
		$navMenu = $this->add_navigation_menu_tabs($reqSection);


		//load content
		$content = $this->loadContent($reqSection);


		//HTML
		?>
		<div class="wrap">

			<h1><?php echo esc_html_x("View custom taxonomies for Attachments", "Plugin page heading", 'ere-attachment-taxonomies'); ?></h1>
			<p><?php echo sprintf(esc_html_x("Here you can manage custom taxonomies for the post type '%s'", "Plugin page text", 'ere-attachment-taxonomies'), 'Attachment'); ?></p>
			<?php echo $navMenu; ?>
			<?php echo $content; ?>
		</div>
		<?php
	}





	/**
	 * Create navigation menu
	 *
	 * @param string $requestedSection
	 *
	 * @return string
	 */
	protected function add_navigation_menu_tabs($requestedSection = '')
	{
		//==
		//  Start buffering
		//==
		//==
		ob_start();





		//  CSS style
		//==
		?>
		<style type="text/css">
			nav.woo-nav-tab-wrapper { margin: 1.5em 0 1em; border-bottom: 1px solid #ccc; }
		</style>
		<?php





		//==
		//  HTML data
		//==
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
			foreach($this->availableSections as $tempSection => $tempArray)
			{
				if ($tempArray['show_in_menu'])
				{
					?>
					<a href="<?php echo admin_url('options-general.php') . '?page=ere-attachment-taxonomies-settings&tab=' . $tempSection; ?>" class="nav-tab <?php echo (($requestedSection === $tempSection)? ' nav-tab-active ' : ''); ?>"><?php echo $tempArray['navmenu_label']; ?></a>
					<?php
				}
			}
			?>
			<?php /* * / ?>
			<a href="<?php echo admin_url('admin.php?page=ere-attachment-taxonomies-settings'); ?>" class="nav-tab <?php echo ((empty($requestedSection) || ($requestedSection === 'list'))? ' nav-tab-active ' : ''); ?>"><?php echo esc_html__("View", 'ere-attachment-taxonomies'); ?></a>
			<a href="<?php echo admin_url('admin.php') . '?page=ere-attachment-taxonomies-settings&tab=new'; ?>" class="nav-tab <?php echo (($requestedSection === 'new')? ' nav-tab-active ' : ''); ?>"><?php echo esc_html__("New", 'ere-attachment-taxonomies'); ?></a>
			<?php /* */ ?>
		</nav>
		<?php





		//==
		//  Return html data
		//==
		return ob_get_clean();
	}





	/**
	 * Load correct content tab
	 *
	 * @param string $requestedSection
	 *
	 * @return string
	 */
	protected function loadContent($requestedSection = '')
	{
		if (empty($requestedSection))
		{
			return $this->taxonomies_list();
		}
		else
		{
			//activate tab depending on chosen section
			return call_user_func(array($this, $this->availableSections[$requestedSection]['method']));
		}
	}




	/**
	 * [HELPER] Sanitize slug
	 *
	 * @param string $inputString
	 *
	 * @return string
	 */
	protected function sanitizeSlug($inputString)
	{
		//strip string from unwanted chars
		$count = 0;
		$inputString = preg_replace('/[^a-z0-9]+/', '', $inputString, -1, $count);

		//make sure max length is 25 chars
		$inputString = substr($inputString, 0, 25);

//		//only letters and numbers
//		|| !ctype_alnum($_POST['ere_taxonomies_slug'])
//		//first char: letter
//		|| !ctype_alpha($_POST['ere_taxonomies_slug'][0])
//		//max 25 chars
//		|| (strlen($_POST['ere_taxonomies_slug']) > 25)
//		||
//		(
//			$newSlug
//			&& taxonomy_exists($_POST['ere_taxonomies_slug'])
//		)

		return $inputString;
	}
	/**
	 * [HELPER] Sanitize singular
	 *
	 * @param string $inputString
	 *
	 * @return string
	 */
	protected function sanitizeSingular($inputString)
	{
		//strip string from unwanted chars
		$count = 0;
		$inputString = preg_replace('/[^a-z0-9 ]+/i', '', $inputString, -1, $count);

		//make sure max length is 25 chars
		$inputString = substr($inputString, 0, 25);

//		//only letters and numbers
//		|| !ctype_alnum($_POST['ere_taxonomies_slug'])
//		//first char: letter
//		|| !ctype_alpha($_POST['ere_taxonomies_slug'][0])
//		//max 25 chars
//		|| (strlen($_POST['ere_taxonomies_slug']) > 25)
//		||
//		(
//			$newSlug
//			&& taxonomy_exists($_POST['ere_taxonomies_slug'])
//		)

		return $inputString;
	}
	/**
	 * [HELPER] Sanitize plural
	 *
	 * @param string $inputString
	 *
	 * @return string
	 */
	protected function sanitizePlural($inputString)
	{
		//strip string from unwanted chars
		$count = 0;
		$inputString = preg_replace('/[^a-z0-9 ]+/i', '', $inputString, -1, $count);

		//make sure max length is 25 chars
		$inputString = substr($inputString, 0, 25);

		return $inputString;
	}




	/**
	 * Taxonomies list
	 *
	 * @return string
	 */
	protected function taxonomies_list()
	{
		//defaults
		$errorOccurred = array();
		$dataReceived = false;
		$removedTaxesCounter = 0;




		//check POST
		if (
			isset($_POST['ere_attachment_taxonomies_nonce'])
			&& wp_verify_nonce($_POST['ere_attachment_taxonomies_nonce'], basename(__FILE__))
		)
		{
			//we have received input
			$dataReceived = true;


			//load data
			$taxonomiesArray = parent::get_taxonomies();


			//check input
			if (
				!empty($_POST['ere_taxonomies_slugs'])
				&& is_array($_POST['ere_taxonomies_slugs'])
			)
			{
				$removeTaxonomiesArray = $_POST['ere_taxonomies_slugs'];

				//check each taxonomy
				foreach($taxonomiesArray as $key => $tempTaxArray)
				{
					//remove taxonomy from the settings
					if (in_array($tempTaxArray['tax_slug'], $removeTaxonomiesArray))
					{
						//remove taxonomy
						unset($taxonomiesArray[$key]);
						$removedTaxesCounter++;

						//remove taxonomy file
						$taxFilePath = __DIR__ . '/../taxonomies/tax_' . $tempTaxArray['tax_slug'] . '.php';
						if (file_exists($taxFilePath))
						{
							$result = unlink($taxFilePath);
							if (!$result)
							{
								$errorOccurred[] = sprintf(esc_html_x("Unable to delete file for taxonomy %s. Taxonomy has been correctly removed from settings.", "Error strings", 'ere-attachment-taxonomies'), $tempTaxArray['tax_slug']);
							}
						}
						else
						{
							$errorOccurred[] = sprintf(esc_html_x("Unable to find taxonomy file for %s. Taxonomy has been correctly removed from settings.", "Error strings", 'ere-attachment-taxonomies'), $tempTaxArray['tax_slug']);
						}


					}
					//else: taxonomy not selected for deletion

				}
				unset($tempTaxArray);

				//update taxonomies array
				parent::set_taxonomies($taxonomiesArray);

			}
			else
			{
				$errorOccurred[] = esc_html_x("Slugs not found", "Error strings", 'ere-attachment-taxonomies');
			}
		}






		//load data
		$taxonomiesArray = parent::get_taxonomies();




		//output buffer
		ob_start();




		//Css
		?>
		<style type="text/css">
			ul, li {
				list-style-type: none;
			}
			ul, li {
				margin:0; padding:0;
			}

			div.dkr_scrollable_table_inner table {
				min-width:100%;
			}

			div.dkr_scrollable_table_inner tbody td {
				text-align: center;
				white-space: nowrap;
				border-left: 1px solid #ccc;
			}
			div.dkr_scrollable_table_inner tbody td:last-child {
				border-right: 1px solid #ccc;
			}

			input:disabled, select:disabled {
				cursor:not-allowed;
			}

			div.dkr_scrollable_table_inner {
				overflow:auto;
			}
		</style>
		<?php




		//table heading
		ob_start();
		?>
		<th><?php echo esc_html_x("Flag to remove", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Slug", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Singular", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Plural", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Gender", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Hierarchical?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Query Var?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Rewrite?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Show Admin Column?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<?php
		$tableHeading = ob_get_clean();




		//Html
		?>
		<form method="post" novalidate="novalidate">

			<?php
			//show errors, if any
			if (
				$dataReceived
				&& !empty($errorOccurred)
			)
			{
				//show errors
				?>
				<div id="message" class="notice notice-error is-dismissible">
					<?php
					foreach($errorOccurred as $tempErrorText)
					{
						?>
						<p><strong><?php echo $tempErrorText; ?></strong></p><button type="button" class="notice-dismiss"></button>
						<?php
					}
					?>
				</div>
				<?php
			}
			else if ($dataReceived)
			{
				if ($removedTaxesCounter === 1)
				{
					?>
					<div id="message" class="notice notice-success is-dismissible">
						<?php echo esc_html_x("One taxonomy correctly removed", "Error strings", 'ere-attachment-taxonomies'); ?>
					</div>
					<?php
				}
				else
				{
					?>
					<div id="message" class="notice notice-success is-dismissible">
						<?php echo sprintf(esc_html_x("%s taxonomies correctly removed", "Error strings", 'ere-attachment-taxonomies'), $removedTaxesCounter); ?>
					</div>
					<?php
				}
				//show success message
			}
			?>
			<div class="dkr_scrollable_table_outer">
				<div class="dkr_scrollable_table_inner">
					<table class="table table-striped table-hover">
						<thead>
						<tr>
							<?php echo $tableHeading; ?>
						</tr>
						</thead>
						<tbody>
						<?php
						if (!empty($taxonomiesArray))
						{
							$yesString = esc_html_x("Yes", "Values for back-end table", 'ere-attachment-taxonomies');
							$noString = esc_html_x("No", "Values for back-end table", 'ere-attachment-taxonomies');

							//parse available modules
							foreach($taxonomiesArray as $tempTax)
							{
								?>
								<tr class="<?php //echo (in_array($tempModule, $ereEnabledTools)? 'success' : 'warning'); ?>">
									<td><input title="" type="checkbox" value="<?php echo $tempTax['tax_slug']; ?>" name="ere_taxonomies_slugs[]" /></td>
									<td><a href="<?php echo admin_url('options-general.php') . '?page=ere-attachment-taxonomies-settings&tab=new&slug=' . $tempTax['tax_slug']; ?>"><?php echo $tempTax['tax_slug']; ?></a></td>
									<td><?php echo $tempTax['singular']; ?></td>
									<td><?php echo $tempTax['plural']; ?></td>
									<td><?php echo $tempTax['gender']; ?></td>
									<td><?php echo (empty($tempTax['hierarchical'])? $noString : $yesString); ?></td>
									<td><?php echo (empty($tempTax['query_var'])? $noString : $yesString); ?></td>
									<td><?php echo (empty($tempTax['rewrite'])? $noString : $yesString); ?></td>
									<td><?php echo (empty($tempTax['show_admin_column'])? $noString : $yesString); ?></td>
								</tr>
								<?php
							}
							unset($tempModule);
						}
						else
						{
							?>
							<tr>
								<td colspan="8"><?php echo esc_html__("No saved taxonomies", 'ere-attachment-taxonomies'); ?></td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
			<p class="submit"><input type="submit" class="button button-primary" value="<?php echo esc_html__("Remove selected taxonomies", 'ere-attachment-taxonomies'); ?>" /></p>
			<?php wp_nonce_field(basename(__FILE__), 'ere_attachment_taxonomies_nonce'); ?>

		</form>
		<?php
		return ob_get_clean();
	}



	/**
	 * New taxonomy
	 *
	 * @return string
	 */
	protected function taxonomies_new()
	{
		//defaults
		$errorOccurred = array();
		$dataReceived = false;


		//load data
		$taxonomiesArray = parent::get_taxonomies();


		//check if a slug has been provided for editing
		if (
			isset($_GET['slug'])
			&& ctype_alnum($_GET['slug'])
			&& taxonomy_exists($_GET['slug'])
			&& !empty($taxonomiesArray[$_GET['slug']])
		)
		{
			$taxonomySettingsArray = $taxonomiesArray[$_GET['slug']];
			$newSlug = false;
		}
		else
		{
			//taxonomy settings array
			$taxonomySettingsArray = array();
			$newSlug = true;
		}


		//check POST
		if (
			isset($_POST['ere_attachment_taxonomies_nonce'])
			&& wp_verify_nonce($_POST['ere_attachment_taxonomies_nonce'], basename(__FILE__))
		)
		{
			//we have received input
			$dataReceived = true;


			//check input
			if (empty($_POST['ere_taxonomies_slug']))
			{
				$errorOccurred[] = esc_html_x("Slug is missing", "Error strings", 'ere-attachment-taxonomies');
			}
			else
			{
				$taxonomySettingsArray['tax_slug'] = strtolower($this->sanitizeSlug($_POST['ere_taxonomies_slug']));

				if ($taxonomySettingsArray['tax_slug'] !== $_POST['ere_taxonomies_slug'])
				{
					$errorOccurred[] = esc_html_x("Slug contains not valid chars (make sure it's lowercase)", "Error strings", 'ere-attachment-taxonomies');
				}
				else if (taxonomy_exists($taxonomySettingsArray['tax_slug']))
				{
					$errorOccurred[] = esc_html_x("Taxonomy slug already exists", "Error strings", 'ere-attachment-taxonomies');
				}
			}

			if (empty($_POST['ere_taxonomies_singular']))
			{
				$errorOccurred[] = esc_html_x("Singular is missing", "Error strings", 'ere-attachment-taxonomies');
			}
			else
			{
				$taxonomySettingsArray['singular'] = $this->sanitizeSingular($_POST['ere_taxonomies_singular']);

				if ($taxonomySettingsArray['singular'] !== $_POST['ere_taxonomies_singular'])
				{
					$errorOccurred[] = esc_html_x("Singular contains not valid chars", "Error strings", 'ere-attachment-taxonomies');
				}
			}

			if (empty($_POST['ere_taxonomies_plural']))
			{
				$errorOccurred[] = esc_html_x("Plural is missing", "Error strings", 'ere-attachment-taxonomies');
			}
			else
			{
				$taxonomySettingsArray['plural'] = $this->sanitizePlural($_POST['ere_taxonomies_plural']);

				if ($taxonomySettingsArray['plural'] !== $_POST['ere_taxonomies_plural'])
				{
					$errorOccurred[] = esc_html_x("Plural contains not valid chars", "Error strings", 'ere-attachment-taxonomies');
				}
			}

			if (
				empty($_POST['ere_taxonomies_gender'])
				|| !in_array($_POST['ere_taxonomies_gender'], array('M', 'F', 'N',))
			)
			{
				$errorOccurred[] = esc_html_x("Gender not valid", "Error strings", 'ere-attachment-taxonomies');
			}
			else
			{
				$taxonomySettingsArray['gender'] = $_POST['ere_taxonomies_gender'];
			}

			if (
				empty($_POST['ere_taxonomies_hierarchical'])
			)
			{
				$taxonomySettingsArray['hierarchical'] = false;
			}
			else
			{
				$taxonomySettingsArray['hierarchical'] = true;
			}

			if (
				empty($_POST['ere_taxonomies_query_var'])
			)
			{
				$taxonomySettingsArray['query_var'] = false;
			}
			else
			{
				$taxonomySettingsArray['query_var'] = true;
			}

			if (
				empty($_POST['ere_taxonomies_rewrite'])
			)
			{
				$taxonomySettingsArray['rewrite'] = false;
			}
			else
			{
				$taxonomySettingsArray['rewrite'] = true;
			}

			if (
				empty($_POST['ere_taxonomies_show_admin_column'])
			)
			{
				$taxonomySettingsArray['show_admin_column'] = false;
			}
			else
			{
				$taxonomySettingsArray['show_admin_column'] = true;
			}

			//check errors
			if (empty($errorOccurred))
			{
				//all inputs are valid: add taxonomy
				$this->add_taxonomy($taxonomySettingsArray);

				//create taxonomy file
				$taxFilePath = __DIR__ . '/../taxonomies/tax_' . $taxonomySettingsArray['tax_slug'] . '.php';

				//create string
				$taxFileString = <<< 'NOWDOC'
<?php
$labels = array(
	'name'              => _x("[[plural]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'singular_name'     => _x("[[singular]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'search_items'      => _x("Search [[plural]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'all_items'         => _x("All [[plural]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'parent_item'       => _x("Parent [[singular]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'parent_item_colon' => _x("Parent [[singular]]:", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'edit_item'         => _x("Edit [[singular]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'update_item'       => _x("Update [[singular]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'add_new_item'      => _x("Add New [[singular]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'new_item_name'     => _x("New [[singular]] Name", "Taxonomy labels", 'ere-attachment-taxonomies'),
	'menu_name'         => _x("[[singular]]", "Taxonomy labels", 'ere-attachment-taxonomies'),
);
//prepare arguments using plugin's settings
$args = array(
	'labels' => $labels,
	'hierarchical' => [[hierarchical]], //true,
	'query_var' => [[query_var]], //'true',
	'rewrite' => [[rewrite]], //'true',
	'show_admin_column' => [[show_admin_column]], //'true',
);
//register current taxonomy
register_taxonomy('[[tax_slug]]', 'attachment', $args);

NOWDOC;

				//replace values within string
				$taxFileString = str_replace(
					array(
						'[[singular]]',
						'[[plural]]',
						'[[hierarchical]]',
						'[[query_var]]',
						'[[rewrite]]',
						'[[show_admin_column]]',
						'[[tax_slug]]',
					),
					array(
						$taxonomySettingsArray['singular'],
						$taxonomySettingsArray['plural'],
						empty($taxonomySettingsArray['hierarchical'])? 'false' : 'true',
						empty($taxonomySettingsArray['query_var'])? 'false' : 'true',
						empty($taxonomySettingsArray['rewrite'])? 'false' : 'true',
						empty($taxonomySettingsArray['show_admin_column'])? 'false' : 'true',
						$taxonomySettingsArray['tax_slug'],
					),
					$taxFileString
				);

				//save taxonomy file (can overwrite existing custom taxonomy)
				$result = file_put_contents($taxFilePath, $taxFileString);

				if ($result === false)
				{
					$errorOccurred[] = esc_html_x("Unable to write taxonomy file. Taxonomy inserted into settings", "Error strings", 'ere-attachment-taxonomies');
				}

				//remove input data if taxonomy has been saved
				$taxonomySettingsArray = array();
			}


		}



		//output buffer
		ob_start();



		//Css
		?>
		<style type="text/css">
			ul, li {
				list-style-type: none;
			}
			ul, li {
				margin:0; padding:0;
			}

			div.dkr_scrollable_table_inner table {
				min-width:100%;
			}

			div.dkr_scrollable_table_inner tbody td {
				text-align: center;
				white-space: nowrap;
				border-left: 1px solid #ccc;
			}
			div.dkr_scrollable_table_inner tbody td:last-child {
				border-right: 1px solid #ccc;
			}

			input:disabled, select:disabled {
				cursor:not-allowed;
			}

			div.dkr_scrollable_table_inner {
				overflow:auto;
			}
		</style>
		<?php




		//table heading
		ob_start();
		?>
		<th><?php echo esc_html_x("Slug", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Singular", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Plural", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Gender", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Hierarchical?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Query Var?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Rewrite?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<th><?php echo esc_html_x("Show Admin Column?", "Heading for back-end table", 'ere-attachment-taxonomies'); ?></th>
		<?php
		$tableHeading = ob_get_clean();




		?>
		<form method="post" novalidate="novalidate">

			<?php
			//show errors, if any
			if (
				$dataReceived
				&& !empty($errorOccurred)
			)
			{
				//show errors
				?>
				<div id="message" class="notice notice-error is-dismissible">
					<?php
					foreach($errorOccurred as $tempErrorText)
					{
						?>
						<p><strong><?php echo $tempErrorText; ?></strong></p><button type="button" class="notice-dismiss"></button>
						<?php
					}
					?>
				</div>
				<?php
			}
			else if ($dataReceived)
			{
				//show success message
				if ($newSlug)
				{
					$messageString = esc_html__("Taxonomy successfully added", 'ere-attachment-taxonomies');
				}
				else
				{
					$messageString = esc_html__("Taxonomy successfully modified", 'ere-attachment-taxonomies');
				}
				?>
				<div id="message" class="notice notice-success is-dismissible">
					<?php echo $messageString; ?>
				</div>
				<?php
			}

			//title
			if ($newSlug)
			{
				$titleString = esc_html__("Add new taxonomy below", 'ere-attachment-taxonomies');
			}
			else
			{
				$titleString = esc_html__("Edit your taxonomy below", 'ere-attachment-taxonomies');
			}
			?>
			<h3><?php echo $titleString; ?></h3>
			<div class="dkr_scrollable_table_outer">
				<div class="dkr_scrollable_table_inner">
					<table class="table table-striped table-hover">
						<thead>
						<tr>
							<?php echo $tableHeading; ?>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>
								<input
									type="text"
									title=""
									name="ere_taxonomies_slug"
									value="<?php echo (!empty($taxonomySettingsArray['tax_slug'])? $taxonomySettingsArray['tax_slug'] : ''); ?>"
								/>
							</td>
							<td>
								<input
									type="text"
									title=""
									name="ere_taxonomies_singular"
									value="<?php echo (!empty($taxonomySettingsArray['singular'])? $taxonomySettingsArray['singular'] : ''); ?>"
								/>
							</td>
							<td>
								<input
									type="text"
									title=""
									name="ere_taxonomies_plural"
									value="<?php echo (!empty($taxonomySettingsArray['plural'])? $taxonomySettingsArray['plural'] : ''); ?>"
								/>
							</td>
							<td>
								<select title="" name="ere_taxonomies_gender">
									<option value="0" <?php selected("0", $taxonomySettingsArray['gender']); ?>>
										<?php echo esc_html__("Select one", 'ere-attachment-taxonomies'); ?>
									</option>
									<option value="M" <?php selected("M", $taxonomySettingsArray['gender']); ?>>
										<?php echo esc_html__("Masculine", 'ere-attachment-taxonomies'); ?>
									</option>
									<option value="F" <?php selected("F", $taxonomySettingsArray['gender']); ?>>
										<?php echo esc_html__("Feminine", 'ere-attachment-taxonomies'); ?>
									</option>
									<option value="N" <?php selected("N", $taxonomySettingsArray['gender']); ?>>
										<?php echo esc_html__("Neutral", 'ere-attachment-taxonomies'); ?>
									</option>
							</td>
							<td>
								<input
									type="checkbox"
									title=""
									name="ere_taxonomies_hierarchical"
									value="1"
									<?php checked($taxonomySettingsArray['hierarchical']); ?>
								/>
							</td>
							<td>
								<input
									type="checkbox"
									title=""
									name="ere_taxonomies_query_var"
									value="1"
									<?php checked($taxonomySettingsArray['query_var']); ?>
								/>
							</td>
							<td>
								<input
									type="checkbox"
									title=""
									name="ere_taxonomies_rewrite"
									value="1"
									<?php checked($taxonomySettingsArray['rewrite']); ?>
								/>
							</td>
							<td>
								<input
									type="checkbox"
									title=""
									name="ere_taxonomies_show_admin_column"
									value="1"
									<?php checked($taxonomySettingsArray['show_admin_column']); ?>
								/>
							</td>
						</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" class="button button-primary" value="<?php echo esc_html__("Save", 'ere-attachment-taxonomies'); ?>" /></p>
					<?php wp_nonce_field(basename(__FILE__), 'ere_attachment_taxonomies_nonce'); ?>
				</div>
			</div>

		</form>
		<?php
		return ob_get_clean();
	}





	/**
	 * Landing tab (mainly explanations)
	 *
	 * @return string
	 */
	protected function taxonomies_landing()
	{
		//output buffer
		ob_start();




		//Css
		?>
		<style type="text/css">
			ul, li {
				list-style-type: none;
			}
			ul, li {
				margin:0; padding:0;
			}

			div.dkr_scrollable_table_inner table {
				min-width:100%;
			}

			div.dkr_scrollable_table_inner tbody td {
				text-align: center;
				white-space: nowrap;
				border-left: 1px solid #ccc;
			}
			div.dkr_scrollable_table_inner tbody td:last-child {
				border-right: 1px solid #ccc;
			}

			input:disabled, select:disabled {
				cursor:not-allowed;
			}

			div.dkr_scrollable_table_inner {
				overflow:auto;
			}
		</style>
		<?php




		//table heading
		ob_start();


		//Html
		?>
		<h2><?php echo esc_html_x("Welcome to Ere's Taxonomies Attachments", "Plugin explanations", 'ere-attachment-taxonomies'); ?></h2>
		<h3><?php echo esc_html_x("This plugin helps you defining custom taxonomies for the Attachments", "Plugin explanations", 'ere-attachment-taxonomies'); ?></h3>
		<p><?php echo esc_html_x("You can create a new taxonomy by selecting the 'New / Edit' tab and choosing the options that suit you", "Plugin explanations", 'ere-attachment-taxonomies'); ?></p>
		<p><?php echo esc_html_x("To get an overview of the currently created taxonomies, select the 'View' tab. There you can also delete a taxonomy by flagging it and pressing the Remove button", "Plugin explanations", 'ere-attachment-taxonomies'); ?></p>
		<p><?php echo esc_html_x("Before using the plugin, try the 'Status' tab to check if everything is ok and functional for your Wordpress installation", "Plugin explanations", 'ere-attachment-taxonomies'); ?></p>
		<?php
		return ob_get_clean();
	}




	/**
	 * Test and current status tab
	 */
	protected function taxonomies_status()
	{
		ob_start();

		$messages = array(
			'right' => array(),
			'wrong' => array(),
		);

		$testFilePath = __DIR__ . '/../taxonomies/test.php';


		//check if directory "taxonomies" is writeable
		$isWritable = false;
		try
		{
			$result = file_put_contents($testFilePath, '<?php' . "\n" . '//this is only a test file');
			if ($result !== false)
			{
				$isWritable = true;
				$messages['right'][] = esc_html_x("Directory is writable: you will be able to create taxonomies correctly", "Error messages", 'ere-attachment-taxonomies');
			}
			else
			{
				$messages['wrong'][] = esc_html_x("Unable to create test file into directory 'taxonomies': cannot create taxonomies automatically. You will be prompted with the code that have be inserted in a new file after creating a new taxonomy", "Error messages", 'ere-attachment-taxonomies');
			}
		}
		catch (Exception $e)
		{
			$messages['wrong'][] = esc_html_x("Directory 'taxonomies' is not writeable: cannot create taxonomies automatically. You will be prompted with the code that have be inserted in a new file after creating a new taxonomy", "Error messages", 'ere-attachment-taxonomies');
		}
		//check if files in directory "taxonomies" are deletable
		$isDeletable = false;
		try
		{
			if (file_exists($testFilePath))
			{
				$result = unlink($testFilePath);
				if ($result)
				{
					$messages['right'][] = esc_html_x("Files are deletable: you will be able to delete taxonomies correctly", "Error messages", 'ere-attachment-taxonomies');
					$isDeletable = true;
				}
				else
				{
					$messages['wrong'][] = esc_html_x("Cannot delete files within directory 'taxonomies': you will have to manually remove the taxonomy file when deleting the taxonomy", "Error messages", 'ere-attachment-taxonomies');
				}
			}
			else
			{
				$messages['wrong'][] = esc_html_x("Unable to find test file", "Error messages", 'ere-attachment-taxonomies');
			}

		}
		catch (Exception $e)
		{
			$messages['wrong'][] = esc_html_x("An error occurred while checking if files are deletable in 'taxonomies' directory: you will have to manually remove the taxonomy file when deleting the taxonomy", "Error messages", 'ere-attachment-taxonomies');
		}

		//update WP options
		$this->add_setting(array('taxdir_iswriteable' => (int) $isWritable));
		$this->add_setting(array('taxdir_isdeletable' => (int) $isDeletable));



		foreach($messages['right'] as $tempMessage)
		{
			?>
			<p class="notice notice-success"><strong><?php echo $tempMessage; ?></strong></p><button type="button" class="notice-dismiss"></button>
			<?php
		}
		unset($tempMessage);

		foreach($messages['wrong'] as $tempMessage)
		{
			?>
			<p class="notice notice-error"><strong><?php echo $tempMessage; ?></strong></p><button type="button" class="notice-dismiss"></button>
			<?php
		}
		unset($tempMessage);

		return ob_get_clean();
	}



}





