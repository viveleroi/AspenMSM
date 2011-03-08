		</div>
	</div>
	<?php if(user()->isLoggedIn()){ // if logged in ?>
	<div id="footer">
		<p><strong>Questions?</strong> Contact Trellis Development <a href="mailto:support@trellisdev.com?subject=Bug Report: <?php print $this->APP->config('application_name') ?> v<?php print VERSION ?> @ <?php print $this->APP->params->server->getRaw('SERVER_NAME') ?>" title="Click to contact Trellis Development">support@trellisdev.com</a></p>
	</div>
	<?php } ?>
</body>
</html>