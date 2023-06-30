<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PortalDetails;
use App\Models\UserDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;




class PortalController extends Controller
{
    //
    public function get_all_users()
    {
    }

    public function assign_portal_admin(Request $request)
    {
        $data = $request->all();
        $details = Auth::user();


        $get_new_portal_user = User::where('employee_id', $data['emp_id'])->get();
        // return $get_new_portal_user[0]->employee_id;

        if ($get_new_portal_user[0]->status == 0) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'This User is Blocked'
            );


            return response()->json($result_array, 405);
        }


        if ($details->id != 1) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'You do not have rights to perform this action'
            );

            $token = Auth::user()->token();
            $token->revoke();
            return response()->json($result_array, 405);
        }



        // // Get the current request object
        // $request = Request::capture();

        // // Get the authorization header
        // $authorizationHeader = $request->header('Authorization');

        // // Extract the token from the authorization header
        // if ($authorizationHeader && strpos($authorizationHeader, 'Bearer') === 0) {
        //     // Bearer token found
        //     $token = str_replace('Bearer ', '', $authorizationHeader);
        // } else {
        //     // Bearer token not found
        //     return "Bearer Token not found";
        // }
        // // die;



        $user_detail['user_id'] = $data['emp_id'];
        $user_detail['portal_id'] = $data['portal_id'];
        $user_detail['role_id'] = $data['role_id'];
        $user_detail['assign_date'] = date('d-m-Y');
        if (!empty($data['remarks'])) {
            $user_detail['remarks'] = $data['remarks'];
        }
        $user_detail['updated_by'] = $details->name;
        $user_detail['updated_id'] = $details->id;

        $db_store = UserDetail::create($user_detail);
        if ($db_store) {
            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $data['portal_id'], 'role_id' => $data['role_id'], 'user_assigned' => 1]);
        }

        if ($update_user_table) {


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://localhost:8080/api/assign_role',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('email' => $get_new_portal_user[0]->email, 'admin_email' => $details->email, 'password' => $get_new_portal_user[0]->password, 'role' => 'ter user', 'name' => $get_new_portal_user[0]->name),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            if ($response) {
                $result_array = array(
                    'status' => 'success',
                    'msg' => 'Portal Admin Assigned Successfully...'
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
    }

    public function remove_portal_admin(Request $request)
    {
        $data = $request->all();
        $details = Auth::user();


        $get_new_portal_user = User::where('employee_id', $data['emp_id'])->get();
        // return $get_new_portal_user[0]->employee_id;


        if ($details->id != 1) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'You do not have rights to perform this action'
            );

            $token = Auth::user()->token();
            $token->revoke();
            return response()->json($result_array, 405);
        }


        if (empty($data['remarks'])) {
            return "Remarks are Mandatory";
        }


        $user_detail['user_id'] = $data['emp_id'];
        $user_detail['portal_id'] = $data['portal_id'];
        $user_detail['role_id'] = $data['role_id'];
        $user_detail['remove_date'] = date('d-m-Y');
        $user_detail['remarks'] = $data['remarks'];
        $user_detail['updated_by'] = $details->name;
        $user_detail['updated_id'] = $details->id;
        // return $user_detail;

        $db_store = UserDetail::create($user_detail);
        if ($db_store) {
            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => "", 'role_id' => "", 'user_assigned' => 0]);
        }

        if ($update_user_table) {


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://localhost:8080/api/remove_role',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('email' => $get_new_portal_user[0]->email, 'admin_email' => $details->email),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            if ($response) {
                $result_array = array(
                    'status' => 'success',
                    'msg' => 'Portal Admin Access Removed Successfully...'
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
    }
    public function generate_access_token(Request $request)
    {
        $data = $request->all();
    }

    public function get_all_portals()
    {
        $res = PortalDetails::get();
        return $res;
    }
}
