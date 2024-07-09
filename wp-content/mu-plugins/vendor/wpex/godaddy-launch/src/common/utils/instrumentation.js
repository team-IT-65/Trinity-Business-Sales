const dataLayer = window._expDataLayer || [];

// See https://godaddy-corp.atlassian.net/wiki/spaces/CKPT/pages/92315197/3.+Logging+Page+Views+for+TCCL
export const logPageEvent = ( page ) => {
	dataLayer.push( {
		data: {
			path: '/' + page,
		},
		schema: 'add_page_view',
		version: 'v1',
	} );
};

// Logs an interaction event to Traffic.
export const logInteractionEvent = ( { eid, type = 'click', data = null } ) => {
	dataLayer.push( {
		data: {
			custom_properties: data,
			eid: `${ eid }.${ type }`,
			type,
		},
		schema: 'add_event',
		version: 'v1',
	} );
};

// See https://godaddy-corp.atlassian.net/wiki/spaces/CKPT/pages/92315189/4.+Logging+Events+for+TCCL
export const logImpressionEvent = ( eid ) => {
	dataLayer.push( {
		data: {
			eid,
			type: 'impression',
		},
		schema: 'add_event',
		version: 'v1',
	} );
};
