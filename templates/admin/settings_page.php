<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$echoed = '';
if ( isset( $_POST['submit_to_drop_or_not'] ) ) {
	if ( isset( $_POST['to-drop-or-not'] ) && $_POST['to-drop-or-not'] === 'on' ) {
		update_option('to-drop-or-not', 'on');
	} else {
		update_option('to-drop-or-not', 'off');
	}
}

$echoed .= '<form method="POST">';
$echoed .= '<label>Drop plugins database tables on Deactivate? </label>';
$echoed .= '<input value="on" name="to-drop-or-not" type="checkbox" ' . checked( get_option( 'to-drop-or-not' ), 'on', false ) . '>';
$echoed .= '<button type="submit" name="submit_to_drop_or_not" class="button">Save</button>';
$echoed .= '</form>';
echo $echoed;

?>