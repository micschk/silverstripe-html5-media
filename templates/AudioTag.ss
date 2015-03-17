<div class="captionImage html5_media html5_audio">
	<audio controls preload="none">
		<!-- MP4 must be first for iPad! -->
		<source src="$MP3.URL" type="audio/mpeg"  /><!-- Safari / Webkit / iOS, IE9 -->
		<source src="$OGG.URL" type="audio/ogg"  /><!-- Firefox3.6+ / Opera 10.5+ -->
		<!-- fallback to Flash removed... -->
		<object width="200" height="20" type="application/x-shockwave-flash" 
				data="{$BaseHref}html5-media/flash/player.swf">
			<!-- Firefox uses the `data` attribute above, IE/Safari uses the param below -->
			<param name="movie" value="{$BaseHref}html5-media/flash/player.swf" />
			<param name="flashvars" value="controlbar=over&amp;file=$MP3.URL" />
			<!-- fallback -->
				<strong>Download:</strong>
				<a href="$MP3.URL">"MP3"</a>
				<a href="$OGG.URL">"OGG"</a>
		</object>
	</audio>
<% if $Content %>
	<div class="caption">$Content</div>
<% end_if %>
</div>