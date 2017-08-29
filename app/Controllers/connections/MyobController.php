<?php
namespace App\Controllers\Connections;

use App\Models\Stakeholders;
use Slim\Router;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages as Flash;
use App\Validation\Contracts\ValidatorInterface;
use App\Models\Members;
use App\Myob\MyobApi;

class MyobController
{
    protected $router;
    protected $validator;
    protected $flash;

    public function __construct(Router $router, ValidatorInterface $validator, Flash $flash)
    {
        $this->router = $router;
        $this->validator = $validator;
        $this->flash = $flash;
    }

    public function connect(Request $request, Response $response, MyobApi $myob)
    {
        $api_url = getenv('API_URL');
        $api_company_file = getenv('API_COMPANY_FILE');
        $api_key = getenv('API_KEY');
        $api_coy_un = getenv('API_COY_UN');
        $api_coy_pw = getenv('API_COY_PW');
        $url_extension = '/Company';
        $this_url = $api_url.$api_company_file.$url_extension;

        return $myob->getUrl($this_url, "", "", $api_key, $api_coy_un, $api_coy_pw);
    }

    public function getCustomer($get, Request $request, Response $response, MyobApi $myob)
    {
        $update_params = $request->getQueryParams();

        if (isset($update_params['custUID'])) {
            $custGuid = $update_params['custUID'];
        } else {
            $custGuid = null;
        }

        $api_url = getenv('API_URL');
        $api_company_file = getenv('API_COMPANY_FILE');
        $api_key = getenv('API_KEY');
        $api_coy_un = getenv('API_COY_UN');
        $api_coy_pw = getenv('API_COY_PW');
        $this_guid = base64_decode($custGuid);
        $url_extension = '/Contact/Customer?$filter=UID%20eq';
        $filter_field = "%20guid'".$this_guid."'";
        $this_url = $api_url.$api_company_file.$url_extension.$filter_field;

        return $myob->getURL($this_url, '', '', $api_key, $api_coy_un, $api_coy_pw);
    }

    public function upDateCustomer($update, Request $request, Response $response, Members $members, Stakeholders $stakeholders, MyobApi $myob)
    {
        $update_params = $request->getQueryParams();

        if (isset($update_params['type'])) {
            $type = $update_params['type'];
        } else {
            $type = null;
        }

        if (isset($update_params['mode'])) {
            $mode = $update_params['mode'];
        } else {
            $mode = null;
        }

        //dump("GET MODE: ", $mode);

        if (isset($update_params['payload'])) {
            $payload = $update_params['payload'];
        } else {
            $payload = null;
        }

        $api_url = getenv('API_URL');
        $api_company_file = getenv('API_COMPANY_FILE');
        $api_key = getenv('API_KEY');
        $api_coy_un = getenv('API_COY_UN');
        $api_coy_pw = getenv('API_COY_PW');

        if ($mode == 'add') {

            $filter_pre = '/Contact/Customer?returnBody=true';
            $url_extension = $filter_pre;
            $this_url = $api_url . $api_company_file . $url_extension;
            $myob_update = $myob->postURL($this_url, $payload, $username=NULL, $password=NULL, $api_key, $api_coy_un, $api_coy_pw);

        } elseif($mode == 'edit' || $mode == 'invoice') {

            $this_job_uid = $_SESSION['customer_uid'];
            $filter_pre = '/Contact/Customer/'.$this_job_uid.'?returnBody=true';
            $url_extension = $filter_pre;
            $this_url = $api_url.$api_company_file.$url_extension;
            $myob_update = $myob->putURL($this_url, $payload, '', '', $api_key, $api_coy_un, $api_coy_pw);
        }

        $myob_update = json_decode($myob_update);

        //dump("MYOB: " + $myob_update);

        if(!isset($myob_update->Errors)) {
            if($type == 'M') {
                $members->where('id', $_SESSION['update_myob_member'])
                    ->update([
                        'myob_uid' => $myob_update->UID,
                        'myob_row' => $myob_update->RowVersion,
                        'myob_integ_status' => 'Y'
                    ]);
            } elseif($type == 'S') {
                $stakeholders->where('id', $_SESSION['update_myob_member'])
                    ->update([
                        'myob_uid' => $myob_update->UID,
                        'myob_row' => $myob_update->RowVersion,
                        'myob_integ_status' => 'Y'
                    ]);
            }
            if(isset($_SESSION['myob_customer_updates'])) {
                array_push($_SESSION['myob_customer_updates'], array("error_code" => 0, "customer_name" => $_SESSION['myob_update_customer_name']));
            } else {
                $myob_customer_errors = array();
                array_push($myob_customer_errors, array("error_code" => 0, "customer_name" => $_SESSION['myob_update_customer_name']));
                $_SESSION['myob_customer_updates'] = $myob_customer_errors;
            }
        } else {
            $error_code = $myob_update->Errors[0]->ErrorCode;

            if(isset($_SESSION['myob_customer_updates'])) {
                array_push($_SESSION['myob_customer_updates'], array("error_code" => $error_code, "customer_name" => $_SESSION['myob_update_customer_name']));
            } else {
                $myob_customer_errors = array();
                array_push($myob_customer_errors, array("error_code" => $error_code, "customer_name" => $_SESSION['myob_update_customer_name']));
                $_SESSION['myob_customer_updates'] = $myob_customer_errors;
            }
        }

        $count_errors = 0;

        for ($m = 0; $m < sizeof($_SESSION['myob_customer_updates']); $m++) {

            $error_code = $_SESSION['myob_customer_updates'][$m]['error_code'];
            $customer = $_SESSION['myob_customer_updates'][$m]['customer_name'];

            if($error_code == '0'){
                $msg = $customer . " was successfully updated";
                $this->flash->addMessage('success', $msg);
            } else {
                $count_errors++;
                $msg = "MYOB could not be updated for " . $customer . " - Error Code: " . $error_code;
                $this->flash->addMessage('error', $msg);
            }
        }

        //dump($mode);
        //dump($_SESSION['myob_customer_updates']);
        //dump($this->flash);
        //dump("ERR:", $count_errors);
        if($mode == 'invoice' ) {
            if ($count_errors > 0) {
                return 'update_errors';
            } else {
                return 'OK';
            }
        } else {
            //return 'update_errors';

            if ($count_errors > 0) {
                return 'update_errors';
            } else {
                return 'OK';
            }
        }
    }
}