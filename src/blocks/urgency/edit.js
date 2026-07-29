import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	TextControl,
	ToggleControl,
	BaseControl,
} from '@wordpress/components';
import WooPlaceholder from '../../utils/woo-placeholder';

export default function Edit( { attributes, setAttributes } ) {
	const {
		type,
		stockThreshold,
		stockMessage,
		showIcon,
		countdownSource,
		customDate,
		countdownLabel,
		hideWhenExpired,
		expiredMessage,
		boxed,
		boxedWidth,
	} = attributes;

	const instructions =
		type === 'stock'
			? __(
					'Shows a low-stock warning based on the live stock level once it drops to or below the threshold. Preview appears on the product page.',
					'noorpdp'
			  )
			: __(
					'Counts down to the sale end date or a custom deadline. Preview appears on the product page.',
					'noorpdp'
			  );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Urgency type', 'noorpdp' ) }>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Type', 'noorpdp' ) }
						value={ type }
						options={ [
							{
								label: __( 'Low stock warning', 'noorpdp' ),
								value: 'stock',
							},
							{
								label: __( 'Countdown timer', 'noorpdp' ),
								value: 'countdown',
							},
						] }
						onChange={ ( value ) => setAttributes( { type: value } ) }
					/>
				</PanelBody>

				{ type === 'stock' && (
					<PanelBody title={ __( 'Low stock settings', 'noorpdp' ) }>
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __(
								'Show when stock is at or below',
								'noorpdp'
							) }
							value={ stockThreshold }
							onChange={ ( value ) =>
								setAttributes( { stockThreshold: value || 1 } )
							}
							min={ 1 }
							max={ 50 }
						/>
						<TextControl
							__nextHasNoMarginBottom
							label={ __( 'Message', 'noorpdp' ) }
							help={ __(
								'Use {stock} where the remaining quantity should appear.',
								'noorpdp'
							) }
							value={ stockMessage }
							onChange={ ( value ) =>
								setAttributes( { stockMessage: value } )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Show icon', 'noorpdp' ) }
							checked={ showIcon }
							onChange={ ( value ) =>
								setAttributes( { showIcon: value } )
							}
						/>
					</PanelBody>
				) }

				{ type === 'countdown' && (
					<PanelBody title={ __( 'Countdown settings', 'noorpdp' ) }>
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Count down to', 'noorpdp' ) }
							value={ countdownSource }
							options={ [
								{
									label: __(
										"Product's scheduled sale end date",
										'noorpdp'
									),
									value: 'sale',
								},
								{
									label: __( 'Custom date & time', 'noorpdp' ),
									value: 'custom',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { countdownSource: value } )
							}
						/>
						{ countdownSource === 'sale' && (
							<p className="components-base-control__help">
								{ __(
									'Only shows while the product has an active sale with a scheduled end date set in Product data → General.',
									'noorpdp'
								) }
							</p>
						) }
						{ countdownSource === 'custom' && (
							<BaseControl
								__nextHasNoMarginBottom
								label={ __( 'Deadline', 'noorpdp' ) }
								help={ __( "Uses your site's timezone.", 'noorpdp' ) }
							>
								<input
									type="datetime-local"
									className="components-text-control__input"
									value={ customDate }
									onChange={ ( event ) =>
										setAttributes( { customDate: event.target.value } )
									}
								/>
							</BaseControl>
						) }
						<TextControl
							__nextHasNoMarginBottom
							label={ __( 'Label', 'noorpdp' ) }
							value={ countdownLabel }
							onChange={ ( value ) =>
								setAttributes( { countdownLabel: value } )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide once expired', 'noorpdp' ) }
							checked={ hideWhenExpired }
							onChange={ ( value ) =>
								setAttributes( { hideWhenExpired: value } )
							}
							help={
								hideWhenExpired
									? __(
											'The block disappears once the deadline passes.',
											'noorpdp'
									  )
									: __(
											'Shows the message below once the deadline passes.',
											'noorpdp'
									  )
							}
						/>
						{ ! hideWhenExpired && (
							<TextControl
								__nextHasNoMarginBottom
								label={ __( 'Expired message', 'noorpdp' ) }
								value={ expiredMessage }
								onChange={ ( value ) =>
									setAttributes( { expiredMessage: value } )
								}
							/>
						) }
					</PanelBody>
				) }

				<PanelBody title={ __( 'Layout', 'noorpdp' ) } initialOpen={ false }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Boxed width', 'noorpdp' ) }
						checked={ boxed }
						onChange={ ( value ) => setAttributes( { boxed: value } ) }
						help={
							boxed
								? __(
										'Constrained to a max width and centered.',
										'noorpdp'
								  )
								: __(
										'Stretches the full width of its container.',
										'noorpdp'
								  )
						}
					/>
					{ boxed && (
						<RangeControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Max width (px)', 'noorpdp' ) }
							value={ boxedWidth }
							onChange={ ( value ) =>
								setAttributes( { boxedWidth: value } )
							}
							min={ 320 }
							max={ 1800 }
							step={ 10 }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div
				{ ...useBlockProps( {
					className: boxed ? 'is-boxed' : undefined,
					style: boxed ? { maxWidth: boxedWidth } : undefined,
				} ) }
			>
				<WooPlaceholder
					icon="warning"
					label={ __( 'Urgency & Countdown', 'noorpdp' ) }
					instructions={ instructions }
				/>
			</div>
		</>
	);
}
