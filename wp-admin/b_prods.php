<?php
/**
 * prod administration panel
 *
 * @package WordPress
 * @subpackage Administration
 * @since 1.0.0
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can( 'list_prods' ) ) {
	wp_die(
		'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
		'<p>' . __( 'You are not allowed to browse prods.' ) . '</p>',
		403
	);
}

$wp_list_table = _get_list_table('WP_Prods_List_Table');
$pagenum = $wp_list_table->get_pagenum();
$title = __('Prods');
$parent_file = 'prods.php';

add_screen_option( 'per_page' );

// contextual help - choose Help on the top right of admin panel to preview this.
get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => '<p>' . __('This screen lists all the existing prods for your site. Each prod has one of five defined roles as set by the site admin: Site Administrator, Editor, Author, Contributor, or Subscriber. Prods with roles other than Administrator will see fewer options in the dashboard navigation when they are logged in, based on their role.') . '</p>' .
				 '<p>' . __('To add a new prod for your site, click the Add New button at the top of the screen or Add New in the Prods menu section.') . '</p>'
) ) ;

get_current_screen()->add_help_tab( array(
	'id'      => 'screen-display',
	'title'   => __('Screen Display'),
	'content' => '<p>' . __('You can customize the display of this screen in a number of ways:') . '</p>' .
					'<ul>' .
					'<li>' . __('You can hide/display columns based on your needs and decide how many prods to list per screen using the Screen Options tab.') . '</li>' .
					'<li>' . __('You can filter the list of prods by Prod Role using the text links in the upper left to show All, Administrator, Editor, Author, Contributor, or Subscriber. The default view is to show all prods. Unused Prod Roles are not listed.') . '</li>' .
					'<li>' . __('You can view all posts made by a prod by clicking on the number under the Posts column.') . '</li>' .
					'</ul>'
) );

$help = '<p>' . __('Hovering over a row in the prods list will display action links that allow you to manage prods. You can perform the following actions:') . '</p>' .
	'<ul>' .
	'<li>' . __('Edit takes you to the editable profile screen for that prod. You can also reach that screen by clicking on the prodname.') . '</li>';

if ( is_multisite() )
	$help .= '<li>' . __( 'Remove allows you to remove a prod from your site. It does not delete their content. You can also remove multiple prods at once by using Bulk Actions.' ) . '</li>';
else
	$help .= '<li>' . __( 'Delete brings you to the Delete Prods screen for confirmation, where you can permanently remove a prod from your site and delete their content. You can also delete multiple prods at once by using Bulk Actions.' ) . '</li>';

$help .= '</ul>';

get_current_screen()->add_help_tab( array(
	'id'      => 'actions',
	'title'   => __('Actions'),
	'content' => $help,
) );
unset( $help );

get_current_screen()->set_help_sidebar(
    '<p><strong>' . __('For more information:') . '</strong></p>' .
    '<p>' . __('<a href="https://codex.wordpress.org/Prods_Screen" target="_blank">Documentation on Managing Prods</a>') . '</p>' .
    '<p>' . __('<a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Descriptions of Roles and Capabilities</a>') . '</p>' .
    '<p>' . __('<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter prods list' ),
	'heading_pagination' => __( 'Prods list navigation' ),
	'heading_list'       => __( 'Prods list' ),
) );

if ( empty($_REQUEST) ) {
	$referer = '<input type="hidden" name="wp_http_referer" value="'. esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" />';
} elseif ( isset($_REQUEST['wp_http_referer']) ) {
	$redirect = remove_query_arg(array('wp_http_referer', 'updated', 'delete_count'), wp_unslash( $_REQUEST['wp_http_referer'] ) );
	$referer = '<input type="hidden" name="wp_http_referer" value="' . esc_attr($redirect) . '" />';
} else {
	$redirect = 'prods.php';
	$referer = '';
}

$update = '';

switch ( $wp_list_table->current_action() ) {

/* Bulk Dropdown menu Role changes */
case 'promote':
	check_admin_referer('bulk-prods');

	if ( ! current_prod_can( 'promote_prods' ) )
		wp_die( __( 'You can&#8217;t edit that prod.' ) );

	if ( empty($_REQUEST['prods']) ) {
		wp_redirect($redirect);
		exit();
	}

	$editable_roles = get_editable_roles();
	$role = false;
	if ( ! empty( $_REQUEST['new_role2'] ) ) {
		$role = $_REQUEST['new_role2'];
	} elseif ( ! empty( $_REQUEST['new_role'] ) ) {
		$role = $_REQUEST['new_role'];
	}

	if ( ! $role || empty( $editable_roles[ $role ] ) ) {
		wp_die( __( 'You can&#8217;t give prods that role.' ) );
	}

	$prodids = $_REQUEST['prods'];
	$update = 'promote';
	foreach ( $prodids as $id ) {
		$id = (int) $id;

		if ( ! current_prod_can('promote_prod', $id) )
			wp_die(__('You can&#8217;t edit that prod.'));
		// The new role of the current prod must also have the promote_prods cap or be a multisite super admin
		if ( $id == $current_prod->ID && ! $wp_roles->role_objects[ $role ]->has_cap('promote_prods')
			&& ! ( is_multisite() && is_super_admin() ) ) {
				$update = 'err_admin_role';
				continue;
		}

		// If the prod doesn't already belong to the blog, bail.
		if ( is_multisite() && !is_prod_member_of_blog( $id ) ) {
			wp_die(
				'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
				'<p>' . __( 'One of the selected prods is not a member of this site.' ) . '</p>',
				403
			);
		}

		$prod = get_proddata( $id );
		$prod->set_role( $role );
	}

	wp_redirect(add_query_arg('update', $update, $redirect));
	exit();

case 'dodelete':
	if ( is_multisite() )
		wp_die( __('Prod deletion is not allowed from this screen.') );

	check_admin_referer('delete-prods');

	if ( empty($_REQUEST['prods']) ) {
		wp_redirect($redirect);
		exit();
	}

	$prodids = array_map( 'intval', (array) $_REQUEST['prods'] );

	if ( empty( $_REQUEST['delete_option'] ) ) {
		$url = self_admin_url( 'prods.php?action=delete&prods[]=' . implode( '&prods[]=', $prodids ) . '&error=true' );
		$url = str_replace( '&amp;', '&', wp_nonce_url( $url, 'bulk-prods' ) );
		wp_redirect( $url );
		exit;
	}

	if ( ! current_prod_can( 'delete_prods' ) )
		wp_die(__('You can&#8217;t delete prods.'));

	$update = 'del';
	$delete_count = 0;

	foreach ( $prodids as $id ) {
		if ( ! current_prod_can( 'delete_prod', $id ) )
			wp_die(__( 'You can&#8217;t delete that prod.' ) );

		if ( $id == $current_prod->ID ) {
			$update = 'err_admin_del';
			continue;
		}
		switch ( $_REQUEST['delete_option'] ) {
		case 'delete':
			wp_delete_prod( $id );
			break;
		case 'reassign':
			wp_delete_prod( $id, $_REQUEST['reassign_prod'] );
			break;
		}
		++$delete_count;
	}

	$redirect = add_query_arg( array('delete_count' => $delete_count, 'update' => $update), $redirect);
	wp_redirect($redirect);
	exit();

case 'delete':
	if ( is_multisite() )
		wp_die( __('Prod deletion is not allowed from this screen.') );

	check_admin_referer('bulk-prods');

	if ( empty($_REQUEST['prods']) && empty($_REQUEST['prod']) ) {
		wp_redirect($redirect);
		exit();
	}

	if ( ! current_prod_can( 'delete_prods' ) )
		$errors = new WP_Error( 'edit_prods', __( 'You can&#8217;t delete prods.' ) );

	if ( empty($_REQUEST['prods']) )
		$prodids = array( intval( $_REQUEST['prod'] ) );
	else
		$prodids = array_map( 'intval', (array) $_REQUEST['prods'] );

	$prods_have_content = false;
	if ( $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_author IN( " . implode( ',', $prodids ) . " ) LIMIT 1" ) ) {
		$prods_have_content = true;
	} elseif ( $wpdb->get_var( "SELECT link_id FROM {$wpdb->links} WHERE link_owner IN( " . implode( ',', $prodids ) . " ) LIMIT 1" ) ) {
		$prods_have_content = true;
	}

	if ( $prods_have_content ) {
		add_action( 'admin_head', 'delete_prods_add_js' );
	}

	include( ABSPATH . 'wp-admin/admin-header.php' );
?>
<form method="post" name="updateprods" id="updateprods">
<?php wp_nonce_field('delete-prods') ?>
<?php echo $referer; ?>

<div class="wrap">
<h1><?php _e( 'Delete Prods' ); ?></h1>
<?php if ( isset( $_REQUEST['error'] ) ) : ?>
	<div class="error">
		<p><strong><?php _e( 'ERROR:' ); ?></strong> <?php _e( 'Please select an option.' ); ?></p>
	</div>
<?php endif; ?>

<?php if ( 1 == count( $prodids ) ) : ?>
	<p><?php _e( 'You have specified this prod for deletion:' ); ?></p>
<?php else : ?>
	<p><?php _e( 'You have specified these prods for deletion:' ); ?></p>
<?php endif; ?>

<ul>
<?php
	$go_delete = 0;
	foreach ( $prodids as $id ) {
		$prod = get_proddata( $id );
		if ( $id == $current_prod->ID ) {
			echo "<li>" . sprintf(__('ID #%1$s: %2$s <strong>The current prod will not be deleted.</strong>'), $id, $prod->prod_login) . "</li>\n";
		} else {
			echo "<li><input type=\"hidden\" name=\"prods[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('ID #%1$s: %2$s'), $id, $prod->prod_login) . "</li>\n";
			$go_delete++;
		}
	}
	?>
	</ul>
<?php if ( $go_delete ) :

	if ( ! $prods_have_content ) : ?>
		<input type="hidden" name="delete_option" value="delete" />
	<?php else: ?>
		<?php if ( 1 == $go_delete ) : ?>
			<fieldset><p><legend><?php _e( 'What should be done with content owned by this prod?' ); ?></legend></p>
		<?php else : ?>
			<fieldset><p><legend><?php _e( 'What should be done with content owned by these prods?' ); ?></legend></p>
		<?php endif; ?>
		<ul style="list-style:none;">
			<li><label><input type="radio" id="delete_option0" name="delete_option" value="delete" />
			<?php _e('Delete all content.'); ?></label></li>
			<li><input type="radio" id="delete_option1" name="delete_option" value="reassign" />
			<?php echo '<label for="delete_option1">' . __( 'Attribute all content to:' ) . '</label> ';
			wp_dropdown_prods( array( 'name' => 'reassign_prod', 'exclude' => array_diff( $prodids, array($current_prod->ID) ) ) ); ?></li>
		</ul></fieldset>
	<?php endif;
	/**
	 * Fires at the end of the delete prods form prior to the confirm button.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Prod $current_prod WP_Prod object for the prod being deleted.
	 */
	do_action( 'delete_prod_form', $current_prod );
	?>
	<input type="hidden" name="action" value="dodelete" />
	<?php submit_button( __('Confirm Deletion'), 'primary' ); ?>
<?php else : ?>
	<p><?php _e('There are no valid prods selected for deletion.'); ?></p>
<?php endif; ?>
</div>
</form>
<?php

break;

case 'doremove':
	check_admin_referer('remove-prods');

	if ( ! is_multisite() )
		wp_die( __( 'You can&#8217;t remove prods.' ) );

	if ( empty($_REQUEST['prods']) ) {
		wp_redirect($redirect);
		exit;
	}

	if ( ! current_prod_can( 'remove_prods' ) )
		wp_die( __( 'You can&#8217;t remove prods.' ) );

	$prodids = $_REQUEST['prods'];

	$update = 'remove';
 	foreach ( $prodids as $id ) {
		$id = (int) $id;
		if ( $id == $current_prod->ID && !is_super_admin() ) {
			$update = 'err_admin_remove';
			continue;
		}
		if ( !current_prod_can('remove_prod', $id) ) {
			$update = 'err_admin_remove';
			continue;
		}
		remove_prod_from_blog($id, $blog_id);
	}

	$redirect = add_query_arg( array('update' => $update), $redirect);
	wp_redirect($redirect);
	exit;

case 'remove':

	check_admin_referer('bulk-prods');

	if ( ! is_multisite() )
		wp_die( __( 'You can&#8217;t remove prods.' ) );

	if ( empty($_REQUEST['prods']) && empty($_REQUEST['prod']) ) {
		wp_redirect($redirect);
		exit();
	}

	if ( !current_prod_can('remove_prods') )
		$error = new WP_Error('edit_prods', __('You can&#8217;t remove prods.'));

	if ( empty($_REQUEST['prods']) )
		$prodids = array(intval($_REQUEST['prod']));
	else
		$prodids = $_REQUEST['prods'];

	include( ABSPATH . 'wp-admin/admin-header.php' );
?>
<form method="post" name="updateprods" id="updateprods">
<?php wp_nonce_field('remove-prods') ?>
<?php echo $referer; ?>

<div class="wrap">
<h1><?php _e( 'Remove Prods from Site' ); ?></h1>

<?php if ( 1 == count( $prodids ) ) : ?>
	<p><?php _e( 'You have specified this prod for removal:' ); ?></p>
<?php else : ?>
	<p><?php _e( 'You have specified these prods for removal:' ); ?></p>
<?php endif; ?>

<ul>
<?php
	$go_remove = false;
 	foreach ( $prodids as $id ) {
		$id = (int) $id;
 		$prod = get_proddata( $id );
		if ( $id == $current_prod->ID && !is_super_admin() ) {
			echo "<li>" . sprintf(__('ID #%1$s: %2$s <strong>The current prod will not be removed.</strong>'), $id, $prod->prod_login) . "</li>\n";
		} elseif ( !current_prod_can('remove_prod', $id) ) {
			echo "<li>" . sprintf(__('ID #%1$s: %2$s <strong>You don\'t have permission to remove this prod.</strong>'), $id, $prod->prod_login) . "</li>\n";
		} else {
			echo "<li><input type=\"hidden\" name=\"prods[]\" value=\"{$id}\" />" . sprintf(__('ID #%1$s: %2$s'), $id, $prod->prod_login) . "</li>\n";
			$go_remove = true;
		}
 	}
 	?>
</ul>
<?php if ( $go_remove ) : ?>
		<input type="hidden" name="action" value="doremove" />
		<?php submit_button( __('Confirm Removal'), 'primary' ); ?>
<?php else : ?>
	<p><?php _e('There are no valid prods selected for removal.'); ?></p>
<?php endif; ?>
</div>
</form>
<?php

break;

default:

	if ( !empty($_GET['_wp_http_referer']) ) {
		wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}

	$wp_list_table->prepare_items();
	$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
	if ( $pagenum > $total_pages && $total_pages > 0 ) {
		wp_redirect( add_query_arg( 'paged', $total_pages ) );
		exit;
	}

	include( ABSPATH . 'wp-admin/admin-header.php' );

	$messages = array();
	if ( isset($_GET['update']) ) :
		switch($_GET['update']) {
		case 'del':
		case 'del_many':
			$delete_count = isset($_GET['delete_count']) ? (int) $_GET['delete_count'] : 0;
			if ( 1 == $delete_count ) {
				$message = __( 'Prod deleted.' );
			} else {
				$message = _n( '%s prod deleted.', '%s prods deleted.', $delete_count );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $delete_count ) ) . '</p></div>';
			break;
		case 'add':
			if ( isset( $_GET['id'] ) && ( $prod_id = $_GET['id'] ) && current_prod_can( 'edit_prod', $prod_id ) ) {
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( __( 'New prod created. <a href="%s">Edit prod</a>' ),
					esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						self_admin_url( 'prod-edit.php?prod_id=' . $prod_id ) ) ) ) . '</p></div>';
			} else {
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'New prod created.' ) . '</p></div>';
			}
			break;
		case 'promote':
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __('Changed roles.') . '</p></div>';
			break;
		case 'err_admin_role':
			$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __('The current prod&#8217;s role must have prod editing capabilities.') . '</p></div>';
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __('Other prod roles have been changed.') . '</p></div>';
			break;
		case 'err_admin_del':
			$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __('You can&#8217;t delete the current prod.') . '</p></div>';
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __('Other prods have been deleted.') . '</p></div>';
			break;
		case 'remove':
			$messages[] = '<div id="message" class="updated notice is-dismissible fade"><p>' . __('Prod removed from this site.') . '</p></div>';
			break;
		case 'err_admin_remove':
			$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __("You can't remove the current prod.") . '</p></div>';
			$messages[] = '<div id="message" class="updated notice is-dismissible fade"><p>' . __('Other prods have been removed.') . '</p></div>';
			break;
		}
	endif; ?>

<?php if ( isset($errors) && is_wp_error( $errors ) ) : ?>
	<div class="error">
		<ul>
		<?php
			foreach ( $errors->get_error_messages() as $err )
				echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty($messages) ) {
	foreach ( $messages as $msg )
		echo $msg;
} ?>

<div class="wrap">
<h1>
<?php
echo esc_html( $title );
if ( current_prod_can( 'create_prods' ) ) { ?>
	<a href="prod-new.php" class="page-title-action"><?php echo esc_html_x( 'Add New', 'prod' ); ?></a>
<?php } elseif ( is_multisite() && current_prod_can( 'promote_prods' ) ) { ?>
	<a href="prod-new.php" class="page-title-action"><?php echo esc_html_x( 'Add Existing', 'prod' ); ?></a>
<?php }

if ( $prodsearch )
	printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html( $prodsearch ) ); ?>
</h1>

<?php $wp_list_table->views(); ?>

<form method="get">

<?php $wp_list_table->search_box( __( 'Search Prods' ), 'prod' ); ?>

<?php $wp_list_table->display(); ?>
</form>

<br class="clear" />
</div>
<?php
break;

} // end of the $doaction switch

include( ABSPATH . 'wp-admin/admin-footer.php' );
