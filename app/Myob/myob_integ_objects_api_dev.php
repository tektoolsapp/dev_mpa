<?php

namespace App\Myob;

use App\Myob\myobapi\myob_api_oauth;

ob_start();
//include_once('includes/setup_pg.php');
//$log = Logger::getLogger('TTDbLog');
//$log->info("ACCOUNTING OBJECTS API - Visited by: ".$id);
//include_once('myob_api/class.myob_oauth.php');
header('Content-Type: text/html; charset=utf-8');

$refresh_token = $_SESSION['refresh_token'];
$token_expiry = $_SESSION['token_expiry'];
$time_now = strtotime(date('h:i:s A', time()));

if(isset($_GET["object"])) {
    $object = $_GET["object"];
    $type = $_GET["type"];
    $skip_val = $_GET["skip"];
} elseif(isset($_POST["object"])) {
    $object = $_POST["object"];
    $type = $_POST["type"];
    $payload = $_POST["payload"];
}

//$object = $_GET["object"];
//$type = $_GET["type"];
//$skip_val = $_GET["skip"];

//echo "TYPE: ".$type;

if($_SESSION['accounts_integ_status'] == 'N') {
	$tmp_login = unserialize($_SESSION['comp_file_login']);
	$api_coy_un = $tmp_login[0];
	$api_coy_pw = $tmp_login[1];
}

if(empty($object)) logdie("object parameter is required");

//$log->info(sprintf( "entity_id: %s; HTTP: %s; Object: %s", $entity, $_SERVER['REQUEST_METHOD'], $object) );

//SETUP LOCAL API  ACCESS
//if($site_version == 'local') {
	$api_company_file = '92966d03-4326-420c-b582-9b141360bbe2';
	$api_url = 'http://10.211.55.3:8080/AccountRight/';
/*
} else {
	$api_company_file = $api_company_file;
	$api_url = $api_url;		
}
*/

switch( $object ) {

	case "info":
		
		include_once('myob_api/api_functions.php');
		
		if($site_version == 'local') {
			
			stream_context_set_default(
				array(
					'http' => array(
						'timeout' => 5
					)
				)
			);
			
			$file_headers = get_headers($api_url);
			
			//print_r($file_headers);
			
			if(empty($http_response_header[0])) {
				
				$response = "no_connection";
			
			} else {
				
				$url_extension = '/Company';
				$this_url = $api_url.$api_company_file.$url_extension;
				$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
				
				//echo " -TURL: ".$this_url;
				//echo " -API KEY: ".$api_key;
				//echo " - UN: ".$api_coy_un;
				//echo " - PW: ".$api_coy_pw;
			
			}
			
		} else {
		
			if($time_now >= $token_expiry) {
				checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
			}
			
			$url_extension = '/Company';
			$this_url = $api_url.$api_company_file.$url_extension;
			$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
			
		}
	
	break;

	case "companyfile":
		
		include_once('myob_api_functions.php');
		
		if($site_version != 'local') {
		
			if($time_now >= $token_expiry) {
				checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
			}
			
			$url_extension = '?$filter=Id%20eq';	
			$filter_field = "%20guid'".$api_company_file."'";
			$this_url = $api_url.$url_extension.$filter_field;
			
			$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
			
		} else {
			
			stream_context_set_default(
				array(
					'http' => array(
						'timeout' => 5
					)
				)
			);
			
			$file_headers = get_headers($api_url);
			
			if(empty($http_response_header[0])) {
				$response = "no_connection";
			} else {
				
				$url_extension = '?$filter=Id%20eq';	
				$filter_field = "%20guid'".$api_company_file."'";
				$this_url = $api_url.$url_extension.$filter_field;
				
				$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
			
			}
		
		}
		
	break;
	
	case "getCustomer":
		
		include_once('mayb_api_functions.php');
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
	
		$this_guid = base64_decode($_GET['ref']);
		$url_extension = '/Contact/Customer?$filter=UID%20eq';
		$filter_field = "%20guid'".$this_guid."'";	
		$this_url = $api_url.$api_company_file.$url_extension.$filter_field;
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
	break;
	
	case "getPayments":
		
		include_once('myob_api_functions.php');
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$invoice_ref = $_GET['invoice'];	
		//PAD FOR MYOB INVOICE STRING LENGTH
		$invoice_ref = str_pad($invoice_ref, 8, "0", STR_PAD_LEFT);
		
		$filter_field = "Invoices/any(a:%20a/Number%20eq%20'".$invoice_ref."')";
		$url_extension = '/Sale/CustomerPayment?$filter=';
		$this_url = $api_url.$api_company_file.$url_extension.$filter_field;
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);	
	
	break;
	
	case "getSupplier":
		
		include_once('myob_api/api_functions.php');
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
	
		$this_guid = base64_decode(urldecode($_GET['ref']));
		//$this_guid = base64_decode($_GET['ref']);
		
		//$this_guid = '8cb70e61-64d3-46e2-9109-7ffdc1a91e90';
		$url_extension = '/Contact/Supplier?$filter=UID%20eq';
		$filter_field = "%20guid'".$this_guid."'";	
		
		//?$filter=CompanyName eq 'ACCESS RENTALS AUSTRALIA'
		
		//$url_extension = '/Contact/Supplier?$filter=CompanyName%20eq';
		//$filter_field = "%20'First Supplier'";	
		
		$this_url = $api_url.$api_company_file.$url_extension.$filter_field;
		
		//echo $this_url;
		
		//$response = $this_url;
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
	
	break;
	
	case "filterJob":
		
		include_once('myob_api/api_functions.php');
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
	
		$this_job_num = $_GET['ref'];
		$url_extension = '/GeneralLedger/Job?$filter=Number%20eq%20';
		$filter_field = "'".$this_job_num."'";	
		$this_url = $api_url.$api_company_file.$url_extension.$filter_field;
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
	
	break;
		
	case "getsuppliers":
		
		include_once('myob_api/api_functions.php');
		$url_extension = '/Contact/Supplier?$top=1000';
		//check for access token expiry	
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		/*
		if($site_version == 'local') {
			$api_company_file = $_SESSION['myob_company_file'];
			$api_url = 'http://10.211.55.7:8080/AccountRight/';
		} else {
			$api_company_file = $api_company_file;
			$api_url = $api_url;		
		}
		*/
		
		$this_url = $api_url.$api_company_file.$url_extension;

		if($_SESSION['accounts_integ_status'] == 'N') {
			$tmp_login = unserialize($_SESSION['comp_file_login']);
			$api_coy_un = $tmp_login[0];
			$api_coy_pw = $tmp_login[1];
		}
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
	break;
		
	case "getinventory":
		
		include_once('myob_api/api_functions.php');
		$url_extension = '/Inventory/Item';
		//check for access token expiry	
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;

		if($_SESSION['accounts_integ_status'] == 'N') {
			$tmp_login = unserialize($_SESSION['comp_file_login']);
			$api_coy_un = $tmp_login[0];
			$api_coy_pw = $tmp_login[1];
		}
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
	break;	
		
	case "getjobs":
		
		include_once('myob_api/api_functions.php');
		$url_extension = '/GeneralLedger/Job';
		//check for access token expiry	
		
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
	break;	
		
	case "getcustomers":
		
		include_once('myob_api/api_functions.php');
		//$url_extension = "/Contact/Customer?$filter=IsActive%20eq%20true";
		
		//$url_extension = "/Contact/Customer";
		//$url_extension = '/Contact/Customer?$top=2000';
		
		if(isset($skip_val)) {
				$url_extension = '/Contact/Customer?$top=400&$skip='.$skip_val;
		} else {
				$url_extension = "/Contact/Customer";
		}
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		if($site_version == 'local') {
			$api_company_file = $_SESSION['myob_company_file'];
			$api_url = 'http://10.211.55.7:8080/AccountRight/';
		} else {
			$api_company_file = $api_company_file;
			$api_url = $api_url;		
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;	
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
		/*
		if(isset($skip)) {
		
			//$response = $url_extension;
			$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
			
		} else {
			$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		}
		*/
		
	break;	
	
	case "getalljobs":
		
		include_once('myob_api/api_functions.php');
		
		if(isset($skip_val)) {
				$url_extension = '/GeneralLedger/Job?$top=400&$skip='.$skip_val;
		} else {
				$url_extension = "/GeneralLedger/Job";
		}
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		if($site_version == 'local') {
			$api_company_file = $_SESSION['myob_company_file'];
			$api_url = 'http://10.211.55.7:8080/AccountRight/';
		} else {
			$api_company_file = $api_company_file;
			$api_url = $api_url;		
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;	
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
	break;	
		
	case "getemployees":
		
		include_once('myob_api/api_functions.php');
		$url_extension = "/Contact/Employee";
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
		break;
		
	case "getemployeespayroll":
		
		include_once('myob_api/api_functions.php');
		$url_extension = "/Contact/EmployeePayrollDetails";
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
	break;	
		
	case "getglaccounts":
		
		include_once('myob_api/api_functions.php');
			
		$filter_pre = '/GeneralLedger/Account';

		$filter_type = '?$filter=Classification%20eq';

		$classification = $_GET['classification_filter'];
		
		//$other_ref = "&$filter=IsHeader%20eq%20'0";
			
		$filter_var = "%20'".$classification."'";
		
		$url_extension = $filter_pre.$filter_type.$filter_var;
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		
		//$response = $this_url;
	
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
		
		break;	
		
	case "postjobs":
		
		include_once('myob_api/api_functions.php');
			
		//echo "Reached Function";
		
		//$post_data = json_decode($_GET['payload'], true);
	
		//$response = json_decode($_GET['payload'], true);
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		//$post_data = json_decode($_GET['payload'], true);
		
		//$post_data = json_decode($_GET['payload'],JSON_HEX_APOS);
		
		//echo $_GET['payload'];
		
		$post_data = json_decode($_GET['payload'], true);
		
		//$post_data = array();
		
		//$post_data[] = $post_data;
		
		//$reponse = print_r($post_data);
		
		$posting_array = array();
		
		for($p = 0; $p < sizeof($post_data); $p++) {
			
			$this_post_data = $post_data[$p];	
			$this_job_uid = $post_data[$p]['UID'];
			$this_job_num = $post_data[$p]['Number'];
			$this_job_type = $post_data[$p]['IsHeader'];
			$this_job_name = urldecode($post_data[$p]['Name']);
			$this_job_desc = urldecode($post_data[$p]['Description']);		
			$this_job_row = $post_data[$p]['RowVersion'];	
			
			//$response = "TJIUD: ".$this_job_uid;
			
			if(!empty($this_job_uid) && $this_job_uid == 'tba') {
				
				//echo "NEW JOB";
				
				//$response =  "NEW JOB";
			
				$posting_array_type = array();			
				$posting_array_type['Number'] = $this_job_num;
				$posting_array_type['IsHeader'] = $this_job_type;
				$posting_array_type['Name'] = $this_job_name;
				$posting_array_type['Description'] = $this_job_desc;
			
				$filter_pre = '/GeneralLedger/Job?returnBody=true';
				$url_extension = $filter_pre;
				$this_url = $api_url.$api_company_file.$url_extension;
				
				$show_post_data = json_encode($posting_array_type,JSON_HEX_APOS);
				
				$show_post_data = json_encode($posting_array_type);
				
				$response = postURL($this_url, $show_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);		
				
				//echo "RESP NEW: ".$response;
				
				$return_vars = json_decode($response, true);			
				$trx_uid = $return_vars['UID'];
				$trx_row = $return_vars['RowVersion'];	
				if(isset($trx_uid) && !empty($trx_uid)) {
					array_push($posting_array, array(job_uid => $trx_uid, job_row => $trx_row, job_num => $this_job_num));
				} else {
					array_push($posting_array, array(job_uid => "Error", job_num => $this_job_num));
				}
		
			} elseif(!empty($this_job_uid) && $this_job_uid != 'tba') {			
				
				//$response =  "EDIT JOB";
								
				$this_post_data = $post_data[$p];	
				$this_job_uid = $post_data[$p]['UID'];
				$this_job_num = $post_data[$p]['Number'];
				$this_job_name = urldecode($post_data[$p]['Name']);
				$this_job_desc = urldecode($post_data[$p]['Description']);		
				$this_job_row = $post_data[$p]['RowVersion'];	
				
				$posting_array_type = array();
				$posting_array_type['UID'] = $this_job_uid;
				$posting_array_type['Number'] = $this_job_num;
				$posting_array_type['Name'] = $this_job_name;
				$posting_array_type['Description'] = $this_job_desc;
				$posting_array_type['RowVersion'] = $this_job_row;
				
				$filter_pre = '/GeneralLedger/Job/'.$this_job_uid.'?returnBody=true';
				$url_extension = $filter_pre;				
				$this_url = $api_url.$api_company_file.$url_extension;
				
				$show_post_data = json_encode($posting_array_type,JSON_HEX_APOS);
				
				$response = putURL($this_url, $show_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
				$return_vars = json_decode($response, true);			
				$trx_uid = $return_vars['UID'];
				$trx_row = $return_vars['RowVersion'];	
				
				if(isset($trx_uid) && !empty($trx_uid)) {
					array_push($posting_array, array(job_uid => $trx_uid, job_row => $trx_row, job_num => $this_job_num));
				} else {
					array_push($posting_array, array(job_uid => "Error", job_num => $this_job_num));
				}		
				
				
				//$response = monitorURL($this_job_num);
				
				//echo "RESP EDIT: ".$response;
							
			} else {
				
				//no GUI present or set to tba
				$this_job_num = $post_data[$p]['Number'];
				
				//echo "RESP ERR: ".$this_job_num;
				
				array_push($posting_array, array(job_uid => "Error", job_num => $this_job_num));
				
				//$reponse = "ERROR";
				
			}
		
		}
	
		if(!empty($posting_array)) {
			$_SESSION['job_posted_array'] = serialize($posting_array);
			unset($posting_array);
		}
					
	break;	
	
	case "putCustomer":
		
	include_once('myob_api/api_functions.php');
			
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		//echo "EDIT CUSTOMER";
		
		$post_mode = $_GET['mode'];
		$post_data = $_GET['payload'];
		
		if($post_mode == 'add') {
		
			//echo "ADD CUSTOMER";
			
			$filter_pre = '/Contact/Customer?returnBody=true';
			$url_extension = $filter_pre;				
			$this_url = $api_url.$api_company_file.$url_extension;
			
			//$show_post_data = json_encode($post_data, JSON_HEX_APOS);
			
			//$response = putURL($this_url, $post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
			$response = postURL($this_url, $post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
			$response = json_encode($response);	
		
		} else {
		
			$this_job_uid = $_SESSION['customer_uid'];
			
			//$this_job_uid = '4b4a76c8-6c34-4031-b541-49f603b570a9';
					
			$filter_pre = '/Contact/Customer/'.$this_job_uid.'?returnBody=true';
			$url_extension = $filter_pre;				
			$this_url = $api_url.$api_company_file.$url_extension;
			$response = putURL($this_url, $post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);	
			$response = json_encode($response);
				
		}
			
		//$response = $post_data;
		
	break;
	
	case "putSupplier":
		
	include_once('myob_api/api_functions.php');
			
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		//echo "EDIT CUSTOMER";
		
		$post_mode = $_GET['mode'];
		$post_data = $_GET['payload'];
		
		if($post_mode == 'add') {
		
			//echo "ADD CUSTOMER";
			
			$filter_pre = '/Contact/Supplier?returnBody=true';
			$url_extension = $filter_pre;				
			$this_url = $api_url.$api_company_file.$url_extension;
			
			//$show_post_data = json_encode($post_data, JSON_HEX_APOS);
			
			//$response = putURL($this_url, $post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
			$response = postURL($this_url, $post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
			$response = json_encode($response);	
		
		} else {
		
			$this_job_uid = $_SESSION['supplier_uid'];
			
			//$this_job_uid = '4b4a76c8-6c34-4031-b541-49f603b570a9';
					
			$filter_pre = '/Contact/Supplier/'.$this_job_uid.'?returnBody=true';
			$url_extension = $filter_pre;				
			$this_url = $api_url.$api_company_file.$url_extension;
			$response = putURL($this_url, $post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);	
			$response = json_encode($response);
				
		}
			
		//$response = $post_data;
		
	break;
	
	case "postgljournal":
		
		include_once('myob_api/api_functions.php');
			
		$post_data = json_decode($_GET['payload'], true);
		//$post_data = $_GET['payload'];
		$filter_pre = '/GeneralLedger/GeneralJournal?returnBody=true';
		$url_extension = $filter_pre;
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		//$this_post_data = $post_data[$p];	
		$this_post_data = $post_data;	
		if($type == 'post') {	
			$use_post_data = json_encode($this_post_data,JSON_HEX_APOS);
			$response = postURL($this_url, $use_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);	
			//$response = "RESP POST: ".$post_data;
				
		} elseif($type == 'put') {
			//TO DO - ADD EDIT PURCHASE FUNCTIONALITY
		}
	
	break;	
	
	case "postservpurchase":
		
		include_once('myob_api/api_functions.php');
			
		$post_data = json_decode($_GET['payload'], true);
		//$post_data = $_GET['payload'];
		$filter_pre = '/Purchase/Bill/Service?returnBody=true';
		$url_extension = $filter_pre;
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		//$this_post_data = $post_data[$p];	
		$this_post_data = $post_data;	
		if($type == 'post') {	
			$use_post_data = json_encode($this_post_data,JSON_HEX_APOS);
			$response = postURL($this_url, $use_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);	
			//$response = "RESP POST: ".$post_data;
				
		} elseif($type == 'put') {
			//TO DO - ADD EDIT PURCHASE FUNCTIONALITY
		}
	
	break;	
	
	case "postservsale":
		
		include_once('myob_api/api_functions.php');
			
		//$post_data = json_decode($_GET['payload'], true);
        $post_data = json_decode($_POST['payload'], true);
		$filter_pre = '/Sale/Invoice/Service?returnBody=true';
		$url_extension = $filter_pre;
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
		
		$update_refs = array();
		$update_lines = array();
		$this_post_data = $post_data[$p];	
		$this_post_data = $post_data;
		
		if($type == 'post') {	
			$use_post_data = json_encode($this_post_data,JSON_HEX_APOS);
			$response = postURL($this_url, $use_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);		
			/*
			$batch_num = $_SESSION['order_batch_num'];
			$use_post_data = json_encode($this_post_data,JSON_HEX_APOS);
			$response = postURL($this_url, $use_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
			$resp_vals = json_decode($response, true);
			$trx_uid = $resp_vals['UID'];
			$purchase_num = $resp_vals['Number'];
			$use_supplier_id = $resp_vals['Supplier']['UID'];		
			$update_lines = $resp_vals['Lines'];
			//print_r($update_lines);
			for($l = 0; $l < sizeof($update_lines); $l++) {	
				if(!empty($trx_uid)) {
					$item_desc = $update_lines[$l]['Description'];
					$use_row_id = substr($item_desc,0,7);	
					//$from = "Ref: ";
					//$to = " Batch:";
					//$use_row_id = trim(getStringBetween($resp_vals['Comment'],$from,$to));					
					//build the update arrays
					if(!empty($trx_uid)) {
						array_push($update_refs, array(the_batch => $batch_num, integ_num => $purchase_num, the_ref => $use_row_id, supplier_id  => $use_supplier_id, the_entity => $entity));
					} else {				
						//build error array
						array_push($error_refs, array(the_batch => $batch_num, the_ref => $use_row_id, the_entity => $entity));
					}
				}
			} //close the lines for
			//reset the lines array
			$update_lines = array();
			*/
		} elseif($type == 'put') {
			//TO DO - ADD EDIT SALE FUNCTIONALITY
		}

	break;		
		
	case "postinventoryadj":
		
		include_once('myob_api/api_functions.php');
			
		//$use_post_data = json_encode($_GET['payload'],JSON_HEX_APOS);
		$use_post_data =$_GET['payload'];
		
		//$use_post_data = json_decode($_GET['payload'], true);
		$filter_pre = '/Inventory/Adjustment?returnBody=true';
		$url_extension = $filter_pre;
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$this_url = $api_url.$api_company_file.$url_extension;
			
		$response = postURL($this_url, $use_post_data, '', '', $api_key, $api_coy_un, $api_coy_pw);
	
		break;	
		
	case "getinventoryitem":
		
		include_once('myob_api/api_functions.php');
			
		$this_post_uid = $_GET['payload'];
		$row_base = '/Inventory/Item?$filter=UID%20eq';	
		$row_field = "%20guid'".$this_post_uid."'";	
		$this_url = $api_url.$api_company_file.$row_base.$row_field;	
		
		//check for access token expiry	
		if($time_now >= $token_expiry) {
			checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token);
		}
		
		$response = getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
	
	break;		
		
	default:
		logdie("error: unsupported object type");
	break; 
}

echo $response;
//$log->trace("JSON response: $response");