/**
 * Returns an action object used in signalling that the publish guide FAB should be activated.
 *
 * @return {Object} Action object
 */
export function activatePublishGuideFab() {
	return {
		type: 'ACTIVATE_PUBLISH_GUIDE_FAB',
	};
}

/**
 * Returns an action object used in signalling that the publish guide FAB should be deactivated.
 *
 * @return {Object} Action object
 */
export function deactivatePublishGuideFab() {
	return {
		type: 'DEACTIVATE_PUBLISH_GUIDE_FAB',
	};
}

/**
 * Returns an action object used in signalling that the user opened the publish guide.
 *
 * @return {Object} Action object
 */
export function openPublishGuide() {
	return {
		type: 'OPEN_PUBLISH_GUIDE',
	};
}

/**
 * Returns an action object used in signalling that the user closed the publish guide.
 *
 * @return {Object} Action object
 */
export function closePublishGuide() {
	return {
		type: 'CLOSE_PUBLISH_GUIDE',
	};
}

/**
 * Returns an action object used in signalling that the user toggles the publish guide.
 *
 * @return {Object} Action object
 */
export function togglePublishGuide() {
	return {
		type: 'TOGGLE_PUBLISH_GUIDE',
	};
}

/**
 * Returns an action object used in signalling to set publish guide items.
 *
 * @param {Object} items
 * @return {Object} Action object
 */
export function setGuideItems( items ) {
	return {
		guideItems: items,
		type: 'SET_GUIDE_ITEMS',
	};
}
