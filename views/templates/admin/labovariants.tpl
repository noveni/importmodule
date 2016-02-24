<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading"><i class="icon-cogs">Chek the type of variants</i></div>
			<div class="panel-content">
				<p>Liste des fichiers présent dans le dossier</p>
				{if $files}
					<ul>
						<li>Nom du fichier - Nmb de ligne - Type différent</li>
					{foreach from=$files item=file}
						<li>{$file.filename} - {$file.nb_line} - 
						{foreach from=$file.variants.type item=type}
							{$type}
						{/foreach}
						-
						[
						{foreach from=$file.variants.name_of_t item=name_of_t}
							{$name_of_t},
						{/foreach}
						]
						</li>
					{/foreach}
					</ul>
				{else}
					<p>Il n'y a aucun fichier, veuillez en rajouter via <a href="{$link->getAdminLink('AdminImporterCounter',true)}">Counter feature</a>.</p>
				{/if}
			</div>
		</div>
	</div>
</div>