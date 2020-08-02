<?php
// Exit if accessed directly

add_action('gform_after_submission', 'gf_to_ygl', 10, 2);

function gf_to_ygl($entry, $form) {
	
	// replace with the Gravity Form form ID
	$form_id = 1;
	
	if ($form['id'] == $form_id) {
		
		// replace the $entry[x] value with the related Gravity Form form field ID
		$firstName = (isset($entry['1'])) ? $entry['1'] : '';
		$lastName = (isset($entry['2'])) ? $entry['2'] : '';
		$email = (isset($entry['3'])) ? $entry['3'] : '';
		$phone = (isset($entry['4'])) ? $entry['4'] : '';
		// find out which community
		// if there is no value (somehow) then set a default
		// if there is only one community then this can be commented out, and the $community_id value can be set below
		$community = (isset($entry['5'])) ? $entry['5'] : '';

		// replace the following values with those related to your YGL account	
		$base_url = 'https://www.youvegotleads.com/api/properties/';
		$username = 'username';
		$password = "password";
		$lead_source_name = "Web-Form Lead";
		$lead_source_id = "xxxxx";
		$lead_source_rank ="x";

		// set the community id based on user selected community
		// the case value should be the same value as the key of the drop down option
		// you can add or delete cases to match your particular configuration
		switch  ($community) {
			case 'one':
				$community_id = '1111111';
				break;
			case 'two':
				$community_id = '2222222';
				break;
			case 'three':
				$community_id = '3333333';
				break;	
			default:
				$community_id = 'fail';
		}
		
		// if you have only one community then remove the switch statement above, and then comment out the varible below
		// don't forget to set the Community ID to the correct value
		// $community_id = '1111111'

		
		if (($email && is_email($email)) && $community_id !== 'fail') {
			
			$post_json = json_encode(
				array(
					"ReferralSources" => array(
						array(
							"LeadSourceName" => $lead_source_name,
							"LeadSourceId" => $lead_source_id,
							"LeadSourceRank" => $lead_source_rank,
						),
					),
					"PrimaryContact" => array(
						"FirstName" => $firstName,
						"LastName" => $lastName,
						"Address" => array(
							"Email" => $email,
							"PhoneHome" => $phone,
						),
					),
				)
			);
            
			$request = new WP_Http();
		    $auth_key = $username . ':' . $password;
			$encode_key = base64_encode($auth_key);
			$post_url = rtrim($base_url, '/') . '/' . $community_id . '/leads';
			$headers = array (
				'Authorization' => 'BASIC ' . $encode_key,
				'Accept' => 'application/json',
				'Content-type' => 'application/json'

			);

			$em_connect = array (
                'method' => 'POST',
                'timeout' => 15,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => $headers,
                'body' => $post_json,
				'cookies' => array()
			);

			$response = wp_remote_post( $post_url, $em_connect );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				$message = 'Error Sending Form 24 from frunctions file. The query was ' . $post_json . ' The Response was: ' . $error_message;
			} else {
				$message = 'Form 24 sent to YGL without error using theme functions file.';
			}
			
			log_me($message);
		}
	}
} 

function log_me($message) {
    if ( WP_DEBUG === true ) {
        if ( is_array($message) || is_object($message) ) {
            error_log( print_r($message, true) );
        } else {
            error_log( $message );
        }
    }
}
