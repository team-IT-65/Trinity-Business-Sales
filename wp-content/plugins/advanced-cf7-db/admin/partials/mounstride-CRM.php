<?php
// Exit if accessed directly
if(!defined( 'ABSPATH' ) ) {
	exit;
}

//enqueue date time picker CSS in file
wp_enqueue_style('mounstride-CRM-css');

?>

<div class="mountstride-crm-wrapper">
	<div class="top-section">
		<div class="container">
			<h1 class="header-typ1">Grow your Leads, Contacts and Sales Activities</h1>
			<div class="header-typ2">Start collaborating as a team, as you always wanted to</div>
			<div class="base-text">Capture leads and manage them in a more efficient way. Manage your sales activities, contact, organization and email conversation against each lead from a single view.</div>
		</div>
	</div>

	<div class="bottom-section">
		<div class="container">
			<div class="features-box-wrapper">
				
				<div class="features-box clearfix odd">
					<div class="inner-section">
						<div class="tbl">
							<div class="tbl-cell info-section-wrapper">
								<div class="info-section">
									<div class="image visible-xs"><img src="<?php echo plugins_url('images/Web-to-Lead.svg', dirname(__FILE__)) ; ?>" title="Web-to-Lead" alt="Web-to-Lead" class="img-responsive" /></div>
									<h3 class="header-typ3">Web-to-Lead</h3>
									<div class="body-text"><p>Automatically add data from your website form as lead / contact and set auto allocation rules from rule engine</p></div>
								</div>
							</div>
							<div class="tbl-cell info-image-section-wrapper hidden-xs">
								<div class="info-image-section">
									<div class="image"><img src="<?php echo plugins_url('images/Web-to-Lead.svg', dirname(__FILE__)) ; ?>" title="Web-to-Lead" alt="Web-to-Lead" class="img-responsive" /></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="features-box clearfix even">
					<div class="inner-section">
						<div class="tbl">
							<div class="tbl-cell info-image-section-wrapper hidden-xs">
								<div class="info-image-section">
									<div class="image"><img src="<?php echo plugins_url('images/Automation-Through-Rule-Engine.svg', dirname(__FILE__)) ; ?>" title="Automation Through Rule Engine" alt="Automation Through Rule Engine" class="img-responsive" /></div>
								</div>
							</div>
							<div class="tbl-cell info-section-wrapper">
								<div class="info-section">
									<div class="image visible-xs"><img src="<?php echo plugins_url('images/Automation-Through-Rule-Engine.svg', dirname(__FILE__)) ; ?>" title="Automation Through Rule Engine" alt="Automation Through Rule Engine" class="img-responsive" /></div>
									<h3 class="header-typ3">Automation through Rule Engine</h3>
									<div class="body-text"><p>Use rule engine to automate lead allocation process by defining rules for all incoming leads via web and email. Match criteria and set actions to be performed like assign labels and set ownership.</p></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="features-box clearfix odd">
					<div class="inner-section">
						<div class="tbl">
							<div class="tbl-cell info-section-wrapper">
								<div class="info-section">
									<div class="image visible-xs"><img src="<?php echo plugins_url('images/Email-Centralisation.svg', dirname(__FILE__)) ; ?>" title="Email Centralisation" alt="Email Centralisation" class="img-responsive" /></div>
									<h3 class="header-typ3">Email Centralisation</h3>
									<div class="body-text"><p>You own your mails. We do not sync your personal email inbox.</p><p>Unorganised. Scattered. Random. Not Prioritised. – With our cleverly integrated solution of mountstride and histride, we address standard challenges posed by traditional email inbox.</p><p>Manage all conversations in one place, no more digging through emails.</p></div>
								</div>
							</div>
							<div class="tbl-cell info-image-section-wrapper hidden-xs">
								<div class="info-image-section">
									<div class="image"><img src="<?php echo plugins_url('images/Email-Centralisation.svg', dirname(__FILE__)) ; ?>" title="Email Centralisation" alt="Email Centralisation" class="img-responsive" /></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="features-box clearfix even">
					<div class="inner-section">
						<div class="tbl">
							<div class="tbl-cell info-image-section-wrapper hidden-xs">
								<div class="info-image-section">
									<div class="image"><img src="<?php echo plugins_url('images/Module-Customization.svg', dirname(__FILE__)) ; ?>" title="Customization" alt="Customization" class="img-responsive" /></div>
								</div>
							</div>
							<div class="tbl-cell info-section-wrapper">
								<div class="info-section">
									<div class="image visible-xs"><img src="<?php echo plugins_url('images/Module-Customization.svg', dirname(__FILE__)) ; ?>" title="Customization" alt="Customization" class="img-responsive" /></div>
									<h3 class="header-typ3">Customization</h3>
									<div class="body-text"><p>Create custom stages based on your unique sales funnel.</p><p>Add input data fields for your business by creating custom fields.</p><p>Define module name and labels that are more familiar in your business environment.</p></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
			</div>
			<div class="btn-outer"><a href="<?php print esc_url('https://www.mountstride.com/contact/');?>" target="_blank" rel="nofollow" class="btn-typ1">Contact Us</a></div>
		</div>
	</div>
</div>