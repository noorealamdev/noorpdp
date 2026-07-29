function openReviewModal( modal ) {
	modal.hidden = false;
	modal.setAttribute( 'aria-hidden', 'false' );
	document.body.classList.add( 'noorpdp-modal-open' );

	const firstField = modal.querySelector( 'select, textarea, input:not([type="hidden"])' );

	if ( firstField ) {
		firstField.focus();
	}
}

function closeReviewModal( modal ) {
	modal.hidden = true;
	modal.setAttribute( 'aria-hidden', 'true' );
	document.body.classList.remove( 'noorpdp-modal-open' );
}

document.addEventListener( 'click', ( event ) => {
	const modalTrigger = event.target.closest( '[data-review-modal-target]' );

	if ( modalTrigger ) {
		const modal = document.getElementById(
			modalTrigger.getAttribute( 'data-review-modal-target' )
		);

		if ( modal ) {
			openReviewModal( modal );
		}

		return;
	}

	const modalCloser = event.target.closest( '[data-review-modal-close]' );

	if ( modalCloser ) {
		const modal = modalCloser.closest( '.noorpdp-product-reviews__modal' );

		if ( modal ) {
			closeReviewModal( modal );
		}

		return;
	}

	const loadMoreButton = event.target.closest(
		'.noorpdp-product-reviews__load-more'
	);

	if ( ! loadMoreButton ) {
		return;
	}

	const wrapper = loadMoreButton.closest(
		'.wp-block-noorpdp-product-reviews'
	);
	const grid = wrapper && wrapper.querySelector( '.noorpdp-product-reviews__grid' );

	if ( ! grid ) {
		return;
	}

	const restUrl = loadMoreButton.getAttribute( 'data-rest-url' );
	const offset = parseInt( loadMoreButton.getAttribute( 'data-offset' ), 10 ) || 0;
	const perPage = parseInt( loadMoreButton.getAttribute( 'data-per-page' ), 10 ) || 12;
	const originalText = loadMoreButton.textContent;

	loadMoreButton.disabled = true;
	loadMoreButton.textContent = loadMoreButton.getAttribute( 'data-loading-text' ) || '…';

	const url = restUrl + '?offset=' + offset + '&per_page=' + perPage;

	fetch( url, { headers: { Accept: 'application/json' } } )
		.then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( 'Request failed' );
			}
			return response.json();
		} )
		.then( ( data ) => {
			if ( data.html ) {
				grid.insertAdjacentHTML( 'beforeend', data.html );
			}

			if ( data.hasMore ) {
				loadMoreButton.setAttribute( 'data-offset', offset + perPage );
				loadMoreButton.disabled = false;
				loadMoreButton.textContent = originalText;
			} else {
				loadMoreButton
					.closest( '.noorpdp-product-reviews__load-more-wrap' )
					.remove();
			}
		} )
		.catch( () => {
			loadMoreButton.disabled = false;
			loadMoreButton.textContent = originalText;
		} );
} );

document.addEventListener( 'keydown', ( event ) => {
	if ( 'Escape' !== event.key ) {
		return;
	}

	const openModal = document.querySelector(
		'.noorpdp-product-reviews__modal:not([hidden])'
	);

	if ( openModal ) {
		closeReviewModal( openModal );
	}
} );
