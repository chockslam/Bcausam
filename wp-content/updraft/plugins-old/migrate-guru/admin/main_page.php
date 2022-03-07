<div class="wrapper">
	<div class="horizontal-header" style="background: #25bea0; width: 99%; padding: 0px;">
		<div class="header-left">
			<div class="mg-branding" style="width: 100%; justify-content: left;">
			<img class="mg-logo" src="<?php echo plugins_url('./../img/migrateguru.png', __FILE__); ?>" style="width:91px"/>
				<div class="mg-heading" style="color: #FFF; margin-top: 10px;	margin-left: 10px;">
					Migrate Guru
				</div>
			</div>
				<div style="width: 400px; text-align: center; color: #FFF;">
					Powered By <a href="https://blogvault.net/" style="color: #17252a;">BlogVault</a>
				</div>
		</div>
		<div class="header-right">
			<div class="bv-branding">
				<a class="bv-button" target="_blank" rel="noopener noreferrer" href="https://migrateguru.freshdesk.com/support/home">
					FAQs
				</a>
				<a class="bv-button" target="_blank" rel="noopener noreferrer" href="https://migrateguru.freshdesk.com/support/home">
					Help Docs
				</a>
				<a class="bv-button" target="_blank" rel="noopener noreferrer" href="https://www.migrateguru.com/contact-us/">
					Support
				</a>
				<a class="bv-button" target="_blank" rel="noopener noreferrer" href="https://wordpress.org/support/plugin/migrate-guru/reviews">
					Rate us 5 stars
				</a>
			</div>
		</div>
	</div>
	<div class="main-content" style="padding-right: 0px; padding-top: 0px;">
		<div class="migrations" style="align-items: end;">
			<div class="mig-left">
				<div class="reg-form">
					<div class="form-heading">
						Easy WordPress Migrations
					</div>
					<a href="https://www.youtube.com/watch?v=HBGcUdOhMcI&t=15s" target="blank" class="video-link horizontal video-first">
						<img class="video-icon" src="<?php echo plugins_url('./../img/play-video.svg', __FILE__); ?>">
						<div class="video-text normal-text link">
							Watch our platform in action
						</div>
					</a>
					<div class="form-fields">
						<form id="mgform" dummy=">" action="<?php echo $this->bvinfo->appUrl(); ?>/migration/migrate" onsubmit="document.getElementById('migratesubmit').disabled = true;" method="post" name="signup">
							<?php $this->showErrors(); ?>
							<input type="text" id="email" name="email" placeholder="Enter your email here" style="float:left;">
							<input type="submit" name="submit" disabled id='migratesubmit' value="Migrate Site" class="primary-button" style="padding-left:24px"/>
							<div class="tnc-check">
								<label class="normal-text horizontal">
									<input type="checkbox" name="consent" onchange="document.getElementById('migratesubmit').disabled = !this.checked;" value="1"/>
									<span class="checkmark"></span>&nbsp;
									I agree to Blogvault &nbsp;
									<a href="https://blogvault.net/tos/" class="link">
										Terms & Conditions &nbsp;
									</a> and &nbsp;
									<a href="https://blogvault.net/privacy/" class="link">
										Privacy Policy
									</a>
								</label>
								<input type="hidden" name="bvsrc" value="wpplugin" />
								<input type="hidden" name="origin" value="migrateguru" />
							</div>
							<?php echo $this->siteInfoTags(); ?>
						</form>
					</div>
				</div>
				<a href="https://www.youtube.com/watch?v=HBGcUdOhMcI&t=15s" target="blank" class="video-link horizontal video-second">
					<img class="video-icon" src="<?php echo plugins_url('./../img/play-video.svg', __FILE__); ?>">
					<div href="https://www.youtube.com/watch?v=HBGcUdOhMcI&t=15s" target="blank" class="video-text normal-text link">
						Watch our platform in action
					</div>
				</a>
				<div class="supp-hosts" style="margin-top:15%">
					<div class="supp-heading normal-text">
						Supported Hosts:
					</div>
					<div class="logos">
						<img class="ind-logo" src="<?php echo plugins_url('./../img/GoDaddy.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/bluehost-logo.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/siteground-logo.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/HostGator-logo.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/flywheel.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/CPanel_logo.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/DreamHost.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/a2_hosting.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/inMotion_Logo.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/wp_engine_logo_bb.png', __FILE__); ?>" />
						<img class="ind-logo" src="<?php echo plugins_url('./../img/ftp.png', __FILE__); ?>" />
					</div>
				</div>
				<div class="info-row-second">
					<a class="link info" href="#">FAQs </a>
					<a class="link info" href="#">Help Docs </a>
					<a class="link info" href="#">Support </a>
					<a class="link info no-border" href="#">Rate us 5 stars </a>
				</div>
			</div>
			<div class="mig-right" style="overflow: hidden;">
				<div style="width:280px; float: right;">
					<div class="side-box" >
						<div class="side-header">Why MigrateGuru ?</div>
						<ul style="font-size: 15px; font-weight: bold;">
							<li><span class="bv-tick">&#x2713;</span> FREE Lifetime Migrations</li>
							<li><span class="bv-tick">&#x2713;</span> 1-Click Easy Migrations</li>
							<li><span class="bv-tick">&#x2713;</span> No Technical Knowledge Needed</li>
							<li><span class="bv-tick">&#x2713;</span> Works with 10,000+ webhosts</li>
							<li><span class="bv-tick">&#x2713;</span> No Site Size Limit</li>
						</ul>
					</div>
					<div class="side-box">
						<div class="side-header">Our other Popular Plugins</div>
						<div style="padding: 8px; overflow: hidden; background: #e6e3e3; margin: 10px; border-radius: 10px;">
							<div class="brand-name"><img class="mg-logo" src="<?php echo plugins_url('./../img/bv-logo.png', __FILE__); ?>" style="width:200px;"/></div>
							<div class="bv-quote">Did you get insurance for your Website? Backup your website now. Try for Free.</div>
							<a href='https://blogvault.net/?utm_source=mg_plugin_checkout&utm_medium=checkout_button_link&utm_campaign=mg_plugin_checkout&utm_term=checkout_button&utm_content=button' class="bv-button checkout-button" target="_blank" rel="noopener noreferrer">Checkout BlogVault Now</a>
						</div>
						<div style="padding: 8px; overflow: hidden; background: #e6e3e3; margin: 10px; border-radius: 10px;">
							<div class="brand-name"><img class="mg-logo" src="<?php echo plugins_url('./../img/mc-logo.png', __FILE__); ?>" style="width:200px"/></div>
							<div class="bv-quote">Is your website hacked? Don't leave it on chance.</div>
							<div class="bv-quote">Scan your website For Free Now.</div>
							<a href='https://www.malcare.com/?utm_source=mg_plugin_checkout&utm_medium=checkout_button_link&utm_campaign=mg_plugin_checkout&utm_term=checkout_button&utm_content=button' class="bv-button checkout-button" target="_blank" rel="noopener noreferrer">Checkout MalCare Now</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>