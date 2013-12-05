{extends file='layout.tpl'}

{block name=content}
	<div class="container">
		<div class="alert alert-danger">
			<h4>Error</h4>
			<p>{$data.message}</p>

			<p>
				<a class="btn btn-danger" href="{$data.base_web_dir}">Home page</a>
			</p>
		</div>
	</div>
{/block}
