/**
 * WordPress dependencies
 */
import { combineReducers } from '@wordpress/data';

/**
 * Reducer for storing the Publish Guide's FAB activated state.
 *
 * @param {Object} state  Previous state.
 * @param {Object} action Action object.
 *
 * @return {Object} Updated state.
 */
export function publishGuideFabActive( state = false, action ) {
	switch ( action.type ) {
		case 'ACTIVATE_PUBLISH_GUIDE_FAB':
			return true;
		case 'DEACTIVATE_PUBLISH_GUIDE_FAB':
			return false;
	}

	return state;
}

/**
 * Reducer for storing the Publish Guide's open state.
 *
 * @param {Object} state  Previous state.
 * @param {Object} action Action object.
 *
 * @return {Object} Updated state.
 */
export function publishGuideActive( state = false, action ) {
	switch ( action.type ) {
		case 'OPEN_PUBLISH_GUIDE':
			return true;
		case 'CLOSE_PUBLISH_GUIDE':
			return false;
		case 'TOGGLE_PUBLISH_GUIDE':
			return ! state;
	}

	return state;
}

/**
 * Reducer for storing the Publish Guide's items state.
 *
 * @param {Object} state  Previous state.
 * @param {Object} action Action object.
 *
 * @return {Object} Updated state.
 */
export function guideItems( state = [], action ) {
	switch ( action.type ) {
		case 'SET_GUIDE_ITEMS':
			return [ ...action.guideItems ];
	}

	return state;
}

export default combineReducers( {
	guideItems,
	publishGuideActive,
	publishGuideFabActive,
} );
