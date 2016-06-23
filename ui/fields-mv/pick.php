<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 * @var $value           array
 * @var $id              string
 */
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );

wp_enqueue_script( 'backbone' );
wp_enqueue_script( 'marionette', PODS_URL . 'ui/js/marionette/backbone.marionette.js', array( 'backbone' ), '2.4.4', true );

wp_enqueue_script( 'backbone.babysitter', PODS_URL . 'ui/js/marionette/backbone.babysitter.min.js', array( 'backbone' ), '0.1.10', true );
wp_enqueue_script( 'backbone.radio', PODS_URL . 'ui/js/marionette/backbone.radio.min.js', array( 'backbone' ), '1.0.2', true );
wp_enqueue_script( 'marionette.radio.shim', PODS_URL . 'ui/js/marionette/marionette.radio.shim.js', array(
	'marionette',
	'backbone.radio'
), '1.0.2', true );
wp_enqueue_script( 'marionette.state', PODS_URL. 'ui/js/marionette/marionette.state.js', array( 'marionette' ), '1.0.1', true );
wp_enqueue_script( 'pods-fields-ready', PODS_URL . 'ui/fields-mv/js/pods-fields-ready.min.js', array(), PODS_VERSION, true );

$data = (array) pods_v( 'data', $options, array(), null, true );
unset ( $options[ 'data' ] );
$options[ 'item_id' ] = (int) $id;

$model_data = array();
foreach ( $data as $this_id => $this_title ) {
	$model_data[] = array(
		'id'       => $this_id,
		'name'     => $this_title,
		'selected' => ( null !== $value[ $this_id ] )
	);
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );
$attributes = array_map( 'esc_attr', $attributes );
$field_meta = array(
	'field_attributes' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name_clean' => $attributes[ 'data-name-clean' ]
	),
	'field_options'    => $options
);

// Set the file name and args based on the content type of the relationship
switch ( $options[ 'pick_object' ] ) {
	case 'post_type':
		$file_name = 'post-new.php';
		$query_args = array(
			'post_type' => $options[ 'pick_val' ],
		);
		break;

	case 'taxonomy':
		$file_name = 'edit-tags.php';
		$query_args = array(
			'taxonomy' => $options[ 'pick_val' ],
		);
		break;

	case 'user':
		$file_name = 'user-new.php';
		$query_args = array();
		break;

	case 'pod':
		$file_name = 'admin.php';
		$query_args = array(
			'page'   => 'pods-manage-' . $options[ 'pick_val' ],
			'action' => 'add'
		);
		break;

	// Something unsupported
	default:
		// What to do here?
		$file_name = '';
		$query_args = array();
		break;
}

// Add args we always need
$query_args = array_merge(
	$query_args,
	array(
		'pods_modal' => '1', // @todo: Replace string literal with defined constant
	)
);

$field_meta[ 'field_options' ][ 'iframe_src' ] = add_query_arg( $query_args, admin_url( $file_name ) );

// Assemble the URL
$url = add_query_arg( $query_args, admin_url( $file_name ) );

include_once PODS_DIR . 'classes/PodsFieldData.php';
$field_data = new PodsUIFieldData( $field_type, array( 'model_data' => $model_data, 'field_meta' => $field_meta ) );
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ], 'id' => $attributes[ 'id' ] ), $name, $form_field_type, $options ); ?>>
	<?php if ( ! empty( $file_name ) ) { ?>
		<?php $field_data->emit_script(); ?>
	<?php } else { ?>
		<p>This related object does not support Flexible Relationships.</p>
	<?php } ?>
</div>
