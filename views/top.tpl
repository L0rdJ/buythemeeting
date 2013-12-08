{extends file='layout.tpl'}

{block name=content}
	<div class="container">
		<div class="alert alert-info">
			<h4>Добра новина!</h4>
			<p>В разі, якщо ти тут вперше - тобі пощастило! Наступні декілька хвилин ти можеш зайнятись чимось цікавим, оскільки завантаження данної сторінки потребує деякого часу.</p>
		</div>

		<!-- Nav tabs -->
		<ul class="nav nav-pills nav-justified">
			{foreach from=$data.albums item=album name=albums}
			<li{if $smarty.foreach.albums.first} class="active"{/if}><a href="#album-{$album.aid}" data-toggle="tab">{$album.title} <span class="badge">{$album.photos|count}</span></a></li>
			{/foreach}
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			{foreach from=$data.albums item=album name=albums}
			<div class="tab-pane{if $smarty.foreach.albums.first} active{/if}" id="album-{$album.aid}">
				<div class="row photos">
					{foreach $album.photos|@array_slice:0:$data.top_limit as $key => $photo}
					<div class="col-lg-3 col-md-4 photo">
						<div class="thumbnail">
							<a class="pull-left thumbnail" href="http://vk.com/photo-{$photo['owner_id']|abs}_{$photo['pid']}" target="blank" data-toggle="tooltip" data-html="true" data-placement="right" title="<h2>{$key+1}<h2><h4>{$photo.info.number}</h4><h3>{$photo.current_bid}&nbsp;грн.</h3>">
								<img src="{$photo.src_big}" alt="{$photo.info.name}">
							</a>
						</div>
					</div>
					{/foreach}
				</div>
			</div>
			{/foreach}
		</div>
	</div>
{/block}

{block name=js}
<script>
jQuery( function() {
	var doMasonry = function() {
		{foreach from=$data.albums item=album}
		jQuery('#album-{$album.aid}').masonry( {
			itemSelector: '.photo'
		} );
		{/foreach}
	};
	var loadTabImages = function() {
		imagesLoaded( jQuery( 'body' ), doMasonry );
	}

	loadTabImages();
	jQuery( 'a[data-toggle="tab"]' ).on( 'shown.bs.tab', function() {
		loadTabImages();
	} );
	jQuery( '.photo a' ).tooltip();
} );
</script>
{/block}
