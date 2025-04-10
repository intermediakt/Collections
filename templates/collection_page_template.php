<?php
//!is_user_logged_in() 
if ( ! defined( 'ABSPATH' )) {
	exit;
}


/**
 * Template variables:
 *
 * @var $default_collection_id		int default collection id diff for each user
 * @var $user_collections			array of users collections (only collection names)
 * @var $active_menu_option			int within the collections page, index of which content to render
 * @var $action_response			string used to notify a user of the actions result
 * @var $colection_id				int selected collection id
 * 
**/

$default_collection_id = $coldb->find_default_users_collection_id();
$user_collections = $coldb->list_collections();


$active_menu_option;
$action_response = '';
$collection_id;



if ( isset($_POST[ 'menu-item-option' ] ) ) {
		$active_menu_option = intval( $_POST[ 'menu-item-option' ] );
		update_user_meta( get_current_user_id(), 'active-menu-option', $active_menu_option);
} else {
		$saved_meta = get_user_meta( get_current_user_id(), 'active-menu-option', true );

		if ($saved_meta !== '') {
			$active_menu_option = intval( $saved_meta );
		} else {
			$active_menu_option = 3;
		}
}

if ( isset( $_POST[ 'select-collection-id' ] ) ){
	$collection_id = intval( $_POST[ 'select-collection-id' ] );
	update_user_meta( get_current_user_id(), 'active-collection', $collection_id );
}else{
	$saved_meta = get_user_meta( get_current_user_id(), 'active-collection', true );
	if( $saved_meta !== '' ){
		$collection_id = intval( $saved_meta );
	} else{
		$collection_id = $default_collection_id;
	}
}


if( isset( $_POST['new-collection'] ) ){
	$new_collection_name = esc_html( $_POST['new-collection'] );
	$result = $coldb->insert_name( $new_collection_name );
	$action_response = $result ? '<h2>Η συλλογή δημιουργήθηκε επιτυχώς </h2>' : '<h2> Κάτι πήγε στραβά, η συλλογή δεν δημιουργήθηκε</h2>';
}

if ( isset( $_POST['remove-collection-id'] ) ) {
	$remove_id = intval($_POST['remove-collection-id']);
	if ($remove_id !== $default_collection_id) {
		$remove_result = $coldb->remove_name($remove_id);
		$action_response = $remove_result ? '<h2>Η συλλογή διαγράφηκε επιτυχώς</h2>' : '<h2>Κάτι πήγε στραβά, η συλλογή δεν διαγράφηκε</h2>';
	} else {
		$action_response =  '<h2>Δεν μπορείτε να διαγράψετε την κύρια συλλογή</h2>';
	}
}

if (isset($_POST['update-collection-id'] ) && isset( $_POST['updated-collection-name'] ) ) {
	$update_id = intval($_POST['update-collection-id']);
	$new_name = sanitize_text_field($_POST['updated-collection-name']);
	
	if ($update_id !== $default_collection_id && !empty($new_name)) {
		$update_result = $coldb->update_name($update_id, $new_name);
		$action_response = $update_result ? '<h2>Η συλλογή ενημερώθηκε επιτυχώς</h2>' : '<h2>Κάτι πήγε στραβά, η συλλογή δεν ενημερώθηκε</h2>';
	} else {
		$action_response = '<h2>Δεν μπορείτε να ενημερώσετε την κύρια συλλογή ή το όνομα είναι άδειο</h2>';
	}
}

if( isset( $_POST[ 'add-to' ] ) && isset( $_POST[ 'insert-book' ] ) ){
	$collection_name_id = intval( $_POST[ 'add-to' ] );
	$book_id = intval( $_POST[ 'insert-book' ] );
	$result = $coldb->insert( $book_id, $collection_name_id );
	$action_response = $result ? '<h2> Το βιβλίο προστέθηκε </h2>' : '<h2> Το βιβλίο υπάρχει ήδη στην συλλογή </h2>';
}

if( isset( $_POST[ 'remove-book' ] ) ){
	$entry_id = intval( $_POST[ 'remove-book' ]);
	$result = $coldb->remove( $entry_id );
	$action_response = $result ? '<h2> Το βιβλίο αφαιρέθηκε </h2>' : '<h2> Κάτι πήγε στραβά, το βιβλίο δέν αφαιρέθηκε </h2>';
} 


get_header();
?>


<style>

.library{
	min-height: 100vh; 
	width: 100vw;
	position: relative;
	display: flex;
	flex-direction: column;
	align-items: center; 
	padding: 20px 0 20px 0; 
	gap: 20px; 
	overflow: auto; 
}
	
.library-menu{
	margin-top: 50px;
}

.collection-form{
	display: flex;
	flex-direction: row;
	gap: 20px;
}

.library select, 
.library button,
.library input{
	max-height: 50px;
	height: 50px;
	text-wrap: nowrap;
}
	
	
.library-content-child{
 	align-items: center;
	padding: 20px;
	gap: 20px;
}

.collection-select{
	padding-bottom: 40px;
}

.bookshelf-entry{
	display: flex;
	padding-bottom: 50px;
	gap: 20px;
}
	
.bookshelf-entry a{
	width: 300px;
	height: 50px;
	max-height: 100px;
	white-space: normal;
	text-overflow: ellipsis;
	overflow: hidden;
	word-wrap: wrap;/**break-word;**/
	display: inline-block;
}
	
.bookshelf-entry a:focus{
	max-height: 100px;
	white-space: normal;
	overflow: visible;
	text-overflow: unset;
}

.bookshelf-entry a:hover{
	max-height: 100px;
	white-space: normal;
	overflow: visible;
	text-overflow: unset;
}

.collection-action-message{
	display: flex;
	padding: 20px 0 20px 0;
	justify-content: center;
}
	
.selected-collection-display-name{
	margin-top: 10px;
	margin-bottom: 40px;
	font-weight: normal !important;

}

.collection-text{
	font-family: "Roboto", Sans-serif; 
	font-size: 17px; 
	font-weight: 400;
}
	
hr {
	width: 70%;
	height: 1px;
	background-color: #adb5bd;
	margin-top: 3px;
}
	
</style>

<div class="library collection-text">
	<div class="library-menu">
		<div class="library-menu-content">
			<form class="collection-form" method="POST">
				<div class="library-menu-item">
					<button tabindex=0 type="submit" name="menu-item-option" value=3 class="library-menu-button"> Επιλογή συλλογής</button>
				</div>
				<div class="library-menu-item">
					<button tabindex=1 type="submit" name="menu-item-option" value=0 class="library-menu-button">Δημιουργία νέας συλλογής</button>
				</div>
				<div class="library-menu-item">
					<button tabindex=2 type="submit" name="menu-item-option" value=1 class="library-menu-button">Μετονομασία συλλογής</button>	
				</div>
				<div class="library-menu-item">
					<button tabindex=3 type="submit" name="menu-item-option" value=2 class="library-menu-button">Διαγραφή συλλογής</button>
				</div>
			</form>
		</div>
	</div>
	<div class="library-content"><?php
		if ( $action_response ){?>
			<div class="collection-action-message"> 
				<?= $action_response ?>
			</div> <?php
		}
		if( $active_menu_option === 0){ ?>
			<div class="library-content-child">
				<form class="collection-form" method="POST">
					<label for="new-collection"> Προσθήκη συλλογής: </label>
					<input type="text" name="new-collection" placeholder="Όνομα νέας συλλογής">
					<button type="submit" class="button"> Δημιουργία </button>
				</form>
			</div> <?php
		} elseif ($active_menu_option === 1){ ?>
			<div class="library-content-child">
				<form class="collection-form" method="POST">
					<label for="update-collection-id">Επιλέξτε συλλογή για ενημέρωση: </label>
					<select name="update-collection-id" id="update-collection-select">
						<?php
						foreach ($user_collections as $entry) {
							if ($entry->id !== $coldb->find_default_users_collection_id()) { ?>
								<option value="<?=$entry->id?>"><?=$entry->collection_name_str?></option>
							<?php }
						} ?>
					</select>
					<input type="text" name="updated-collection-name" placeholder="Νέο όνομα συλλογής">
					<button type="submit" class="button">Ενημέρωση Συλλογής</button>
				</form>
			</div> <?php
		} elseif( $active_menu_option === 2 ){ ?>
			<div class="library-content-child">
				<form class="collection-form" method="POST">
					<label for="remove-collection-select">Επιλέξτε συλλογή για διαγραφή:</label>
					<select name="remove-collection-id" id="remove-collection-select">
						<?php
						foreach ($user_collections as $entry) {
							if ( $entry->id !== $coldb->find_default_users_collection_id() ) { ?>
								<option value="<?=$entry->id?>"><?=$entry->collection_name_str?></option><?php
							}
						} ?>
					</select>
					<button type="submit" class="button">Διαγραφή Συλλογής</button>
				</form>
			</div> <?php
		} else{ ?>
			<div class="library-content-child">
				<div class="collection-select">
					<form class="collection-form" method="POST">
						<label for="select-collection-id"> Επέλεξε συλλογή: </label>
							<select tabindex=4 name="select-collection-id"><?php
								foreach ( $user_collections as $entry ) {?>
									<option value="<?=$entry->id?>"><?=$entry->collection_name_str?></option><?php
								}?> 
							</select>
							<button tabindex=5 type="submit"> Επιλογή </button>
					</form>
				</div><?php
				$collection_name = $coldb->get_name( $collection_id );
				if( !$collection_name ){
					$collection_name = DEFAULT_COLLECTION_NAME;
					$collection_id = $default_collection_id;
				}else{
					$collection_name = $collection_name[0]->collection_name_str;
				}
			
				$collection = $coldb->get_collection( $collection_id ); ?>
				<div class="selected-collection-display-name">
					<h3 style="font-weight: 200;"> Επιλεγμένη Συλλογή: <?=$collection_name?> </h3>
					<hr>
				</div><?php
				if( $collection ){
					$tab_index = 6;
					foreach( $collection as $entry ){ ?>
						<div class="bookshelf-entry">
							<form class="collection-form" method="POST">
								<div>
              					<a class="bookshelf-entry-info" arial-label="<?=$entry->book_title?>" tabindex="<?= $tab_index ?>" href="<?=$entry->permalink?>"> 
									<?=$entry->book_title?> 
								</a>
								</div>
								<input type="hidden" name="insert-book" value="<?=$entry->book_id?>">
								<label for="add-to"> Προσθήκη στην συλλογή: </label>
								<select name="add-to"> <?php
									foreach( $user_collections as $collection_entry ){ ?>
										<option value="<?=$collection_entry->id?>">
											<?=$collection_entry->collection_name_str?>
										</option> <?php
									} ?>
								</select>
								<button type="submit"> Προσθήκη </button>
							</form>
							<form class="collection-form" method="POST">
								<input type="hidden" name="remove-book" value="<?=$entry->id?>">
								<button type="submit"> Αφαίρεση απο συλλογή </button>
							</form>
						</div> <?php
						$tab_index += 1;
					}
				}
				?>
			</div><?php
		} ?>
	</div>
</div>

<?php

get_footer();

?>
