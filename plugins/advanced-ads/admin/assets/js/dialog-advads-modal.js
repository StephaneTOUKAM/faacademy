// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact -- PHPCS can't handle es5 short functions
const modal = element => {

	function FormValues() {
		this.addedNodes   = [];
		this.removedNodes = [];
	};

	let hasForm           = false,
		initialFormValues = new FormValues(),
		changedFormValues = new FormValues();

	/**
	 * Remove the pound sign from the location hash.
	 *
	 * @return {string}
	 */
	const getId = () => window.location.hash.replace( '#', '' );

	const mutationObserver = new MutationObserver( mutations => {
		for ( const mutation of mutations ) {
			for ( const removedNode of mutation.removedNodes ) {
				const nodes = document.createTreeWalker( removedNode, NodeFilter.SHOW_ELEMENT );
				while ( nodes.nextNode() ) {
					if ( nodes.currentNode.tagName === 'INPUT' || nodes.currentNode.tagName === 'SELECT' ) {
						const index = changedFormValues.addedNodes.indexOf( nodes.currentNode.name );
						if ( index > - 1 ) {
							changedFormValues.addedNodes.splice( index, 1 );
						} else {
							changedFormValues.removedNodes.push( nodes.currentNode.name );
						}
					}
				}
			}
			for ( const addedNode of mutation.addedNodes ) {
				if ( addedNode.nodeType === Node.TEXT_NODE ) {
					continue;
				}

				const nodes = document.createTreeWalker( addedNode, NodeFilter.SHOW_ELEMENT );
				while ( nodes.nextNode() ) {
					if ( nodes.currentNode.tagName === 'INPUT' || nodes.currentNode.tagName === 'SELECT' ) {
						changedFormValues.addedNodes.push( nodes.currentNode.name );
					}
				}
			}
		}
	} );

	const showModal = () => {
		element.showModal();
		changedFormValues = new FormValues();
		mutationObserver.observe( element, {childList: true, subtree: true} );
	};

	/**
	 * If the current hash matches the modal id attribute, open it.
	 */
	const showIfHashMatches = () => {
		if ( getId() === element.id ) {
			showModal();
		}
	};

	/**
	 * Check if there are inputs that have been changed and if their value is different.
	 *
	 * @param {Object} reference The initial values when the modal loaded, indexed by name attribute.
	 * @param {Object} changed The input values that were changed, indexed by name.
	 *
	 * @return {boolean}
	 */
	const hasChanged = ( reference, changed ) => {
		for ( const name in changed ) {
			if ( ! reference.hasOwnProperty( name ) || reference[name].toString() !== changed[name].toString() ) {
				return true;
			}
		}

		return false;
	};

	/**
	 * If the modal is associated with a form and any values have changed, ask for confirmation to navigate away.
	 * Returns true if the user agrees with termination, false otherwise.
	 *
	 * @return {boolean}
	 */
	const terminationNotice = () => {
		if ( ! hasForm || ! hasChanged( initialFormValues, changedFormValues ) ) {
			return true;
		}

		// ask user for confirmation.
		if ( window.confirm( window.advadstxt.confirmation ) ) {
			// if we have added or removed nodes, we need to reload the page.
			if ( changedFormValues.addedNodes.length || changedFormValues.removedNodes.length ) {
				window.location.reload();
				return true;
			}

			// otherwise, we'll replace the values with the previous values.
			for ( const name in changedFormValues ) {
				const input = element.querySelector( '[name="' + name + '"]' );
				if ( input === null ) {
					continue;
				}

				if ( input.type === 'checkbox' ) {
					input.checked = initialFormValues[name];
				} else if ( input.type === 'radio' ) {
					element.querySelector( '[name="' + name + '"][value="' + initialFormValues[name] + '"]' ).checked = true;
				} else {
					input.value = initialFormValues[name];
				}
			}

			return true;
		}

		return false;
	};

	// Check whether to open modal on page load.
	showIfHashMatches();

	/**
	 * Listen to the hashchange event, to check if the current modal needs to be opened.
	 */
	window.addEventListener( 'hashchange', () => {
		showIfHashMatches();
		if ( getId() === 'close' && terminationNotice() ) {
			element.close();
		}
	} );

	/**
	 * Attach a click listener to all links referencing this modal and prevent their default action.
	 * By changing the hash on every click, we also create a history entry.
	 */
	document.querySelectorAll( 'a[href$="#' + element.id + '"]' ).forEach( link => {
		link.addEventListener( 'click', e => {
			e.preventDefault();
			showModal();
		} );
	} );

	/**
	 * On the cancel event, check for termination notice and fire a custom event.
	 */
	element.addEventListener( 'cancel', event => {
		event.preventDefault();
		if ( terminationNotice() ) {
			element.close();

			mutationObserver.disconnect();

			document.dispatchEvent( new CustomEvent( 'advads-modal-canceled', {
				detail: {
					modal_id: element.id
				}
			} ) );
		}
	} );

	/**
	 * On the close event, i.e., a form got submit, empty the hash to prevent form from reopening.
	 */
	element.addEventListener( 'close', event => {
		if ( getId() === element.id ) {
			window.location.hash = '';
		}
	} );

	try {
		// try if there is a form inside the modal, otherwise continue in catch.
		element.querySelector( 'form' ).addEventListener( 'submit', () => {
			window.location.hash = '';
		} );
		hasForm = true;
	} catch ( e ) {
		let targetForm;
		try {
			targetForm = element.querySelector( 'button.advads-modal-close-action' ).form;
			hasForm    = true;
		} catch ( e ) {
		}
		try {
			/**
			 * Listen for the keydown event in all inputs.
			 * If the enter key is pressed and the modal has a form, submit it, else do nothing.
			 */
			element.querySelectorAll( 'input' ).forEach( input => {
				input.addEventListener( 'keydown', e => {
					if ( e.key !== 'Enter' ) {
						return;
					}

					if ( typeof targetForm !== 'undefined' && targetForm.reportValidity() ) {
						targetForm.submit();
						return;
					}

					// if there are inputs, but there is no form associated with them, do nothing.
					e.preventDefault();
				} );
			} );
		} catch ( e ) {

		}
	}

	if ( hasForm ) {
		/**
		 * Collect input values.
		 * Checkboxes are true/false.
		 * Radio buttons are true false on the saved value.
		 *
		 * @param {Node} input
		 * @return {*}
		 */
		const checkbox = input => {
			if ( input.type === 'checkbox' ) {
				return input.checked;
			}
			if ( input.type === 'radio' && input.checked ) {
				return input.value;
			}

			return input.value;
		};

		/**
		 * Collect inputs in this modal and save their initial and changed values (if any).
		 */
		element.querySelectorAll( 'input, select' ).forEach( input => {
			if ( ! input.name.length ) {
				return;
			}

			initialFormValues[input.name] = checkbox( input );

			input.addEventListener( 'change', event => {
				changedFormValues[input.name] = checkbox( input );
			} );
		} );
	}

	/**
	 * On the cancel buttons, check termination notice and close the modal.
	 */
	element.querySelectorAll( '.advads-modal-close, .advads-modal-close-background' ).forEach( button => {
		button.addEventListener( 'click', e => {
			e.preventDefault();
			element.dispatchEvent( new Event( 'cancel' ) );
		} );
	} );

	try {
		/**
		 * If the save button is not a `<button>` element. Close the form without changing the hash.
		 */
		element.querySelector( 'a.advads-modal-close-action' ).addEventListener( 'click', e => {
			e.preventDefault();
			element.close();
		} );
	} catch ( e ) {
	}
};

window.addEventListener( 'DOMContentLoaded', () => {
	try {
		if ( typeof document.querySelector( '.advads-modal[id^=modal-]' ).showModal !== 'function' ) {
			return;
		}
	} catch ( e ) {
		return;
	}
	[...document.getElementsByClassName( 'advads-modal' )].forEach( modal );
} );
