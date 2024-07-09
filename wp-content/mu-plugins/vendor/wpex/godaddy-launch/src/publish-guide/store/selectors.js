/**
 * Returns true if the Publish Guide FAB is active.
 *
 * @param {Object} state Global application.
 *
 * @return {boolean} Whether the publish guide FAB is active.
 */
export function isPublishGuideFabActive( state ) {
	return state.publishGuideFabActive;
}

/**
 * Returns true if the Publish Guide is opened.
 *
 * @param {Object} state Global application.
 *
 * @return {boolean} Whether the publish guide is open.
 */
export function isPublishGuideOpened( state ) {
	return state.publishGuideActive;
}

/**
 * Returns publish guide items.
 *
 * @param {Object} state Global application.
 *
 * @return {Array} Array of guide items.
 */
export function getGuideItems( state ) {
	return state.guideItems;
}

/**
 * Returns publish guide items completion status.
 *
 * @param {Object} state Global application.
 *
 * @return {boolean} Whether the publish guide items are all complete.
 */
export function getGuideItemsComplete( state ) {
	const completedItems = state.guideItems.filter( ( item ) => item.props.hasCompleted );
	return completedItems.length === state.guideItems.length;
}
