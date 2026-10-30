import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { title, boxed, boxedWidth } = attributes;
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<RichText.Content
				tagName="h2"
				className="noorpdp-faq__title"
				value={ title }
			/>
			<div
				className={
					'noorpdp-faq__accordion' +
					( boxed ? ' is-boxed' : '' )
				}
				style={ boxed ? { maxWidth: boxedWidth } : undefined }
			>
				<InnerBlocks.Content />
			</div>
		</div>
	);
}
