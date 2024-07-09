/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { MediaUpload } from '@wordpress/media-utils';

addFilter(
	'editor.MediaUpload',
	'godaddy-launch/publish-guide/replace-media-upload',
	() => MediaUpload
);
