import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	useSettings,
	RichText,
	InspectorControls,
	BlockControls,
	AlignmentControl,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	Button,
	BaseControl,
	FontSizePicker,
	RangeControl,
	ColorPalette,
	ToggleControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import {
	getBackgroundStyle,
	hasBackgroundVideo,
	hasOverlay,
} from './background';

const ACTIONS_TEMPLATE = [ [ 'noorpdp/button' ] ];

export default function Edit( { attributes, setAttributes } ) {
	const {
		heading,
		subheading,
		backgroundType,
		backgroundImage,
		backgroundVideo,
		overlayColor,
		overlayOpacity,
		textAlign,
		headingFontSize,
		subheadingFontSize,
		boxed,
		boxedWidth,
	} = attributes;
	const [ fontSizes ] = useSettings( 'typography.fontSizes' );
	const [ colors = [] ] = useSettings( 'color.palette' );

	const blockProps = useBlockProps( {
		className: textAlign ? `has-text-align-${ textAlign }` : undefined,
		style: getBackgroundStyle(
			backgroundType,
			backgroundImage,
			backgroundVideo,
			overlayOpacity
		),
	} );
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'noorpdp-hero__actions' },
		{
			allowedBlocks: [ 'noorpdp/button', 'core/buttons' ],
			template: ACTIONS_TEMPLATE,
			templateInsertUpdatesSelection: false,
		}
	);

	const onSelectImage = ( media ) =>
		setAttributes( {
			backgroundImage: { url: media.url, id: media.id },
		} );

	const onRemoveImage = () =>
		setAttributes( { backgroundImage: { url: '', id: 0 } } );

	const onSelectVideo = ( media ) =>
		setAttributes( {
			backgroundVideo: { url: media.url, id: media.id },
		} );

	const onRemoveVideo = () =>
		setAttributes( { backgroundVideo: { url: '', id: 0 } } );

	const showVideo = hasBackgroundVideo( backgroundType, backgroundVideo );
	const showOverlay = hasOverlay( overlayOpacity );

	return (
		<>
			<BlockControls>
				<AlignmentControl
					value={ textAlign }
					onChange={ ( value ) =>
						setAttributes( { textAlign: value || 'center' } )
					}
				/>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Background', 'noorpdp' ) }>
					<ToggleGroupControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Type', 'noorpdp' ) }
						value={ backgroundType }
						onChange={ ( value ) =>
							setAttributes( { backgroundType: value } )
						}
					>
						<ToggleGroupControlOption
							value="image"
							label={ __( 'Image', 'noorpdp' ) }
						/>
						<ToggleGroupControlOption
							value="video"
							label={ __( 'Video', 'noorpdp' ) }
						/>
					</ToggleGroupControl>

					{ 'video' === backgroundType ? (
						<>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ onSelectVideo }
									allowedTypes={ [ 'video' ] }
									value={ backgroundVideo?.id }
									render={ ( { open } ) => (
										<Button
											variant="secondary"
											onClick={ open }
											className="noorpdp-hero__select-image"
										>
											{ backgroundVideo?.url
												? __(
														'Replace video',
														'noorpdp'
												  )
												: __(
														'Select video',
														'noorpdp'
												  ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ backgroundVideo?.url && (
								<Button
									variant="link"
									isDestructive
									onClick={ onRemoveVideo }
								>
									{ __( 'Remove video', 'noorpdp' ) }
								</Button>
							) }
						</>
					) : (
						<>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ onSelectImage }
									allowedTypes={ [ 'image' ] }
									value={ backgroundImage?.id }
									render={ ( { open } ) => (
										<Button
											variant="secondary"
											onClick={ open }
											className="noorpdp-hero__select-image"
										>
											{ backgroundImage?.url
												? __(
														'Replace image',
														'noorpdp'
												  )
												: __(
														'Select image',
														'noorpdp'
												  ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							{ backgroundImage?.url && (
								<Button
									variant="link"
									isDestructive
									onClick={ onRemoveImage }
								>
									{ __( 'Remove image', 'noorpdp' ) }
								</Button>
							) }
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Overlay', 'noorpdp' ) }>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Opacity', 'noorpdp' ) }
						help={ __(
							'Darkens the background so text stays readable.',
							'noorpdp'
						) }
						value={ overlayOpacity }
						onChange={ ( value ) =>
							setAttributes( { overlayOpacity: value ?? 0 } )
						}
						min={ 0 }
						max={ 100 }
					/>
					{ showOverlay && (
						<BaseControl
							__nextHasNoMarginBottom
							label={ __( 'Color', 'noorpdp' ) }
						>
							<ColorPalette
								colors={ colors }
								value={ overlayColor }
								onChange={ ( value ) =>
									setAttributes( {
										overlayColor: value || '#000000',
									} )
								}
							/>
						</BaseControl>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Typography', 'noorpdp' ) }>
					<BaseControl
						__nextHasNoMarginBottom
						label={ __( 'Heading', 'noorpdp' ) }
					>
						<FontSizePicker
							__nextHasNoMarginBottom
							value={ headingFontSize }
							fontSizes={ fontSizes }
							onChange={ ( value ) =>
								setAttributes( {
									headingFontSize: value || '',
								} )
							}
						/>
					</BaseControl>
					<BaseControl
						__nextHasNoMarginBottom
						label={ __( 'Subheading', 'noorpdp' ) }
					>
						<FontSizePicker
							__nextHasNoMarginBottom
							value={ subheadingFontSize }
							fontSizes={ fontSizes }
							onChange={ ( value ) =>
								setAttributes( {
									subheadingFontSize: value || '',
								} )
							}
						/>
					</BaseControl>
				</PanelBody>

				<PanelBody title={ __( 'Layout', 'noorpdp' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Boxed width', 'noorpdp' ) }
						checked={ boxed }
						onChange={ ( value ) => setAttributes( { boxed: value } ) }
						help={
							boxed
								? __(
										'Heading, subheading and buttons are constrained to a max width and centered.',
										'noorpdp'
								  )
								: __(
										'Heading, subheading and buttons stretch the full width of the hero.',
										'noorpdp'
								  )
						}
					/>
					{ boxed && (
						<RangeControl
							label={ __( 'Max width (px)', 'noorpdp' ) }
							value={ boxedWidth }
							onChange={ ( value ) =>
								setAttributes( { boxedWidth: value } )
							}
							min={ 480 }
							max={ 1800 }
							step={ 10 }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ showVideo && (
					<video
						className="noorpdp-hero__bg-video"
						src={ backgroundVideo.url }
						autoPlay
						muted
						loop
						playsInline
					/>
				) }
				{ showOverlay && (
					<div
						className="noorpdp-hero__overlay"
						style={ {
							backgroundColor: overlayColor,
							opacity: overlayOpacity / 100,
						} }
					/>
				) }
				<div
					className={
						'noorpdp-hero__content' +
						( boxed ? ' is-boxed' : '' )
					}
					style={ boxed ? { maxWidth: boxedWidth } : undefined }
				>
					<RichText
						tagName="h1"
						className="noorpdp-hero__heading"
						style={
							headingFontSize
								? { fontSize: headingFontSize }
								: undefined
						}
						value={ heading }
						onChange={ ( value ) =>
							setAttributes( { heading: value } )
						}
						placeholder={ __( 'Your Hero Heading', 'noorpdp' ) }
					/>
					<RichText
						tagName="p"
						className="noorpdp-hero__subheading"
						style={
							subheadingFontSize
								? { fontSize: subheadingFontSize }
								: undefined
						}
						value={ subheading }
						onChange={ ( value ) =>
							setAttributes( { subheading: value } )
						}
						placeholder={ __(
							'Add a supporting subheading…',
							'noorpdp'
						) }
					/>
					<div { ...innerBlocksProps } />
				</div>
			</div>
		</>
	);
}
