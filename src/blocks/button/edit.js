import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	BlockControls,
	AlignmentControl,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { text, url, opensInNewTab, textAlign } = attributes;

	// The link itself carries every style support (color, typography,
	// spacing, border, shadow) since it's the actual visible button box;
	// the wrapper below only ever handles the button's own alignment.
	const blockProps = useBlockProps( {
		className: 'noorpdp-button__link',
	} );

	return (
		<>
			<BlockControls>
				<AlignmentControl
					value={ textAlign }
					onChange={ ( value ) =>
						setAttributes( { textAlign: value } )
					}
				/>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Link Settings', 'noorpdp' ) }>
					<TextControl
						label={ __( 'URL', 'noorpdp' ) }
						value={ url }
						onChange={ ( value ) =>
							setAttributes( { url: value } )
						}
						placeholder="https://"
					/>
					<ToggleControl
						label={ __( 'Open in new tab', 'noorpdp' ) }
						checked={ opensInNewTab }
						onChange={ ( value ) =>
							setAttributes( { opensInNewTab: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div
				className={
					textAlign
						? `wp-block-noorpdp-button has-text-align-${ textAlign }`
						: 'wp-block-noorpdp-button'
				}
			>
				<RichText
					tagName="a"
					{ ...blockProps }
					value={ text }
					onChange={ ( value ) => setAttributes( { text: value } ) }
					placeholder={ __( 'Add text…', 'noorpdp' ) }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>
			</div>
		</>
	);
}
