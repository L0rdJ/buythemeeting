{extends file='layout.tpl'}

{block name=content}
	<div class="container">
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
					{foreach $album.photos as $key => $photo}
					<div class="col-lg-4 col-md-6 photo">
						<div class="thumbnail">
							<a class="pull-left thumbnail" href="http://vk.com/photo-{$photo['owner_id']|abs}_{$photo['pid']}" target="blank">
								<img src="{$photo.src_big}" alt="{$photo.info.name}">
							</a>
							<div style="clear: both;"></div>
							<div class="caption">
								<h4>{$key+1}. {$photo.info.number}, {$photo.info.name}</h4>
								<h3>{$photo.current_bid} грн.</h3>
								{*{$photo.info.full_desc}*}
							</div>
						</div>
					</div>
					{/foreach}
				</div>
			</div>
			{/foreach}
		</div>
	</div>
{/block}
