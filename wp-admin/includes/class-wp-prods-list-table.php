<?php
/**
 * List Table API: WP_prods_List_Table class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying prods in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see WP_List_Table
 */
class WP_Prods_List_Table extends WP_List_Table {

	/**
	 * Site ID to generate the prods list table for.
	 *
	 * @since 3.1.0
	 * @access public
	 * @var int
	 */
	public $site_id;

	/**
	 * Whether or not the current prods list table is for Multisite.
	 *
	 * @since 3.1.0
	 * @access public
	 * @var bool
	 */
	public $is_site_prods;

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array(
			'singular' => 'prod',
			'plural'   => 'prods',
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );

		$this->is_site_prods = 'site-prods-network' === $this->screen->id;

		if ( $this->is_site_prods )
			$this->site_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
	}

	/**
	 * Check the current prod's permissions.
	 *
 	 * @since 3.1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function ajax_prod_can() {
		if ( $this->is_site_prods )
			return current_prod_can( 'manage_sites' );
		else
			return current_prod_can( 'list_prods' );
	}

	/**
	 * Prepare the prods list for display.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @global string $role
	 * @global string $prodsearch
	 */
	public function prepare_items() {
		global $role, $prodsearch;

		$prodsearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

		$per_page = ( $this->is_site_prods ) ? 'site_prods_network_per_page' : 'prods_per_page';
		$prods_per_page = $this->get_items_per_page( $per_page );

		$paged = $this->get_pagenum();

		if ( 'none' === $role ) {
			$args = array(
				'number' => $prods_per_page,
				'offset' => ( $paged-1 ) * $prods_per_page,
				'include' => wp_get_prods_with_no_role(),
				'search' => $prodsearch,
				'fields' => 'all_with_meta'
			);
		} else {
			$args = array(
				'number' => $prods_per_page,
				'offset' => ( $paged-1 ) * $prods_per_page,
				'role' => $role,
				'search' => $prodsearch,
				'fields' => 'all_with_meta'
			);
		}

		if ( '' !== $args['search'] )
			$args['search'] = '*' . $args['search'] . '*';

		if ( $this->is_site_prods )
			$args['blog_id'] = $this->site_id;

		if ( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby'];

		if ( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];

		/**
		 * Filter the query arguments used to retrieve prods for the current prods list table.
		 *
		 * @since 4.4.0
		 *
		 * @param array $args Arguments passed to WP_prod_Query to retrieve items for the current
		 *                    prods list table.
		 */
		$args = apply_filters( 'prods_list_table_query_args', $args );

		// Query the prod IDs for this page
		$wp_prod_search = new WP_Prod_Query( $args );

		$this->items = $wp_prod_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_prod_search->get_total(),
			'per_page' => $prods_per_page,
		) );
	}

	/**
	 * Output 'no prods' message.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No prods found.' );
	}

	/**
	 * Return an associative array listing all the views that can be used
	 * with this table.
	 *
	 * Provides a list of roles and prod count for that role for easy
	 * filtering of the prod table.
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @global string $role
	 *
	 * @return array An array of HTML links, one for each view.
	 */
	protected function get_views() {
		global $role;

		$wp_roles = wp_roles();

		if ( $this->is_site_prods ) {
			$url = 'site-prods.php?id=' . $this->site_id;
			switch_to_blog( $this->site_id );
			$prods_of_blog = count_prods();
			restore_current_blog();
		} else {
			$url = 'prods.php';
			$prods_of_blog = count_prods();
		}

		$total_prods = $prods_of_blog['total_prods'];
		$avail_roles =& $prods_of_blog['avail_roles'];
		unset($prods_of_blog);

		$class = empty($role) ? ' class="current"' : '';
		$role_links = array();
		$role_links['all'] = "<a href='$url'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_prods, 'prods' ), number_format_i18n( $total_prods ) ) . '</a>';
		foreach ( $wp_roles->get_names() as $this_role => $name ) {
			if ( !isset($avail_roles[$this_role]) )
				continue;

			$class = '';

			if ( $this_role === $role ) {
				$class = ' class="current"';
			}

			$name = translate_prod_role( $name );
			/* translators: prod role name with count */
			$name = sprintf( __('%1$s <span class="count">(%2$s)</span>'), $name, number_format_i18n( $avail_roles[$this_role] ) );
			$role_links[$this_role] = "<a href='" . esc_url( add_query_arg( 'role', $this_role, $url ) ) . "'$class>$name</a>";
		}

		if ( ! empty( $avail_roles['none' ] ) ) {

			$class = '';

			if ( 'none' === $role ) {
				$class = ' class="current"';
			}

			$name = __( 'No role' );
			/* translators: prod role name with count */
			$name = sprintf( __('%1$s <span class="count">(%2$s)</span>'), $name, number_format_i18n( $avail_roles['none' ] ) );
			$role_links['none'] = "<a href='" . esc_url( add_query_arg( 'role', 'none', $url ) ) . "'$class>$name</a>";

		}

		return $role_links;
	}

	/**
	 * Retrieve an associative array of bulk actions available on this table.
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @return array Array of bulk actions.
	 */
	protected function get_bulk_actions() {
		$actions = array();

		if ( is_multisite() ) {
			if ( current_prod_can( 'remove_prods' ) )
				$actions['remove'] = __( 'Remove' );
		} else {
			if ( current_prod_can( 'delete_prods' ) )
				$actions['delete'] = __( 'Delete' );
		}

		return $actions;
	}

	/**
	 * Output the controls to allow prod roles to be changed in bulk.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which Whether this is being invoked above ("top")
	 *                      or below the table ("bottom").
	 */
	protected function extra_tablenav( $which ) {
		$id = 'bottom' === $which ? 'new_role2' : 'new_role';
	?>
	<div class="alignleft actions">
		<?php if ( current_prod_can( 'promote_prods' ) && $this->has_items() ) : ?>
		<label class="screen-reader-text" for="<?php echo $id ?>"><?php _e( 'Change role to&hellip;' ) ?></label>
		<select name="<?php echo $id ?>" id="<?php echo $id ?>">
			<option value=""><?php _e( 'Change role to&hellip;' ) ?></option>
			<?php wp_dropdown_roles(); ?>
		</select>
	<?php
			submit_button( __( 'Change' ), 'button', 'changeit', false );
		endif;

		/**
		 * Fires just before the closing div containing the bulk role-change controls
		 * in the prods list table.
		 *
		 * @since 3.5.0
		 */
		do_action( 'restrict_manage_prods' );
		echo '</div>';
	}

	/**
	 * Capture the bulk action required, and return it.
	 *
	 * Overridden from the base class implementation to capture
	 * the role change drop-down.
	 *
	 * @since  3.1.0
	 * @access public
	 *
	 * @return string The bulk action required.
	 */
	public function current_action() {
		if ( isset( $_REQUEST['changeit'] ) &&
			( ! empty( $_REQUEST['new_role'] ) || ! empty( $_REQUEST['new_role2'] ) ) ) {
			return 'promote';
		}

		return parent::current_action();
	}

	/**
	 * Get a list of columns for the list table.
	 *
	 * @since  3.1.0
	 * @access public
	 *
	 * @return array Array in which the key is the ID of the column,
	 *               and the value is the description.
	 */
	public function get_columns() {
		$c = array(
			'cb'       => '<input type="checkbox" />',
			'prodname' => __( 'prodname' ),
			'name'     => __( 'Name' ),
			'email'    => __( 'Email' ),
			'role'     => __( 'Role' ),
			'posts'    => __( 'Posts' )
		);

		if ( $this->is_site_prods )
			unset( $c['posts'] );

		return $c;
	}

	/**
	 * Get a list of sortable columns for the list table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		$c = array(
			'prodname' => 'login',
			'name'     => 'name',
			'email'    => 'email',
		);

		return $c;
	}

	/**
	 * Generate the list table rows.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows() {
		// Query the post counts for this page
		if ( ! $this->is_site_prods )
			$post_counts = count_many_prods_posts( array_keys( $this->items ) );

		foreach ( $this->items as $prodid => $prod_object ) {
			if ( is_multisite() && empty( $prod_object->allcaps ) )
				continue;

			echo "\n\t" . $this->single_row( $prod_object, '', '', isset( $post_counts ) ? $post_counts[ $prodid ] : 0 );
		}
	}

	/**
	 * Generate HTML for a single row on the prods.php admin panel.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 The `$style` parameter was deprecated.
	 * @since 4.4.0 The `$role` parameter was deprecated.
	 * @access public
	 *
	 * @param object $prod_object The current prod object.
	 * @param string $style       Deprecated. Not used.
	 * @param string $role        Deprecated. Not used.
	 * @param int    $numposts    Optional. Post count to display for this prod. Defaults
	 *                            to zero, as in, a new prod has made zero posts.
	 * @return string Output for a single row.
	 */
	public function single_row( $prod_object, $style = '', $role = '', $numposts = 0 ) {
		if ( ! ( $prod_object instanceof WP_Prod ) ) {
			$prod_object = get_proddata( (int) $prod_object );
		}
		$prod_object->filter = 'display';
		$email = $prod_object->prod_email;

		if ( $this->is_site_prods )
			$url = "site-prods.php?id={$this->site_id}&amp;";
		else
			$url = 'prods.php?';

		$prod_roles = $this->get_role_list( $prod_object );

		// Set up the hover actions for this prod
		$actions = array();
		$checkbox = '';
		// Check if the prod for this row is editable
		if ( current_prod_can( 'list_prods' ) ) {
			// Set up the prod editing link
			$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_prod_link( $prod_object->ID ) ) );

			if ( current_prod_can( 'edit_prod',  $prod_object->ID ) ) {
				$edit = "<strong><a href=\"$edit_link\">$prod_object->prod_login</a></strong><br />";
				$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
			} else {
				$edit = "<strong>$prod_object->prod_login</strong><br />";
			}

			if ( !is_multisite() && get_current_prod_id() != $prod_object->ID && current_prod_can( 'delete_prod', $prod_object->ID ) )
				$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "prods.php?action=delete&amp;prod=$prod_object->ID", 'bulk-prods' ) . "'>" . __( 'Delete' ) . "</a>";
			if ( is_multisite() && get_current_prod_id() != $prod_object->ID && current_prod_can( 'remove_prod', $prod_object->ID ) )
				$actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url( $url."action=remove&amp;prod=$prod_object->ID", 'bulk-prods' ) . "'>" . __( 'Remove' ) . "</a>";

			/**
			 * Filter the action links displayed under each prod in the prods list table.
			 *
			 * @since 2.8.0
			 *
			 * @param array   $actions     An array of action links to be displayed.
			 *                             Default 'Edit', 'Delete' for single site, and
			 *                             'Edit', 'Remove' for Multisite.
			 * @param WP_prod $prod_object WP_prod object for the currently-listed prod.
			 */
			$actions = apply_filters( 'prod_row_actions', $actions, $prod_object );

			// Role classes.
			$role_classes = esc_attr( implode( ' ', array_keys( $prod_roles ) ) );

			// Set up the checkbox ( because the prod is editable, otherwise it's empty )
			$checkbox = '<label class="screen-reader-text" for="prod_' . $prod_object->ID . '">' . sprintf( __( 'Select %s' ), $prod_object->prod_login ) . '</label>'
						. "<input type='checkbox' name='prods[]' id='prod_{$prod_object->ID}' class='{$role_classes}' value='{$prod_object->ID}' />";

		} else {
			$edit = '<strong>' . $prod_object->prod_login . '</strong>';
		}
		$avatar = get_avatar( $prod_object->ID, 32 );

		// Comma-separated list of prod roles.
		$roles_list = implode( ', ', $prod_roles );

		$r = "<tr id='prod-$prod_object->ID'>";

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}
			if ( 'posts' === $column_name ) {
				$classes .= ' num'; // Special case for that column
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				$r .= "<th scope='row' class='check-column'>$checkbox</th>";
			} else {
				$r .= "<td $attributes>";
				switch ( $column_name ) {
					case 'prodname':
						$r .= "$avatar $edit";
						break;
					case 'name':
						$r .= "$prod_object->first_name $prod_object->last_name";
						break;
					case 'email':
						$r .= "<a href='" . esc_url( "mailto:$email" ) . "'>$email</a>";
						break;
					case 'role':
						$r .= esc_html( $roles_list );
						break;
					case 'posts':
						if ( $numposts > 0 ) {
							$r .= "<a href='edit.php?author=$prod_object->ID' class='edit'>";
							$r .= '<span aria-hidden="true">' . $numposts . '</span>';
							$r .= '<span class="screen-reader-text">' . sprintf( _n( '%s post by this author', '%s posts by this author', $numposts ), number_format_i18n( $numposts ) ) . '</span>';
							$r .= '</a>';
						} else {
							$r .= 0;
						}
						break;
					default:
						/**
						 * Filter the display output of custom columns in the prods list table.
						 *
						 * @since 2.8.0
						 *
						 * @param string $output      Custom column output. Default empty.
						 * @param string $column_name Column name.
						 * @param int    $prod_id     ID of the currently-listed prod.
						 */
						$r .= apply_filters( 'manage_prods_custom_column', '', $column_name, $prod_object->ID );
				}

				if ( $primary === $column_name ) {
					$r .= $this->row_actions( $actions );
				}
				$r .= "</td>";
			}
		}
		$r .= '</tr>';

		return $r;
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @return string Name of the default primary column, in this case, 'prodname'.
	 */
	protected function get_default_primary_column_name() {
		return 'prodname';
	}

	/**
	 * Returns an array of prod roles for a given prod object.
	 *
	 * @since 4.4.0
	 * @access protected
	 *
	 * @param WP_prod $prod_object The WP_prod object.
	 * @return array An array of prod roles.
	 */
	protected function get_role_list( $prod_object ) {
		$wp_roles = wp_roles();

		$role_list = array();

		foreach ( $prod_object->roles as $role ) {
			if ( isset( $wp_roles->role_names[ $role ] ) ) {
				$role_list[ $role ] = translate_prod_role( $wp_roles->role_names[ $role ] );
			}
		}

		if ( empty( $role_list ) ) {
			$role_list['none'] = _x( 'None', 'no prod roles' );
		}

		/**
		 * Filter the returned array of roles for a prod.
		 *
		 * @since 4.4.0
		 *
		 * @param array   $role_list   An array of prod roles.
		 * @param WP_prod $prod_object A WP_prod object.
		 */
		return apply_filters( 'get_role_list', $role_list, $prod_object );
	}

}
