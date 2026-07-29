<?php
/**
 * Tabs block server render.
 *
 * Builds the tab list from the inner tab blocks' attributes and wraps the
 * pre-rendered panels ($content) with the Interactivity API context.
 *
 * @package NoorPDP
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (the panels).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$noorpdp_tabs = array();

foreach ( $block->parsed_block['innerBlocks'] as $noorpdp_inner ) {
	$noorpdp_tabs[] = array(
		'uid'   => isset( $noorpdp_inner['attrs']['uid'] ) ? (string) $noorpdp_inner['attrs']['uid'] : '',
		'title' => isset( $noorpdp_inner['attrs']['title'] ) ? (string) $noorpdp_inner['attrs']['title'] : '',
	);
}

if ( empty( $noorpdp_tabs ) ) {
	return;
}

$noorpdp_context = array(
	'active' => array( 'id' => $noorpdp_tabs[0]['uid'] ),
);

// The initial state is rendered server-side (first tab active); the
// Interactivity API directives keep it in sync after hydration.
$noorpdp_panels = new WP_HTML_Tag_Processor( $content );
$noorpdp_index  = 0;

while ( $noorpdp_panels->next_tag( array( 'class_name' => 'noorpdp-tabs__panel' ) ) ) {
	if ( 0 !== $noorpdp_index ) {
		$noorpdp_panels->set_attribute( 'hidden', true );
	}
	++$noorpdp_index;
}

$noorpdp_content = $noorpdp_panels->get_updated_html();

// Self-contained max-width instead of relying on the theme/template to
// constrain block content. Applied to this block's own wrapper — save.js
// stores only the panel inner blocks with no wrapper of its own, so
// changing what render.php builds here carries no validation/deprecation
// risk at all.
$noorpdp_boxed        = ! isset( $attributes['boxed'] ) || (bool) $attributes['boxed'];
$noorpdp_boxed_width  = isset( $attributes['boxedWidth'] ) ? absint( $attributes['boxedWidth'] ) : 1200;
$noorpdp_wrapper_args = array();

if ( $noorpdp_boxed ) {
	$noorpdp_wrapper_args['class'] = 'is-boxed';
	$noorpdp_wrapper_args['style'] = 'max-width:' . $noorpdp_boxed_width . 'px';
}
?>
<div
	<?php echo get_block_wrapper_attributes( $noorpdp_wrapper_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>
	data-wp-interactive="noorpdp/tabs"
	data-wp-context="<?php echo esc_attr( (string) wp_json_encode( $noorpdp_context ) ); ?>"
>
	<div
		class="noorpdp-tabs__list"
		role="tablist"
		data-wp-on--keydown="actions.handleKeydown"
	>
		<?php foreach ( $noorpdp_tabs as $noorpdp_i => $noorpdp_tab ) : ?>
			<button
				type="button"
				id="noorpdp-tab-<?php echo esc_attr( $noorpdp_tab['uid'] ); ?>"
				class="noorpdp-tabs__tab<?php echo 0 === $noorpdp_i ? ' is-active' : ''; ?>"
				role="tab"
				aria-selected="<?php echo 0 === $noorpdp_i ? 'true' : 'false'; ?>"
				tabindex="<?php echo 0 === $noorpdp_i ? '0' : '-1'; ?>"
				aria-controls="noorpdp-tab-panel-<?php echo esc_attr( $noorpdp_tab['uid'] ); ?>"
				data-wp-context="<?php echo esc_attr( (string) wp_json_encode( array( 'tabId' => $noorpdp_tab['uid'] ) ) ); ?>"
				data-wp-on--click="actions.activate"
				data-wp-bind--aria-selected="state.isSelected"
				data-wp-bind--tabindex="state.tabIndex"
				data-wp-class--is-active="state.isSelected"
			>
				<?php echo esc_html( $noorpdp_tab['title'] ); ?>
			</button>
		<?php endforeach; ?>
	</div>
	<div class="noorpdp-tabs__panels">
		<?php echo $noorpdp_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered inner blocks. ?>
	</div>
</div>
