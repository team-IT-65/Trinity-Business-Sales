// TODO: Utilize the REST API without the global.
// Backbone REST API client
global.wp = {};

// Prevent console messages when running tests.
console = {
	...console,

	/**
	 * Jest tests for deprecated and transforms result in native info console responses.
	 * Deleting the info method of `console` we are able to prevent test failures.
	 *
	 * Alternatively; we could assert with `@wordpress/jest-console` using
	 * `expect( console ).toHaveWarned();` however there are inconsistent results between
	 * various assertions making it difficult to conditionally assert with `jest-console`
	 */
	groupCollapsed: () => { },
	info: () => { },
	warn: () => { },
};

global.gdvPublishGuideDefaults = {
	appContainerClass: 'gdl-publish-guide',
	page: 1,
	userId: 1,
};

global.gdlPublishGuideItems = {
	AddDomain: {
		default: false,
		propName: 'gdl_pgi_add_domain',
		milestoneName: 'add-domain',
	},
	SiteContent: {
		default: false,
		propName: 'gdl_pgi_site_content',
		milestoneName: 'site-content',
	},
	SEOGuide: {
		default: false,
		propName: 'gdl_pgi_site_seo',
		milestoneName: 'site-seo',
	},
	SiteDesign: {
		default: false,
		propName: 'gdl_pgi_site_design',
		milestoneName: 'site-design',
	},
	SiteInfo: {
		propName: 'gdl_pgi_site_info',
		milestoneName: 'site-info',
	},
	SiteMedia: {
		default: false,
		propName: 'gdl_pgi_site_media',
		milestoneName: 'site-media',
	},
};

global.gdvLinks = {
	admin: 'example.com',
	changeDomain: 'example.com',
	editorRedirectUrl: 'example.com',
	seoRedirectUrl: 'example.com',
};
