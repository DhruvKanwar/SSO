<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MailnotSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

date_default_timezone_set('Asia/Kolkata');
ini_set('max_execution_time', -1);

class UserController extends Controller
{
    public function get_user_from_spine()
    {




        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://edatakart.com/api/employees',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'X-API-KEY: FiVGwokXDsJ5MEQrA2JY4e1RXJ7i5EkT'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);



        $res = json_decode($response);

        // echo "<pre>";
        // print_r($res->data);
        // exit;

        $size = sizeof($res->data);
        // echo "<pre>";
        // print_r($res->data);
        // print_r("Size :: ");
        // print_r($size);

        // exit;
        $data = array();

        for ($i = 0; $i < $size; $i++) {
            // echo "<pre>";
            $status = "";
            if (!empty($res->data[$i]->EmployeeCode) && $res->data[$i]->DateOfBirth) {
                $check_email_exists = User::where('employee_id', $res->data[$i]->EmployeeCode)->first();
                $sso_unid = User::select('sso_unid')->latest('sso_unid')->first();
                $sso_unid = json_decode(json_encode($sso_unid), true);
                if (empty($sso_unid) || $sso_unid == null) {
                    $sso_unid = 780001;
                } else {
                    $sso_unid = $sso_unid['sso_unid'] + 1;
                }

                // $month_number = explode("-", $res->data[$i]->LastWorkingDate);

                // switch ($month_number[1]) {
                //     case "Jan":
                //         $month_number[1] = '01';
                //         break;
                //     case "Feb":
                //         $month_number[1] = '02';
                //         break;
                //     case "Mar":
                //         $month_number[1] = '03';
                //         break;
                //     case "Apr":
                //         $month_number[1] = '04';
                //         break;
                //     case "May":
                //         $month_number[1] = '05';
                //         break;
                //     case "Jun":
                //         $month_number[1] = '06';
                //     case "Jul":
                //         $month_number[1] = '07';
                //         break;
                //     case "Aug":
                //         $month_number[1] = '08';
                //         break;
                //     case "Sep":
                //         $month_number[1] = '09';
                //         break;
                //     case "Oct":
                //         $month_number[1] = '10';
                //         break;
                //     case "Nov":
                //         $month_number[1] = '11';
                //         break;
                //     case "Dec":
                //         $month_number[1] = '12';
                //         break;
                // }
                // $final_exit_date = $month_number[0] . '-' . $month_number[1] . '-' . $month_number[2];
                // $today = date('d-m-Y');
                // $exit_date = strtotime($final_exit_date);
                // $today_date = strtotime($today);
                // if ($exit_date < $today_date) {
                //     $status = 0;
                // } else {
                //     $status = 1;
                // }

                if ($res->data[$i]->EmployeeStatus == "E") {
                    $status = 0;
                } else {
                    $status = 1;
                }

                // if (!empty($res->data[$i]->last_working_date)) {
                //     $status = 0;
                // } else {
                //     $status = 1;
                // }
                // status=0 is Blocked,status=1 is Active


                $dob = $res->data[$i]->DateOfBirth;
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


                // $pfu = "";
                // if ($res->data[$i]->Grade == "Unit - 1") {
                //     $pfu = "SD1";
                // } else if ($res->data[$i]->Grade == "Unit - 2") {
                //     $pfu = "MA2";
                // } else if ($res->data[$i]->Grade == "Unit - 3") {
                //     $pfu = "SD3";
                // } else if ($res->data[$i]->Grade == "Unit - 4") {
                //     $pfu = "MA4";
                // }
                $data['official_email'] = $res->data[$i]->OfficeEmail;
                $data['company'] = $res->data[$i]->Grade;
                $data['name'] = $res->data[$i]->Name;
                $data['email'] = $sso_unid . '@heythere.in';
                $data['password'] = Hash::make($date_of_birth);
                $data['dob'] = $res->data[$i]->DateOfBirth;
                $data['sso_unid'] = $sso_unid;
                $data['request_source'] = 'spine-sync';
                $data['user_type'] = 'employee';
                $data['employee_id'] = $res->data[$i]->EmployeeCode;
                $data['location'] = $res->data[$i]->Location;
                $data['joining_date'] = $res->data[$i]->DateOfJoining;
                $data['block_date'] = $res->data[$i]->LastWorkingDate;
                $data['phone'] = $res->data[$i]->Mobile1;
                $data['status'] = $status;
                $data['role_id'] = '3';
                $data['assigned_by_id'] = '';
                $data['user_ip'] = \Request::ip();
                $store = "";

                if (empty($check_email_exists)) {
                    $store = User::create($data);
                } else {
                    if (!$status) {
                        unset($data['employee_id']);
                        $store = User::where('id', $check_email_exists->id)->update($data);
                        // $store = User::where('employee_id', $data['employee_id'])->update([
                        //     'block_date' => $res->data->last_working_date, 'status' => $status
                        // ]);
                    }
                }
            }
        }

        return 1;

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
        $user = User::where('user_assigned', 0)->whereIn('role_id', [2, 3])->with('UserRole')->get();
        if ($user) {
            $result_array = array(
                'status' => 'success',
                'msg' => 'Portal Admin Assigned Successfully...',
                'data' => $user
            );

            return response()->json($result_array, 200);
        } else {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'Error in connecting with the Portal you are assigning'
            );


            return response()->json($result_array, 405);
        }

    }

    public function get_all_portal_admins()
    {
        $user = User::where('user_assigned', 1)->where('role_id', 2)->with('UserRole', 'UserDetail')->get();
        if ($user) {
            $result_array = array(
                'status' => 'success',
                'msg' => 'Portal Admin Assigned Successfully...',
                'data' => $user
            );

            return response()->json($result_array, 200);
        } else {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'Error in connecting with the Portal you are assigning'
            );


            return response()->json($result_array, 405);
        }
    }

    public function get_users_to_assign()
    {
        $details = Auth::user();
        // $get_portal_ids=User::where('employee_id',$details->employee_id)->first();

        $user = User::where('user_assigned', 0)->where('role_id', 3)->with('UserRole')->get();
       
        if ($user) {
            $result_array = array(
                'status' => 'success',
                'msg' => 'Data Fetched Successfully...',
                'data' => $user
            );

            return response()->json($result_array, 200);
        } else {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'Error in connecting..'
            );


            return response()->json($result_array, 405);
        }
    }

    public function get_assigned_portal_users()
    {
        $details = Auth::user();
        // $get_portal_ids=User::where('employee_id',$details->employee_id)->first();

        $user = User::where('user_assigned', 1)->where('role_id', 3)->where('assigned_by_id',$details->id)->with('UserRole')->get();

        if ($user) {
            $result_array = array(
                'status' => 'success',
                'msg' => 'Data Fetched Successfully...',
                'data' => $user
            );

            return response()->json($result_array, 200);
        } else {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'Error in connecting..'
            );


            return response()->json($result_array, 405);
        }
    }

   
}
