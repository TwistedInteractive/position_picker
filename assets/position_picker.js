jQuery(function(){
	var $ = jQuery;
	var maxWidth = $("div.field-positionpicker").width();
	$("div.field-positionpicker select").change(function(){
		var id = $(this).val();
		if(id != 0)	{
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
			
			var imageWidth = originalWidth * ratio;
			var imageHeight = originalHeight * ratio;

			// show the image:		
			$("div.position_picker", $(this).parent()).html('<img src="' + Symphony.WEBSITE + '/extensions/position_picker/assets/crosshair.gif" class="crosshair" /><img src="' + file + '" class="pic" />');
			$("div.position_picker img.pic").width(maxWidth);
			$("div.position_picker img.pic").height(originalHeight * ratio);
			// Position the crosshair on a click:
			$("div.position_picker img.pic").click(function(e){
				var pixelOffsetX = e.pageX - $(this).offset().left;
		        var pixelOffsetY = e.pageY - $(this).offset().top;
		        
				var offsetX = (pixelOffsetX / imageWidth)*100;
		        var offsetY = (pixelOffsetY / imageHeight)*100;

				$("img.crosshair", $(this).parent()).css({left: (offsetX -1.6) + '%', top: offsetY + '%'});
				//$("input[type=hidden]", $(this).parent().parent()).val(Math.round(offsetX / ratio) + ',' + Math.round(offsetY / ratio));
				$("input[type=hidden]", $(this).parent().parent()).val(offsetX + ',' + offsetY);
				console.log($("input[type=hidden]", $(this).parent().parent()).val());
				return false;
			});
			// Check if there are already coordinates set:
			coords = $("input[type=hidden]", $(this).parent()).val().split(',');
			if(coords.length == 2)
			{
				// original, for pixels
				//$("div.position_picker img.crosshair", $(this).parent()).css({marginLeft: Math.round(coords[0] * ratio) - 16 + "px", marginTop: Math.round(coords[1] * ratio) - 16 + "px"});
				// for percentage
				$("div.position_picker img.crosshair", $(this).parent()).css({left: (coords[0] -1.6) + "%", top: coords[1] + "%"});
			}
		} else {
			$("div.position_picker", $(this).parent()).html('');
		}
	}).change(); // Fire to make sure that if there is already something selected it gets shown
});