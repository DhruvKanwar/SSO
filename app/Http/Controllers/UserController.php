<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MailnotSent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{
    public function get_user_from_spine()
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://test-courier.easemyorder.com/api/get_emp_info/01552',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $res = json_decode($response);

        $data = array();

        $sso_unid = User::select('sso_unid')->latest('sso_unid')->first();
        $sso_unid = json_decode(json_encode($sso_unid), true);
        if (empty($sso_unid) || $sso_unid == null) {
            $sso_unid = 780001;
        } else {
            $sso_unid = $sso_unid['sso_unid'] + 1;
        }
        if (!empty($res->data->last_working_date)) {
            $status = 0;
        } else {
            $status = 1;
        }
        // status=0 is Blocked,status=1 is Active


        $dob = $res->data->date_of_birth;
        $month_number = explode("-", $dob);

        switch ($month_number[1]) {
            case "Jan":
                $month_number[1] = '01';
                break;
            case "Feb":
                $month_number[1] = '02';
                break;
            case "Mar":
                $month_number[1] = '03';
                break;
            case "Apr":
                $month_number[1] = '04';
                break;
            case "May":
                $month_number[1] = '05';
                break;
            case "Jun":
                $month_number[1] = '06';
            case "Jul":
                $month_number[1] = '07';
                break;
            case "Aug":
                $month_number[1] = '08';
                break;
            case "Sep":
                $month_number[1] = '09';
                break;
            case "Oct":
                $month_number[1] = '10';
                break;
            case "Nov":
                $month_number[1] = '11';
                break;
            case "Dec":
                $month_number[1] = '12';
                break;
        }
        $date_of_birth = $month_number[0] . $month_number[1] . $month_number[2];


        $data['name'] = $res->data->name;
        $data['email'] = $res->data->official_email_id;
        $data['password'] = Hash::make($date_of_birth);
        $data['dob'] = $res->data->date_of_birth;
        $data['sso_unid'] = $sso_unid;
        $data['request_source'] = 'spine-sync';
        $data['user_type'] = 'employee';
        $data['employee_id'] = $res->data->employee_id;
        $data['company'] = $res->data->pfu;
        $data['location'] = $res->data->location;
        $data['joining_date'] = $res->data->date_of_joining;
        $data['block_date'] = $res->data->last_working_date;
        $data['phone'] = $res->data->telephone_no;
        $data['status'] = $status;
        $data['role_id'] = '3';
        $store = "";
        $check_emp_exists = User::where('employee_id', $data['employee_id'])->first();
        if (empty($check_emp_exists)) {
            $store = User::create($data);
        } else {
            if (!$status) {
                $store = User::where('employee_id', $data['employee_id'])->update([
                    'block_date' => $res->data->last_working_date, 'status' => $status
                ]);
            }
        }

        if ($store) {
            $data['login_password'] = $date_of_birth;
            $data['title'] = "Credentials for Login";
            $data['email'] = 'dhroov.kanwar@eternitysolutions.net';
            if (!empty($data["email"]) && $data["email"] != 0) {

                // return view('emails.UserLoginDetails', $data);
                Mail::mailer('smtp')->send('emails.UserLoginDetails', $data, function ($message) use ($data) {
                    $message->to($data["email"], $data["email"])
                        ->from($address = 'do-not-reply@frontierag.com', $name = 'Frontiers No Reply')
                        ->subject($data["title"]);
                });
            } else {
                // dd("f");
                // print_r("DS");
                $mail_not_sent['mail_response'] = 'No Email Found';
                $res = MailnotSent::create($mail_not_sent);
            }
        } else {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'Unable to Store Data,Please Try Again'
            );

            return response()->json($result_array, 405);
        }
    }

    public function get_all_users()
    {
        $res = User::where('user_assigned', 0)->with('UserRole')->get();
        return $res;
    }
}
