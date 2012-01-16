var position_picker_ratio;
var position_picker_interval_ID;

function checkImageSize()
{

}

jQuery(function(){
	var $ = jQuery;
		maxWidth = $("div.field-positionpicker").width();

	$("div.field-positionpicker select").change(function(){
		var id = $(this).val(),
			$pp = $('div.position_picker');

		if(id != 0) {
			if(id == -1) {
				$(this).hide();

				// Static image
				var file = $("div.position_picker_vars var.path").text();

				$pp.html('<img src="' + Symphony.WEBSITE + '/extensions/position_picker/assets/crosshair.gif" class="crosshair" /><img src="' + file + '" class="pic" />');

				var originalWidth = $pp.find("img.pic").width(),
					originalHeight = $pp.find("img.pic").height();
			}
			else {
				var file = Symphony.WEBSITE + '/workspace' + $("div.position_picker_vars var[rel=" + id + "].path").text();

				$pp.html('<img src="' + Symphony.WEBSITE + '/extensions/position_picker/assets/crosshair.gif" class="crosshair" /><img src="' + file + '" class="pic" />');

				var originalWidth = $("div.position_picker_vars var[rel=" + id + "].width").text(),
					originalHeight = $("div.position_picker_vars var[rel=" + id + "].height").text();
			}

			var ratio = maxWidth / originalWidth,
				imageWidth = originalWidth * ratio,
				imageHeight = originalHeight * ratio;

			// show the image:
			$pp.find("img.pic").width(maxWidth).height(originalHeight * ratio);

			if($('#unit_type').val() == 'percentage') {
				$pp.delegate('img.pic', 'click', function(event) {
					var $img = $(this),
						pixelOffsetX = event.pageX - $img.offset().left,
						pixelOffsetY = event.pageY - $img.offset().top;

					var offsetX = (pixelOffsetX / imageWidth) * 100,
						offsetY = (pixelOffsetY / imageHeight) * 100;

					$("img.crosshair", $img.parent()).css({
						marginLeft: pixelOffsetX - 16,
						marginTop: pixelOffsetY - 16
					});

					$("input[type=hidden]", $img.closest('label')).val(offsetX + ',' + offsetY);

					return false;
				});

				var coords = $("input[type=hidden]", $(this).parent()).val().split(',');
				if(coords.length == 2) {
					var xPos = imageWidth * (coords[0]/100),
						yPos = imageHeight * (coords[1]/100);

					$pp.find("img.crosshair", $(this).parent()).css({
						marginLeft: xPos - 16,
						marginTop: yPos - 16
					});
				}
			}

			else {
				$pp.delegate("img.pic", "click", function(event) {
					var $img = $(this),
						offsetX = event.pageX - $img.offset().left,
						offsetY = event.pageY - $img.offset().top;

					$("img.crosshair", $img.parent()).css({
						marginLeft: offsetX - 16,
						marginTop: offsetY - 16
					});

					$("input[type=hidden]", $img.closest('label')).val(Math.round(offsetX / ratio) + ',' + Math.round(offsetY / ratio));

					return false;
				});

				var coords = $("input[type=hidden]", $(this).parent()).val().split(',');
				if(coords.length == 2) {
					$pp.find("img.crosshair", $(this).parent()).css({
						marginLeft: Math.round(coords[0] * ratio) - 16 + "px",
						marginTop: Math.round(coords[1] * ratio) - 16 + "px"
					});
				}
			}
		}

		else {
			$pp.html('');
		}

	}).change(); // Fire to make sure that if there is already something selected it gets shown
});