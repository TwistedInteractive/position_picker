jQuery(function(){
	var $ = jQuery;
	var maxWidth = $("div.field-positionpicker").width();
	$("div.field-positionpicker select").change(function(){
		var id = $(this).val();
		if(id != 0)
		{
			if(id == -1) {
                // Static image
                var info = $("div.position_picker_vars var[rel=url]").text().split('*');
                var file = Symphony.WEBSITE + info[0];
                $("div.field-positionpicker select").hide();
            } else {
                var info = $("div.position_picker_vars var[rel=" + id + "]").text().split('*');
                var file = Symphony.WEBSITE + '/workspace' + info[0];
            }
			var originalWidth = info[1];
			var originalHeight = info[2];
			var ratio = maxWidth / originalWidth;
			// show the image:		
			$("div.position_picker", $(this).parent()).html('<img src="' + Symphony.WEBSITE + '/extensions/position_picker/assets/crosshair.gif" class="crosshair" /><img src="' + file + '" class="pic" />');
			$("div.position_picker img.pic").width(maxWidth);
			$("div.position_picker img.pic").height(originalHeight * ratio);
			// Position the crosshair on a click:
			$("div.position_picker img.pic").click(function(e){
				var offsetX = e.offsetX;
				var offsetY = e.offsetY;
				$("img.crosshair", $(this).parent()).css({marginLeft: offsetX - 16, marginTop: offsetY - 16});
				$("input[type=hidden]", $(this).parent().parent()).val(Math.round(offsetX / ratio) + ',' + Math.round(offsetY / ratio));
				console.log($("input[type=hidden]", $(this).parent().parent()).val());
				return false;
			});
			// Check if there are already coordinates set:
			coords = $("input[type=hidden]", $(this).parent()).val().split(',');
			if(coords.length == 2)
			{
				$("div.position_picker img.crosshair", $(this).parent()).css({marginLeft: Math.round(coords[0] * ratio) - 16 + "px", marginTop: Math.round(coords[1] * ratio) - 16 + "px"});
			}
		} else {
			$("div.position_picker", $(this).parent()).html('');
		}
	}).change(); // Fire to make sure that if there is already something selected it gets shown
});