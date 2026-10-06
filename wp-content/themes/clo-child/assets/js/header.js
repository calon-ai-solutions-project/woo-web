/**
 * Header behaviour: mobile menu toggle and sub-menu toggles.
 * Without JavaScript the menu and its sub-menus are simply shown open.
 */
( function () {
	var header = document.getElementById( 'masthead' );
	if ( ! header ) {
		return;
	}
	var nav = header.querySelector( '.clo-nav' );
	var toggle = header.querySelector( '.clo-menu-toggle' );
	header.classList.add( 'has-js' );

	if ( nav && toggle ) {
		toggle.addEventListener( 'click', function () {
			var open = toggle.getAttribute( 'aria-expanded' ) !== 'true';
			toggle.setAttribute( 'aria-expanded', String( open ) );
			nav.classList.toggle( 'is-open', open );
		} );
	}

	// Give each parent item a button that opens its sub-menu (used on touch and small screens).
	var parents = header.querySelectorAll( '.clo-menu > .menu-item-has-children' );
	Array.prototype.forEach.call( parents, function ( item, index ) {
		var link = item.querySelector( 'a' );
		var sub = item.querySelector( '.sub-menu' );
		if ( ! link || ! sub ) {
			return;
		}
		sub.id = sub.id || 'clo-sub-menu-' + index;
		var button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'clo-sub-toggle';
		button.setAttribute( 'aria-expanded', 'false' );
		button.setAttribute( 'aria-controls', sub.id );
		button.innerHTML =
			'<span class="screen-reader-text">Open the ' + link.textContent.trim() + ' menu</span>' +
			'<svg class="clo-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6"/></svg>';
		link.insertAdjacentElement( 'afterend', button );

		button.addEventListener( 'click', function () {
			var open = button.getAttribute( 'aria-expanded' ) !== 'true';
			button.setAttribute( 'aria-expanded', String( open ) );
			item.classList.toggle( 'is-open', open );
		} );
	} );

	// Escape closes an open sub-menu and returns focus to its button.
	header.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' ) {
			return;
		}
		var openItem = header.querySelector( '.clo-menu > .is-open' );
		if ( openItem ) {
			var button = openItem.querySelector( '.clo-sub-toggle' );
			openItem.classList.remove( 'is-open' );
			if ( button ) {
				button.setAttribute( 'aria-expanded', 'false' );
				button.focus();
			}
		}
	} );
}() );
