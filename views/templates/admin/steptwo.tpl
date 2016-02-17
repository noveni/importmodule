<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading"><i class="icon-cogs"></i>feed with the feed</div>
			<div class="panel-content">
				<p>{l s='Pour synchroniser les produis, cliquer sur le bouton ce-dessous.' mod='importer'}</p>
				<p>{l s='Une fois cliquer sur le bouton. Le process va mapper les produits et les analyser pour les inserer dans la base de données.'}</p>
				<p>{$link->getAdminLink('AdminImporterRunning',true)}</p>
				<form action="{$link->getAdminLink('AdminImporterRunning',true)}" method="post" id="step2form">
					<button type="submit" class="btn" id="step2from_start">{l s='Cliquez ici pour synchroniser les produits'}</button>
					<input type="hidden" id="submitStep2Importer" name="submitStep2Importer" value="1">
				</form>
			</div>
		</div>
	</div>
</div>