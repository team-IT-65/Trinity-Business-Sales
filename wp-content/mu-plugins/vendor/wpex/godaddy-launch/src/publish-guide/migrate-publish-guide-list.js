import AddDomain from './migrate-publish-guide-items/add-domain';
import ReviewSite from './migrate-publish-guide-items/review-site';

export default function MigratePublishGuideList() {
	return (
		<ul className="publish-guide-popover__items">
			<ReviewSite />
			<AddDomain />
		</ul>
	);
}

