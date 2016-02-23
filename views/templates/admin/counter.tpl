<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading"><i class="icon-cogs"></i>Counter</div>
			<div class="panel-content">
				<p>Liste des fichier présent dans le dossier</p>
				{if $empty == 1}
				<p>Il n'y pas encore de fichier</p>
				{elseif $empty == 0}
					<ul>
					{foreach from=$files item=file}
					<li>{$file.filename} - {$file.nb_line}</li>
					{/foreach}
					</ul>
				{/if}
			</div>
		</div>
	</div>
</div>