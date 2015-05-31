streetInput = '';
cityInput = '';
stateInput = '';
$.validator.setDefaults({
	submitHandler: function() {
		streetInput = document.getElementById('streetAddress').value;
		cityInput = document.getElementById('city').value;
		stateInput = document.getElementById('state').value;                    
		$.ajax({
			url: 'sss.php',
			//url: "http://findyourvilla.elasticbeanstalk.com",
			data: { 
			'street' : streetInput,
			'city' : cityInput,
			'state' : stateInput 
			},
			type: 'GET',
			success: successFunc,
			error: function(){}
		});
	}
});
$().ready(function() {
	$("#realEstate").validate({
	errorPlacement: function(error, element) {
		element.parent().addClass("has-error");
		error.appendTo($("p#"+element.attr("id")+"ErrorContainer"));
		document.getElementById('response_content').setAttribute('style','display:none');
		document.getElementById('footer').setAttribute('style','display:none');
	},
	success: function(label) {					
		document.getElementById(label.attr("for")).parentNode.setAttribute("class", "form-group");
	}
	});
	$("#city").on('input',function() {
		if($(this).val().length == 0) { 			
			document.getElementById('city').parentElement.setAttribute("class","form-group has-error");
			$(this).valid();				
		}
	});
	$("#streetAddress").on('input',function() {
		if($(this).val().length == 0) {
			document.getElementById('streetAddress').parentElement.setAttribute("class","form-group has-error");
			$(this).valid()
		}
	});
	$("#state").change(function() {
		if($(this).val().length == 0) {
			document.getElementById('state').parentElement.setAttribute("class","form-group has-error");
			$('#state').valid();			
		}
	});
});
function successFunc(output) {
	resp = JSON && JSON.parse(output) || $.parseJSON(output);
	html_text = '';				
	if(resp.status && resp.status.text == 'success' && resp.homeDetails) {
		// FB Share request creation
		var chartUrl = (resp.chart && resp.chart['5years']) ? resp.chart['5years'].url : '';
		var priceSym = (resp.homeDetails.img_overall_change.indexOf('up_g.gif')!=-1) ? priceSym = '+' : priceSym='-';
		priceSym = (resp.homeDetails.overall_change != 'N/A') ? priceSym : '';

		var fbShareDetails = '\"'+chartUrl+'\", \"'+resp.homeDetails.header_url+'\", \"'+resp.homeDetails.header_text+'\", \"'+resp.homeDetails.last_sold_price+'\", \"'+priceSym+resp.homeDetails.overall_change+'\"';

		if(resp.homeDetails.zestimate_last_updated=='N/A') resp.homeDetails.zestimate_last_updated = '';
		if(resp.homeDetails.rentzestimate_last_updated=='N/A') resp.homeDetails.rentzestimate_last_updated = '';

		basic_info= "<div class = 'table-responsive' style='overflow:auto;background-color:white;padding:20px;border:1px solid;border-radius:3px;border-color:#f0ad4e;box-shadow:-2.5px 2px 6px -4px rgba(243, 178, 73, 0.4) inset'><table class='table table-striped'>";
		basic_info+="<tr><td colspan=2>See more details for <a href="+resp.homeDetails.header_url+" target='_blank'>"+resp.homeDetails.header_text+"</a> on Zillow</td><td colspan='2' class='text-right'><button class='btn btn-primary' onclick='shareOnFb("+fbShareDetails+")'>Share on <b>facebook</b></button></td></tr>";
		basic_info+= "<tr><td>Property Type:</td><td align=right>"+resp.homeDetails.property_type+"</td><td>Last Sold Price:</td><td align=right>"+resp.homeDetails.last_sold_price+"</td></tr>";
		basic_info+= "<tr><td>Year Built:</td><td align=right>"+resp.homeDetails.year_built+"</td><td>Last Sold Date:</td><td align=right>"+resp.homeDetails.last_sold_date+"</td></tr>";
		basic_info+= "<tr><td>Lot Size:</td><td align=right>"+resp.homeDetails.lot_size+"</td><td>Zestimate<sup>&reg;</sup> Property Estimate as of "+resp.homeDetails.zestimate_last_updated+":</td><td align=right>"+resp.homeDetails.zestimate_amount+"</td></tr>"; 
		basic_info+= "<tr><td>Finished Area:</td><td align=right>"+resp.homeDetails.finished_area+"</td><td>30 Days Overall Change "+''+":</td><td align=right>"+resp.homeDetails.img_overall_change+' '+resp.homeDetails.overall_change+"</td></tr>"; 
		basic_info+= "<tr><td>Bathrooms:</td><td align=right>"+resp.homeDetails.bathrooms+"</td><td>All Time Property Change:</td><td align=right>"+resp.homeDetails.property_range+"</td></tr>"; 
		basic_info+= "<tr><td>Bedrooms:</td><td align=right>"+resp.homeDetails.bedrooms+"</td><td>Rent Zestimate<sup>&reg;</sup> Rent Valuation as of "+resp.homeDetails.rentzestimate_last_updated+":</td><td align=right>"+resp.homeDetails.rentzestimate_amount+"</td></tr>"; 
		basic_info+= "<tr><td>Tax Assessment Year:</td><td align=right>"+resp.homeDetails.tax_assessment_yr+"</td><td>30 Days Rent Change "+''+":</td><td align=right>"+resp.homeDetails.img_rent_change+' '+resp.homeDetails.rent_change+"</td></tr>";    
		basic_info+= "<tr><td>Tax Assessment:</td><td align=right>"+resp.homeDetails.tax_assessment+"</td><td>All Time Rent Range:</td><td align=right>"+resp.homeDetails.all_time_rent_range+"</td></tr>";     
		basic_info+= "</table>";
		basic_info+= "</div>";
		html_text = basic_info;
		
		document.getElementById('basic_info').innerHTML = html_text;
		document.getElementById('basic_info').setAttribute("class", "tab-pane active");
		document.getElementById('historical_zestimates').setAttribute("class", "tab-pane");
		document.getElementById('bs').setAttribute("class", "active");
		document.getElementById('hz').setAttribute("class", "");
		
		if(resp.chart) {
			document.getElementById('1year').innerHTML = '<img class="img-responsive" style="margin: 0 auto;" class="text-center" src=\"'+resp.chart['1year'].url+'\"><div class="carousel-caption" style="width:100%;padding:0px;background-color: rgba(0, 0, 0, 0.5);"><h4 class="text-left">&nbsp;Historical Zestimates for the past 1 year</h4><p class="text-left">&nbsp;'+resp.homeDetails.header_text+'</p></div>';
			document.getElementById('5years').innerHTML = '<img class="img-responsive" style="margin: 0 auto;" src=\"'+resp.chart['5years'].url+'\"><div class="carousel-caption" style="width:100%;padding:0px;background-color: rgba(0, 0, 0, 0.5);"><h4 class="text-left">&nbsp;Historical Zestimates for the past 5 years</h4><p class="text-left">&nbsp;'+resp.homeDetails.header_text+'</p></div>';
			document.getElementById('10years').innerHTML = '<img class="img-responsive" style="margin: 0 auto;" src=\"'+resp.chart['10years'].url+'\"><div class="carousel-caption" style="width:100%;padding:0px;background-color: rgba(0, 0, 0, 0.5);"><h4 class="text-left">&nbsp;Historical Zestimates for the past 10 years</h4><p class="text-left">&nbsp;'+resp.homeDetails.header_text+'</p></div>';
			document.getElementById('historical_zestimates_carousel').setAttribute('style','display:block');
			document.getElementById("nochart").setAttribute('style','display:none');
		} else {
			document.getElementById("nochart").innerHTML = "<br><p class='text-center'>Sorry. No Historical Zestimates Found</p><br>";
			document.getElementById("nochart").setAttribute('style','display:block');
			document.getElementById('historical_zestimates_carousel').setAttribute('style','display:none');
			
		}
		document.getElementById('response_error').setAttribute('style','display:none');
		document.getElementById('response_content').setAttribute("style",'display:block');			
		document.getElementById('footer').setAttribute('style','display:block');
	} else {	
		if(resp.status.messageCode == "508") {
			html_text = "<p class='text-center' style='color:red'>No exact match found -- Verify that the given address is correct.</p>";
		} else {
			html_text = "<p class='text-center' style='color:red'>"+resp.status.messageText+"</p>";	
		}
		document.getElementById('response_error').innerHTML = html_text;
		document.getElementById('response_error').setAttribute('style','display:block');
		document.getElementById('response_content').setAttribute("style",'display:none');			
		document.getElementById('footer').setAttribute('style','display:none');			
	}
}

window.fbAsyncInit = function() {
	FB.init({
		appId      : '312599545590326',
		xfbml      : true,
		version    : 'v2.1'
	});
};

(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function shareOnFb(picture, url, address, lastSoldPrice, overAllChange) {
	FB.ui({
		method: 'feed',
		link: url,
		picture: picture,
		caption: 'Property Information from Zillow.com',
		description: 'Last Sold Price:'+lastSoldPrice+', 30 Days Overall Change:'+overAllChange,
		name: address,
	}, function(response) {
		if(response) {
			if(!response.error_code){
				alert("Posted Successfully");						
			} else {
				alert("Error while posting");
			}
		}
	});
}			
