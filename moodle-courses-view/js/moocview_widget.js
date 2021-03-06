jQuery(document).ready(function(){
	jQuery(document).on("change", '.moocview-contenttype-class', function(){
		if(jQuery(this).val() == 0)
		{
			jQuery(this).parent().siblings(".moocview-pmaxcatcourses-class").hide();
			jQuery(this).parent().siblings(".moocview-pmorelink-class").hide();
			jQuery(this).parent().siblings(".moocview-pmorelinkurl-class").hide();
			jQuery(this).parent().siblings(".moocview-categoryparam-class").hide();
		}
		else
		{
			jQuery(this).parent().siblings(".moocview-pmaxcatcourses-class").children('label').text(contentTypeLbl[parseInt(jQuery(this).val())]);
			jQuery(this).parent().siblings(".moocview-pmaxcatcourses-class").show();
			jQuery(this).parent().siblings(".moocview-pmorelink-class").show();
			if(jQuery(this).parent().siblings(".moocview-pmorelink-class").val() == 'other')
			{	
				jQuery(this).parent().siblings(".moocview-pmorelinkurl-class").show();
				jQuery(this).parent().siblings(".moocview-categoryparam-class").show();
			}
		}
	});
	
	jQuery(document).on("change", '.moocview-morelink-class', function(){
		if(jQuery(this).val() != 'other')
		{
			jQuery(this).parent().siblings(".moocview-pmorelinkurl-class").children('input').val(jQuery(this).val());
			jQuery(this).parent().siblings(".moocview-pmorelinkurl-class").hide();
			jQuery(this).parent().siblings(".moocview-categoryparam-class").hide();
		}
		else
		{
			jQuery(this).parent().siblings(".moocview-pmorelinkurl-class").children('input').val('');
			jQuery(this).parent().siblings(".moocview-pmorelinkurl-class").show();
			jQuery(this).parent().siblings(".moocview-categoryparam-class").show();
		}
	});
});