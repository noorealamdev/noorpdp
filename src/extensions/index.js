import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	RangeControl,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import { VISIBILITY_DEFAULT, ANIMATION_DEFAULT, isExtendable } from './shared';

/**
 * Adds the visibility/animation attributes to every NoorPDP block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Filtered settings.
 */
function addExtensionAttributes( settings, name ) {
	if ( ! isExtendable( name ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			noorpdpVisibility: {
				type: 'object',
				default: VISIBILITY_DEFAULT,
			},
			noorpdpAnimation: {
				type: 'object',
				default: ANIMATION_DEFAULT,
			},
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'noorpdp/extensions/attributes',
	addExtensionAttributes
);

/**
 * Appends a "Visibility" and "Animation" Inspector panel to every
 * NoorPDP block, after its own controls.
 */
const withExtensionControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( ! isExtendable( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const visibility = {
			...VISIBILITY_DEFAULT,
			...attributes.noorpdpVisibility,
		};
		const animation = {
			...ANIMATION_DEFAULT,
			...attributes.noorpdpAnimation,
		};

		const updateVisibility = ( key, value ) =>
			setAttributes( {
				noorpdpVisibility: { ...visibility, [ key ]: value },
			} );

		const updateAnimation = ( key, value ) =>
			setAttributes( {
				noorpdpAnimation: { ...animation, [ key ]: value },
			} );

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Visibility', 'noorpdp' ) }
						initialOpen={ false }
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide on desktop', 'noorpdp' ) }
							checked={ visibility.hideOnDesktop }
							onChange={ ( value ) =>
								updateVisibility( 'hideOnDesktop', value )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide on tablet', 'noorpdp' ) }
							checked={ visibility.hideOnTablet }
							onChange={ ( value ) =>
								updateVisibility( 'hideOnTablet', value )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide on mobile', 'noorpdp' ) }
							checked={ visibility.hideOnMobile }
							onChange={ ( value ) =>
								updateVisibility( 'hideOnMobile', value )
							}
						/>
					</PanelBody>
					<PanelBody
						title={ __( 'Animation', 'noorpdp' ) }
						initialOpen={ false }
					>
						<SelectControl
							__nextHasNoMarginBottom
							label={ __(
								'Entrance animation',
								'noorpdp'
							) }
							value={ animation.type }
							options={ [
								{
									label: __( 'None', 'noorpdp' ),
									value: 'none',
								},
								{
									label: __( 'Fade in', 'noorpdp' ),
									value: 'fade-in',
								},
								{
									label: __( 'Slide up', 'noorpdp' ),
									value: 'slide-up',
								},
								{
									label: __( 'Zoom in', 'noorpdp' ),
									value: 'zoom-in',
								},
							] }
							onChange={ ( value ) =>
								updateAnimation( 'type', value )
							}
						/>
						{ animation.type !== 'none' && (
							<>
								<RangeControl
									__nextHasNoMarginBottom
									label={ __(
										'Duration (ms)',
										'noorpdp'
									) }
									min={ 100 }
									max={ 3000 }
									step={ 50 }
									value={ animation.duration }
									onChange={ ( value ) =>
										updateAnimation( 'duration', value )
									}
								/>
								<RangeControl
									__nextHasNoMarginBottom
									label={ __(
										'Delay (ms)',
										'noorpdp'
									) }
									min={ 0 }
									max={ 3000 }
									step={ 50 }
									value={ animation.delay }
									onChange={ ( value ) =>
										updateAnimation( 'delay', value )
									}
								/>
							</>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	},
	'withNoorPDPExtensionControls'
);

addFilter(
	'editor.BlockEdit',
	'noorpdp/extensions/inspector-controls',
	withExtensionControls
);

/**
 * Writes the resulting visibility classes and animation data attributes
 * onto the saved markup of static (save.js) NoorPDP blocks.
 *
 * @param {Object} extraProps Block save element props.
 * @param {Object} blockType  Block type object.
 * @param {Object} attributes Block attributes.
 * @return {Object} Filtered props.
 */
function addExtensionSaveProps( extraProps, blockType, attributes ) {
	if ( ! isExtendable( blockType.name ) ) {
		return extraProps;
	}

	const visibility = {
		...VISIBILITY_DEFAULT,
		...attributes.noorpdpVisibility,
	};
	const animation = {
		...ANIMATION_DEFAULT,
		...attributes.noorpdpAnimation,
	};

	const classNames = [ extraProps.className ];

	if ( visibility.hideOnMobile ) {
		classNames.push( 'noorpdp-hide-mobile' );
	}

	if ( visibility.hideOnTablet ) {
		classNames.push( 'noorpdp-hide-tablet' );
	}

	if ( visibility.hideOnDesktop ) {
		classNames.push( 'noorpdp-hide-desktop' );
	}

	const nextProps = {
		...extraProps,
		className: classNames.filter( Boolean ).join( ' ' ) || undefined,
	};

	if ( animation.type && 'none' !== animation.type ) {
		nextProps[ 'data-noorpdp-animation' ] = animation.type;
		nextProps[ 'data-noorpdp-duration' ] = animation.duration;
		nextProps[ 'data-noorpdp-delay' ] = animation.delay;
	}

	return nextProps;
}

addFilter(
	'blocks.getSaveContent.extraProps',
	'noorpdp/extensions/save-props',
	addExtensionSaveProps
);
