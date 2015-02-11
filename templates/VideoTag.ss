<div class="captionImage html5_media html5_video">
	<video width="$Poster.Width" height="$Poster.Height" controls>
		<!-- MP4 must be first for iPad! -->
		<source src="$MP4.URL" type="video/mp4"  /><!-- Safari / iOS, IE9 -->
		<source src="$WEBM.URL" type="video/webm" /><!-- Chrome10+, Ffx4+, Opera10.6+ -->
		<source src="$OGV.URL" type="video/ogg"  /><!-- Firefox3.6+ / Opera 10.5+ -->
		<!-- fallback to Flash: -->
		<object width="$Poster.Width" height="$Poster.Height" type="application/x-shockwave-flash" 
				data="{$BaseHref}html5-media/flash/player.swf">
			<!-- Firefox uses the `data` attribute above, IE/Safari uses the param below -->
			<param name="movie" value="{$BaseHref}html5-media/flash/player.swf" />
			<param name="flashvars" value="controlbar=over&amp;image=$Poster.URL&amp;file=$MP4.URL" />
			<!-- fallback image. note the title field below, put the title of the video there -->
			<img src="$Poster.URL" width="$Poster.Width" height="$Poster.Height" alt="$Name"
				 title="No video playback capabilities, please download the video below" />
				<strong>Download:</strong>
				<a href="$MP4.URL">"MP4"</a>
				<a href="$WEBM.URL">"OGG"</a>
				<a href="$OGV.URL">"WebM"</a>
		</object>
	</video>
<% if $Content %>
	<div class="caption">$Content</div>
<% end_if %>
</div>