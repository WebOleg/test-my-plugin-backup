<?php
/**
 * Woocommerce BMA Gateway
 *
 * @category 	'BNA Validate Credit Cards' Module
 * @version     1.0
 */

function luhn_check($number) {

	// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
	$number=preg_replace('/\D/', '', $number);
	
	// Set the string length and parity
	$number_length=strlen($number);
	$parity=$number_length % 2;
	
	// Loop through each digit and do the maths
	$total=0;
	for ($i=0; $i<$number_length; $i++) {
		$digit=$number[$i];
		// Multiply alternate digits by two
		if ($i % 2 == $parity) {
		$digit*=2;
		// If the sum is two digits, add them together (in effect)
		if ($digit > 9) {
			$digit-=9;
		}
		}
		// Total up the digits
		$total+=$digit;
	}
	
	// If the total mod 10 equals 0, the number is valid
	return ($total % 10 == 0) ? TRUE : FALSE;
	
}

function validateCC($cc_num, $type) 
{
	$pattern = null;
	switch ($type) {
		case "AMEX": 
			$pattern = "/^([34|37]{2})([0-9]{13})$/";//American Express
			break;
		case "Dinners": 
			$pattern = "/^([30|36|38]{2})([0-9]{12})$/";//Diner's Club
			break;
		case "Discover": 
			$pattern = "/^([6011]{4})([0-9]{12})$/";//Discover Card
			break;
		case "MASTERCARD": 
			$pattern = "/^([51|52|53|54|55]{2})([0-9]{14})$/";//Mastercard
			break;
		default:
			$pattern = "/^([4]{1})([0-9]{12,15})$/";//Visa
	}

	return preg_match($pattern,$cc_num) ? true : false;
}


function check_cc($cc_number, $extra_check = false)
{
	$cards = array(
		"VISA" => "(4\d{12}(?:\d{3})?)",
		"AMEX" => "(3[47]\d{13})",
		"jcb" => "(35[2-8][89]\d\d\d{10})",
		"maestro" => "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
		"solo" => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
		"MASTERCARD" => "^5[1-5][0-9]{0,14}|^(222[1-9]|2[3-6]\\d{2}|27[0-1]\\d|2720)[0-9]{0,12}", //"(5[1-5]\d{14})"
		"switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
	);
	$names = array("VISA", "AMEX", "JCB", "Maestro", "Solo", "MASTERCARD", "Switch");
	$matches = array();
	$pattern = "#^(?:".implode("|", $cards).")$#";
	$result = preg_match($pattern, str_replace(" ", "", $cc_number), $matches);
	if($extra_check && $result > 0){
		$result = (validatecard($cc_number)) ? 1 : 0;
	}

	return ($result>0) ? $names[sizeof($matches)-2] : false;
}
