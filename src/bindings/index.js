import { registerBlockBindingsSource } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';

/**
 * Must match `Blocks\Bindings::SOURCE` — the label and `usesContext` this
 * source actually renders with come from that server-side registration;
 * this client-side half only adds the editor-only pieces (the "Connect to
 * custom field" picker and the canvas preview value), which WordPress
 * doesn't support registering from PHP.
 */
const SOURCE_NAME = 'noorpdp/product-field';

// Not every WordPress version this plugin supports (6.6+) ships the Block
// Bindings UI (it landed in 6.7) — skip registration entirely rather than
// render a picker core can't display.
if ( typeof registerBlockBindingsSource === 'function' ) {
	/*
	 * The field list (names, labels, and the stand-in product's current
	 * values) lives on the server — see Rest\Product_Fields_Controller.
	 * Both getFieldsList (called whenever the picker opens) and getValues
	 * (called whenever a bound block renders in the canvas) need it, so
	 * it's fetched once per editor session and reused.
	 */
	let fieldsRequest;
	const getFields = () => {
		if ( ! fieldsRequest ) {
			fieldsRequest = apiFetch( {
				path: '/noorpdp/v1/product-fields',
			} ).catch( () => ( {} ) );
		}
		return fieldsRequest;
	};

	registerBlockBindingsSource( {
		name: SOURCE_NAME,
		usesContext: [ 'postType' ],

		async getFieldsList() {
			const fields = await getFields();

			return Object.fromEntries(
				Object.entries( fields ).map( ( [ key, field ] ) => [
					key,
					{ label: field.label, value: field.value },
				] )
			);
		},

		async getValues( { bindings } ) {
			const fields = await getFields();
			const values = {};

			Object.entries( bindings || {} ).forEach(
				( [ attributeName, binding ] ) => {
					const key = binding?.args?.key;
					values[ attributeName ] =
						key && fields[ key ] ? fields[ key ].value : '';
				}
			);

			return values;
		},

		// Read-only: the underlying product field is edited on the
		// product itself (or in ACF), never from inside a Layout.
		canUserEditValue: () => false,
	} );
}
