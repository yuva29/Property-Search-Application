<?php

$street = isset($_GET['street']) ? $_GET['street'] : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';   
$state = isset($_GET['state']) ? $_GET['state'] : ''; 
$zws_id = 'X1-ZWz1b2yek9wjrf_7aoby';

//Replace spaces by '+'
$street = preg_replace("/[\s]+/", "+", $street);
$city = preg_replace("/[\s]+/", "+", $city);
date_default_timezone_set('America/Los_Angeles');

//Request to Zillow
$url = 'http://www.zillow.com/webservice/GetDeepSearchResults.htm?';
$request = $url."zws-id=".$zws_id."&address=".$street."&citystatezip=".$city.','.$state."&rentzestimate=true";
$response = simplexml_load_file($request);

$queryResult = array();
if(!empty($response)) {
    if($response->message->code == 0) {
		$queryResult['status']['text'] = 'success';
        $result = $response->response->results->result;
		getHomeDetails($result);
		getHistoricalData("1year");
		getHistoricalData("5years");
		getHistoricalData("10years");
    } else { // Error handling
		$queryResult['status']['messageCode'] = (string)$response->message->code;
		$queryResult['status']['messageText'] = (string)$response->message->text;
    }
} else {
    $queryResult['status']['text'] = 'failure';
}

function getHistoricalData($duration) {
	global $zws_id;
	global $queryResult;
	$url = 'http://www.zillow.com/webservice/GetChart.htm?';
	$request = $url."zws-id=".$zws_id."&zpid=".$queryResult['homeDetails']['zpid']."&unit-type=percent&width=600&height=300&chartDuration=".$duration;
	$response = simplexml_load_file($request);
	if(!empty($response) && $response->message->code == 0){
		$queryResult['chart'][$duration]['url'] = (string)$response->response->url; 
	}else{
		$queryResult['status']['image'] = $response->message->text;
	}
}

function getHomeDetails($result){
	global $queryResult;
	$queryResult['homeDetails']['zpid']	= (string)$result->zpid;
	$queryResult['homeDetails']['header_url'] = ($result->links && $result->links->homedetails && strlen(trim($result->links->homedetails))>0) ? (string)$result->links->homedetails : (string)'N/A';
	$queryResult['homeDetails']['property_type'] = ($result->useCode && strlen(trim($result ->useCode))>0) ? (string)$result->useCode : (string)'N/A';
	$queryResult['homeDetails']['year_built'] = ($result->yearBuilt && strlen(trim($result->yearBuilt))>0) ? (string)$result->yearBuilt : (string)'N/A';
	$queryResult['homeDetails']['lot_size'] = ($result->lotSizeSqFt && strlen(trim($result->lotSizeSqFt))>0) ? $result->lotSizeSqFt : 'N/A';
	$queryResult['homeDetails']['finished_area'] = ($result->finishedSqFt && strlen(trim($result->finishedSqFt))>0) ? $result->finishedSqFt : 'N/A';
	$queryResult['homeDetails']['bathrooms'] = ($result->bathrooms && strlen(trim($result->bathrooms))>0) ? (string)$result->bathrooms : (string)'N/A';
	$queryResult['homeDetails']['bedrooms'] = ($result->bedrooms && strlen(trim($result->bedrooms))>0) ? (string)$result->bedrooms : (string)'N/A';
	$queryResult['homeDetails']['tax_assessment_yr'] = ($result->taxAssessmentYear && strlen(trim($result->taxAssessmentYear))>0) ? (string)$result->taxAssessmentYear : (string)'N/A';
	$queryResult['homeDetails']['tax_assessment'] = ($result->taxAssessment && strlen(trim($result->taxAssessment))>0) ? $result->taxAssessment : 'N/A';
	$queryResult['homeDetails']['last_sold_price'] = ($result->lastSoldPrice && strlen(trim($result->lastSoldPrice))>0) ? $result->lastSoldPrice : 'N/A';
	$queryResult['homeDetails']['last_sold_date'] = ($result->lastSoldDate && strlen(trim($result->lastSoldDate))>0) ? $result->lastSoldDate : 'N/A';
	$queryResult['homeDetails']['overall_change'] = ($result->zestimate && $result->zestimate->valueChange && strlen(trim($result->zestimate->valueChange))>0) ? $result->zestimate->valueChange : 'N/A';
	$queryResult['homeDetails']['zestimate_valuation_low'] = ($result->zestimate && $result->zestimate->valuationRange && $result->zestimate->valuationRange->low && strlen(trim($result->zestimate->valuationRange->low))>0) ?  $result->zestimate->valuationRange->low : 'N/A';
	$queryResult['homeDetails']['zestimate_valuation_high'] = ($result->zestimate && $result->zestimate->valuationRange && $result->zestimate->valuationRange->high && strlen(trim($result->zestimate->valuationRange->high))>0) ? $result->zestimate->valuationRange->high : 'N/A';
	$queryResult['homeDetails']['rent_change'] = ($result->rentzestimate && $result->rentzestimate->valueChange && strlen(trim($result->rentzestimate->valueChange))>0) ? $result->rentzestimate->valueChange : 'N/A';
	$queryResult['homeDetails']['rentzestimate_valuation_low'] = ($result->rentzestimate && $result->rentzestimate->valuationRange && $result->rentzestimate->valuationRange->low && strlen(trim($result->rentzestimate->valuationRange->low))>0) ? $result->rentzestimate->valuationRange->low : 'N/A';
	$queryResult['homeDetails']['rentzestimate_valuation_high'] =($result->rentzestimate && $result->rentzestimate->valuationRange && $result->rentzestimate->valuationRange->high && strlen(trim($result->rentzestimate->valuationRange->high))>0) ? $result->rentzestimate->valuationRange->high : 'N/A';
	$queryResult['homeDetails']['zestimate_last_updated'] = $result->zestimate->{'last-updated'};
	$queryResult['homeDetails']['rentzestimate_last_updated'] = $result->rentzestimate->{'last-updated'};    
	$queryResult['homeDetails']['zestimate_amount'] = ($result->zestimate && $result->zestimate->amount && strlen(trim($result->zestimate->amount))>0) ? $result->zestimate->amount : 'N/A';
	$queryResult['homeDetails']['rentzestimate_amount'] = ($result->rentzestimate && $result->rentzestimate->amount && strlen(trim($result->rentzestimate->amount))>0) ? $result->rentzestimate->amount : 'N/A';
	//Address Details
	$queryResult['homeDetails']['street'] = (string)$result->address->street;
	$queryResult['homeDetails']['state'] = (String)$result->address->state;
	$queryResult['homeDetails']['city'] = (string)$result->address->city;
	$queryResult['homeDetails']['zipcode'] = (string)$result->address->zipcode;
	//Header
	$queryResult['homeDetails']['header_text'] = $queryResult['homeDetails']['street'].", ".$queryResult['homeDetails']['city'].", ".$queryResult['homeDetails']['state']."-".$queryResult['homeDetails']['zipcode'];		
	//Formatting
	$queryResult['homeDetails']['img_overall_change'] = '';
	if($queryResult['homeDetails']['overall_change']!='N/A') { 
		$queryResult['homeDetails']['img_overall_change'] = ($queryResult['homeDetails']['overall_change'] >0) ? "<img src='http://www-scf.usc.edu/~csci571/2014Spring/hw6/up_g.gif'/>" : "<img src='http://www-scf.usc.edu/~csci571/2014Spring/hw6/down_r.gif'/>";
		$queryResult['homeDetails']['overall_change'] = abs($queryResult['homeDetails']['overall_change']);
	}
	$queryResult['homeDetails']['img_rent_change'] = '';
	if($queryResult['homeDetails']['rent_change'] != 'N/A') {
		$queryResult['homeDetails']['img_rent_change'] = ($queryResult['homeDetails']['rent_change']>0) ? "<img src='http://www-scf.usc.edu/~csci571/2014Spring/hw6/up_g.gif'/>" : "<img src='http://www-scf.usc.edu/~csci571/2014Spring/hw6/down_r.gif'/>";
		$queryResult['homeDetails']['rent_change'] = abs($queryResult['homeDetails']['rent_change']);
	}
	
	//Thousand seperator
	if($queryResult['homeDetails']['lot_size'] != 'N/A') $queryResult['homeDetails']['lot_size'] = number_format((double)$queryResult['homeDetails']['lot_size'],0,'',',') . " sq.ft.";
	if($queryResult['homeDetails']['finished_area'] != 'N/A') $queryResult['homeDetails']['finished_area'] = number_format((double)$queryResult['homeDetails']['finished_area'],0,'',',') . " sq.ft.";    
	if($queryResult['homeDetails']['last_sold_price'] != 'N/A') $queryResult['homeDetails']['last_sold_price'] = '$'.number_format((double)$queryResult['homeDetails']['last_sold_price'],2,'.',',');
	if($queryResult['homeDetails']['tax_assessment'] != 'N/A') $queryResult['homeDetails']['tax_assessment'] = '$'.number_format((double)$queryResult['homeDetails']['tax_assessment'],2,'.',',');
	if($queryResult['homeDetails']['overall_change'] != 'N/A') $queryResult['homeDetails']['overall_change'] = '$'.number_format((double)$queryResult['homeDetails']['overall_change'],2,'.',',');
	if($queryResult['homeDetails']['rent_change'] != 'N/A') $queryResult['homeDetails']['rent_change'] = '$'.number_format((double)$queryResult['homeDetails']['rent_change'],2,'.',',');   
	if($queryResult['homeDetails']['zestimate_valuation_low'] != 'N/A') $queryResult['homeDetails']['zestimate_valuation_low'] = '$'. number_format((double)$queryResult['homeDetails']['zestimate_valuation_low'],2,'.',',');
	if($queryResult['homeDetails']['zestimate_valuation_high'] != 'N/A') $queryResult['homeDetails']['zestimate_valuation_high'] = '$'.number_format((double)$queryResult['homeDetails']['zestimate_valuation_high'], 2,'.',',');
	if($queryResult['homeDetails']['rentzestimate_valuation_low'] != 'N/A') $queryResult['homeDetails']['rentzestimate_valuation_low'] = '$'. number_format((double)$queryResult['homeDetails']['rentzestimate_valuation_low'], 2,'.',',');         
	if($queryResult['homeDetails']['rentzestimate_valuation_high'] != 'N/A') $queryResult['homeDetails']['rentzestimate_valuation_high'] = '$'.number_format((double)$queryResult['homeDetails']['rentzestimate_valuation_high'], 2,'.',',');
	if($queryResult['homeDetails']['zestimate_amount'] != 'N/A') $queryResult['homeDetails']['zestimate_amount'] = '$'.number_format((double)$queryResult['homeDetails']['zestimate_amount'],2,'.',',');        
	if($queryResult['homeDetails']['rentzestimate_amount'] != 'N/A') $queryResult['homeDetails']['rentzestimate_amount'] = '$'.number_format((double)$queryResult['homeDetails']['rentzestimate_amount'],2,'.',',');    

	$queryResult['homeDetails']['property_range'] = $queryResult['homeDetails']['zestimate_valuation_low'] ." - ". $queryResult['homeDetails']['zestimate_valuation_high'];
	$queryResult['homeDetails']['all_time_rent_range'] = $queryResult['homeDetails']['rentzestimate_valuation_low'] ." - ". $queryResult['homeDetails']['rentzestimate_valuation_high'];    
	//Date conversions
	if($queryResult['homeDetails']['last_sold_date']!='N/A') $queryResult['homeDetails']['last_sold_date'] = date("d-M-Y",strtotime($queryResult['homeDetails']['last_sold_date']));
	if($queryResult['homeDetails']['zestimate_last_updated']!='N/A') $queryResult['homeDetails']['zestimate_last_updated'] = date("d-M-Y",strtotime($queryResult['homeDetails']['zestimate_last_updated']));
	if($queryResult['homeDetails']['rentzestimate_last_updated']) $queryResult['homeDetails']['rentzestimate_last_updated'] =  date("d-M-Y",strtotime($queryResult['homeDetails']['rentzestimate_last_updated']));	
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

echo json_encode($queryResult, JSON_PRETTY_PRINT);
// echo json_encode($queryResult);
?>