<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading"><i class="icon-cogs"></i> Feed with the feed</div>
			<div class="panel-content">
				<p>{l s='Pour synchroniser les produis, cliquer sur le bouton ce-dessous.' mod='importer'}</p>
				<p>{l s='Une fois cliquer sur le bouton. Le process va mapper les produits et les analyser pour les inserer dans la base de données.'}</p>
				<div class="importer-stats">
					<p>Nom du fichier: <span id="xml_filename">{$xml_filename}</span> </p>
					<p>Il y a <span id="xml_file_count">{$xml_file_count}</span> ligne dans le fichier.</p>
					<p>Current key in the XML file : <span id="xml_current_key">{$xml_current_key}</span></p>
				</div>
				<form action="{$link->getAdminLink('AdminImporterRunning',true)}" method="post" id="step2form">
					<button type="submit" class="btn btn-default btn-md" id="step2from_start">{l s='Cliquez ici pour synchroniser les produits'}</button>
					<input type="hidden" id="submitStep2Importer" name="submitStep2Importer" value="1">
				</form>
			</div>
			<div class="panel-footer">
				<button type="button" class="btn btn-default" id="reset_xml_line" data-url="{$link->getAdminLink('AdminImporterRunning',true)}"><i class="process-icon-refresh"></i>{l s='Reset XML line count'}</button>
				<button type="button" class="btn btn-default" id="test_import" data-url="{$link->getAdminLink('AdminImporterRunning',true)}"><i class="process-icon-upload"></i>{l s='Import test'}</button>
			</div>
		</div>
		<div class="panel">
			<div class="panel-heading">Importation</div>
			<div class="panel-content">
				<table class="table table-striped" id="product_table">
					<tr>
						<th>N°</th>
						<th>ID</th>
						<th>Art n°</th>
						<th>Titre</th>
						<th>b2b price(VAT excl.)</th>
						<th>b2c price(VAT incl.)</th>
						<th>Date d'ajout</th>
						<th>Date de modif</th>
						<th>Image</th>
					</tr>
				</table>
			</div>
		</div>
		<div class="panel">
			<div class="panel-heading">Erreur</div>
			<div class="panel-content" id="error_stant"></div>
		</div>
	</div>
</div>