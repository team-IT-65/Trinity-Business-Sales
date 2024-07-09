const dataLayer = window._expDataLayer || [];

// See https://godaddy-corp.atlassian.net/wiki/spaces/CKPT/pages/92315197/3.+Logging+Page+Views+for+TCCL
export const logPageEvent = (page) => {
    dataLayer.push({
        schema: 'add_page_view',
        version: 'v1',
        data: {
            path: '/' + page
        }
    });
}

/**
 * Logs an interaction event to Traffic.
 * @param {string} eid represent the data eid to track.
 * @param {string} type can be one of 'click', 'hover', 'touch', 'custom'
 * @param {object} data custom data to be logged.
 */
export const logInteractionEvent = ({ eid, type = 'click', data = null }) => {
    dataLayer.push({
        schema: 'add_event',
        version: 'v1',
        data: {
            eid: `${eid}.${type}`,
            type,
            custom_properties: data
        }
    });
}

// See https://godaddy-corp.atlassian.net/wiki/spaces/CKPT/pages/92315189/4.+Logging+Events+for+TCCL
export const logImpressionEvent = (eid) => {
    dataLayer.push({
        schema: 'add_event',
        version: 'v1',
        data: {
            type: 'impression',
            eid: eid + '.impression'
        }
    });
}
