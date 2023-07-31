<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PortalDetails;
use App\Models\UserDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Ui\Presets\React;
use Illuminate\Support\Facades\Mail;
use App\Models\MailnotSent;


class PortalController extends Controller
{
    //
    public function get_all_users()
    {
        $user = User::where('user_assigned', 0)->whereIn('role_id', [2, 3])->get();
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


    public function assign_portal_admin(Request $request)
    {
        $data = $request->all();
        // return $data;
        $details = Auth::user();


        $get_new_portal_user = User::where('employee_id', $data['emp_id'])->get();
        // return $get_new_portal_user[0]->employee_id;

        $explode_request_portal_ids = explode(',', $data['portal_id']);
        $portal_ids =  explode(',', $get_new_portal_user[0]->portal_id);

        $explode_assigned_portal_ids = array_map('trim', $portal_ids);

        if (count(array_diff($explode_request_portal_ids, $explode_assigned_portal_ids)) === 0 && count(array_diff($explode_assigned_portal_ids, $explode_request_portal_ids)) === 0) {

            $result_array = array(
                'status' => 'fail',
                'msg' => 'These Portals already has been assigned',
                'portal_id' => $get_new_portal_user[0]->portal_id
            );
            return response()->json($result_array, 200);
        }




        if ($get_new_portal_user[0]->status == 0) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'This User is Blocked'
            );


            return response()->json($result_array, 405);
        }


        if ($details->role_id != 1) {
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
            $check_portal_exists = User::where('employee_id', $data['emp_id'])->first();
            if(!empty($check_portal_exists->portal_id))
            {
                $portal_data=explode(',', $check_portal_exists->portal_id);
                $portals_now = array_map('trim', $portal_data);

                array_push($portals_now, $data['portal_id'] );
                sort($portals_now);
                $updated_portals = implode(', ', $portals_now);
                $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $updated_portals, 'role_id' => $data['role_id'], 'user_assigned' => 1]);

                
            }
            else{

                $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $data['portal_id'], 'role_id' => $data['role_id'], 'user_assigned' => 1]);

            }
        }

        $assign_flag = false;
        $result_array = array();

        if ($update_user_table) {

            $get_assigned_user_data=User::where('employee_id',$data['emp_id'])->first();

            $portal_id = explode(',', $user_detail['portal_id']);

            $explode_portal_ids = array_map('trim', $portal_id);


            $portal_ids_size = sizeof($explode_portal_ids);
            $portal_data = array();
            for ($i = 0; $i < $portal_ids_size; $i++) {
                $portalDBdata = PortalDetails::where('id', $explode_portal_ids[$i])->first();
                $url = $portalDBdata->url;
                $portal_name = $portalDBdata->portal_name;
                if ($explode_portal_ids[$i] == 1 && $get_assigned_user_data->role_id == 2  || 
                    $explode_portal_ids[$i] == 2 && $get_assigned_user_data->role_id == 2) {
                    $role = "admin user";
                } else {
                    $role = "";
                }

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url . '/api/assign_role',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('email' => $get_new_portal_user[0]->email, 'admin_email' => $details->email, 'password' => $get_new_portal_user[0]->password, 'role' => $role, 'name' => $get_new_portal_user[0]->name),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                $update_user_table = User::where('employee_id', $data['emp_id'])->first();
        
                if ($response == 101) {
                    $result_array = array(
                        'status' => 'success',
                        'msg' => 'Portal Admin Assigned Successfully...',
                        'portal_name' => $portal_name,
                        'portal_id' => $update_user_table->portal_id
                    );

                    $assign_flag = true;
                } else {
                    $db_store = UserDetail::where('user_id', $user_detail['user_id'])->whereNotNull('assign_date')->latest('id')->first();
                    if (!empty($db_store)) {

                        $portal_assigned_id = $db_store->portal_id;
                        $assigned_id = explode(',', $portal_assigned_id);

                        $explode_assigned_id = array_map('trim', $assigned_id);

                        $check_explode = array();
                        $check_explode = $explode_assigned_id;
                        $key = array_search($explode_portal_ids[$i], $explode_assigned_id);
                        if ($key !== false) {
                            unset($explode_assigned_id[$key]);
                        }
                        $updated_portal_id = implode(', ', $explode_assigned_id);
                        if ($portal_ids_size == 1 || sizeof($check_explode) == 1) {
                            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => "", 'role_id' => 3, 'user_assigned' => 0]);
                            UserDetail::where('id', $db_store->id)->delete();
                        } else {
                            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $updated_portal_id]);
                            UserDetail::where('id', $db_store->id)->update([
                                'portal_id' => $updated_portal_id
                            ]);
                        }

                        $update_user_table = User::where('employee_id', $data['emp_id'])->first();

                        $result_array = array(
                            'status' => 'fail',
                            'msg' => 'Error in connecting with the Portal you are assigning',
                            'portal_name' => $portal_name,
                            'portal_id' => $update_user_table->portal_id
                        );

                        $assign_flag = false;
                    }
                }
            }

            $host=1;

            if($host)
            {
            $data['title'] = "Admin assigned";
            $data['email'] = 'itsupport@frontierag.com';
            $get_portal_id=User::where('employee_id',$data['emp_id'])->first();
            $explode=explode(',',$get_portal_id->portal_id);
            $portal_names=self::fetch_portals($explode);
            $data['portal_names']= $portal_names;
            $data['name']= $get_portal_id->name;
            if (!empty($data["email"])) {

                // return view('emails.UserLoginDetails', $data);
               Mail::mailer('smtp')->send('emails.AssignAdmin', $data, function ($message) use ($data) {
                    $message->to($data["email"], $data["email"])
                        ->from($address = 'do-not-reply@frontierag.com', $name = 'Frontiers No Reply')
                        ->subject($data["title"]);
                });
                // return $t;
            } else {
                // dd("f");
                // print_r("DS");
                $mail_not_sent['mail_response'] = 'No Email Found';
                $res = MailnotSent::create($mail_not_sent);
            }

        }
            return response()->json($result_array, 200);
         
        }
    }

    public static function fetch_portals($inputArray)
    {
        $portalNames = [];
        foreach ($inputArray as $value) {
            if ($value == 1) {
                $portalNames[] = 'TER';
            } elseif ($value == 2) {
                $portalNames[] = 'EaseMyLR';
            } 

            return $portalNames;
        }

    }

    public function remove_portal_admin(Request $request)
    {
        $data = $request->all();
        $details = Auth::user();


        $get_new_portal_user = User::where('employee_id', $data['emp_id'])->get();

        // return $get_new_portal_user[0]->employee_id;
        if (empty($get_new_portal_user[0]->portal_id)) {
            $result_array = array(
                'status' => 'success',
                'msg' => 'No Portal has been assigned yet',
                'portal_id' => $get_new_portal_user[0]->portal_id
            );
            return response()->json($result_array, 200);
        }

        if ($get_new_portal_user[0]->status == 0) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'This User is Blocked'
            );


            return response()->json($result_array, 405);
        }


        if ($details->role_id != 1) {
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


      
        $result_array = array();


        $explode_portal_ids = explode(',', $data['portal_id']);
        $portal_ids_size = sizeof($explode_portal_ids);
  

        $portal_data = array();
        for ($i = 0; $i < $portal_ids_size; $i++) {
            $portalDBdata = PortalDetails::where('id', $explode_portal_ids[$i])->first();
            $url = $portalDBdata->url;
            $portal_name = $portalDBdata->portal_name;
            if ($explode_portal_ids[$i] == 1) {
                $role = "ter user";
            } else {
                $role = "";
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url . '/api/remove_role',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('email' => $get_new_portal_user[0]->email, 'admin_email' => $details->email, 'password' => $get_new_portal_user[0]->password, 'role' => $role, 'name' => $get_new_portal_user[0]->name),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            // $response = "DS";

            if ($response == 101) {
                $portal_data[$i] = $explode_portal_ids[$i];
            }

        }

        $get_user_data = User::where('employee_id', $data['emp_id'])->first();
        $portal_id = explode(',', $get_user_data->portal_id);
        $explode_user_portal_id = array_map('trim', $portal_id);



        $remove_ids = array_intersect($explode_user_portal_id, $portal_data);

        // // print_r(implode(', ', $remove_ids));
        // print_r($explode_user_portal_id);
        // print_r($portal_data);
        // print_r($remove_ids);
        // exit;

        $user_detail['user_id'] = $data['emp_id'];
        $user_detail['role_id'] = $data['role_id'];
        $user_detail['remove_date'] = date('d-m-Y');
        $user_detail['remarks'] = $data['remarks'];
        $user_detail['updated_by'] = $details->name;
        $user_detail['updated_id'] = $details->id;

        sort($portal_data);
        sort($explode_user_portal_id);

        // print_r($explode_user_portal_id);
        //   print_r($portal_data);
        //   print_r($remove_ids);
        //   exit;

        if ($portal_data == $explode_user_portal_id) {
            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => "", 'role_id' => 3, 'user_assigned' => 0]);
            $updated_portal_id = implode(', ', $portal_data);
            $user_detail['portal_id'] = $updated_portal_id;
            if (!empty($user_detail['portal_id'])) {
                $db_store = UserDetail::create($user_detail);
            }
        } else {
            $output = array_merge(array_diff($explode_user_portal_id, $portal_data), array_diff($portal_data, $explode_user_portal_id));
            $updated_portal_ids   = implode(', ', $output);
            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $updated_portal_ids, 'role_id' => 2, 'user_assigned' => 1]);
            $user_detail['portal_id'] = implode(', ', $remove_ids);
            if (!empty($user_detail['portal_id'])) {
                $db_store = UserDetail::create($user_detail);
            }
        }


        $updated_portal_details = User::where('employee_id', $data['emp_id'])->first();

        $result_array = array(
            'status' => 'success',
            'msg' => 'Portal Admin Removed Successfully...',
            'portal_id' => $updated_portal_details->portal_id
        );



        $host = 1;

        if ($host) {
            $data['title'] = "Admin Removed";
            $data['email'] = 'itsupport@frontierag.com';
            $get_portal_id = UserDetail::where('user_id', $data['emp_id'])->orderBy('id', 'desc')->first();
            $explode = explode(',', $get_portal_id->portal_id);
            $portal_names = self::fetch_portals($explode);
            $data['portal_names'] = $portal_names;
            $data['name'] = $get_portal_id->name;
            if (!empty($data["email"])) {

                // return view('emails.UserLoginDetails', $data);
                Mail::mailer('smtp')->send('emails.RemoveAdmin', $data, function ($message) use ($data) {
                    $message->to($data["email"], $data["email"])
                        ->from($address = 'do-not-reply@frontierag.com', $name = 'Frontiers No Reply')
                        ->subject($data["title"]);
                });
                // return $t;
            } else {
                // dd("f");
                // print_r("DS");
                $mail_not_sent['mail_response'] = 'No Email Found';
                $res = MailnotSent::create($mail_not_sent);
            }
        }

        return response()->json($result_array, 200);


        // // $db_store = UserDetail::create($user_detail);



        // if ($assign_flag) {
        // } else {
        //     return response()->json($result_array, 200);
        // }
    }

    public function assign_portal_role(Request $request)
    {
        $data = $request->all();
        // return $data;
        $details = Auth::user();


        $get_new_portal_user = User::where('employee_id', $data['emp_id'])->get();
        $check_portal_admin = User::where('employee_id', $details->employee_id)->first();
        // return $get_new_portal_user[0]->employee_id;

   
        $explode_request_portal_ids = explode(',', $data['portal_id']);
        $explode_assigned_portal_ids =  explode(',', $get_new_portal_user[0]->portal_id);
        $explode_portal_admin_ids =  explode(',', $check_portal_admin->portal_id);

        $array1_without_spaces = array_map('trim', $explode_portal_admin_ids);
        $commonValues = array_intersect($array1_without_spaces, $explode_request_portal_ids);

        if (empty($commonValues)) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'You don not have access for some portals',
                'portal_id' => $check_portal_admin->portal_id
            );
            return response()->json($result_array, 200);
        }

        if (count(array_diff($explode_request_portal_ids, $explode_assigned_portal_ids)) === 0 && count(array_diff($explode_assigned_portal_ids, $explode_request_portal_ids)) === 0) {

            $result_array = array(
                'status' => 'fail',
                'msg' => 'These Portals already has been assigned',
                'portal_id' => $get_new_portal_user[0]->portal_id
            );
            return response()->json($result_array, 200);
        }




        if ($get_new_portal_user[0]->status == 0) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'This User is Blocked'
            );


            return response()->json($result_array, 405);
        }


        if ($details->role_id != 2) {
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
        $user_detail['role_id'] = '3';
        $user_detail['assign_date'] = date('d-m-Y');
        if (!empty($data['remarks'])) {
            $user_detail['remarks'] = $data['remarks'];
        }
        $user_detail['updated_by'] = $details->name;
        $user_detail['updated_id'] = $details->id;

        $db_store = UserDetail::create($user_detail);
        if ($db_store) {
            $check_portal_exists = User::where('employee_id', $data['emp_id'])->first();
            if (!empty($check_portal_exists->portal_id)) {
                $portals_now = explode(',', $check_portal_exists->portal_id);
                array_push($portals_now, $data['portal_id']);
                sort($portals_now);
                $updated_portals = implode(', ', $portals_now);
                $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $updated_portals, 'role_id' => 3, 'user_assigned' => 1]);
            } else {

                $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $data['portal_id'], 'assigned_by_id' => $details->id, 'role_id' => 3, 'user_assigned' => 1]);
            }

        }

        $assign_flag = false;
        $result_array = array();

        if ($update_user_table) {

            $explode_portal_ids = explode(',', $user_detail['portal_id']);
            $portal_ids_size = sizeof($explode_portal_ids);
            $portal_data = array();
            for ($i = 0; $i < $portal_ids_size; $i++) {
                $portalDBdata = PortalDetails::where('id', $explode_portal_ids[$i])->first();
                $url = $portalDBdata->url;
                $portal_name = $portalDBdata->portal_name;
                if ($explode_portal_ids[$i] == 1) {
                    $role = "ter user";
                } else {
                    $role = "";
                }

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url . '/api/assign_role',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('email' => $get_new_portal_user[0]->email, 'admin_email' => $details->email, 'password' => $get_new_portal_user[0]->password, 'role' => $role, 'name' => $get_new_portal_user[0]->name),
                ));

                $response = curl_exec($curl);
                // return $response;
                curl_close($curl);
                $update_user_table = User::where('employee_id', $data['emp_id'])->first();
                if ($response == 101) {
                    $result_array = array(
                        'status' => 'success',
                        'msg' => 'Portal Admin Assigned Successfully...',
                        'portal_name' => $portal_name,
                        'portal_id' => $update_user_table->portal_id
                    );

                    $assign_flag = true;
                } else {
                    $db_store = UserDetail::where('user_id', $user_detail['user_id'])->whereNotNull('assign_date')->latest('id')->first();
                    if (!empty($db_store)) {

                        $portal_assigned_id = $db_store->portal_id;
                        $explode_assigned_id = explode(',', $portal_assigned_id);
                        $check_explode = array();
                        $check_explode = $explode_assigned_id;
                        $key = array_search($explode_portal_ids[$i], $explode_assigned_id);
                        if ($key !== false) {
                            unset($explode_assigned_id[$key]);
                        }
                        $updated_portal_id = implode(', ', $explode_assigned_id);
                        if ($portal_ids_size == 1 || sizeof($check_explode) == 1) {
                            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => "", 'role_id' => 3, 'assigned_by_id'=> $details->id, 'user_assigned' => 0]);
                            UserDetail::where('id', $db_store->id)->delete();
                        } else {
                            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $updated_portal_id, 'assigned_by_id'=> $details->id]);
                            UserDetail::where('id', $db_store->id)->update([
                                'portal_id' => $updated_portal_id
                            ]);
                        }

                        $update_user_table = User::where('employee_id', $data['emp_id'])->first();

                        $result_array = array(
                            'status' => 'fail',
                            'msg' => 'Error in connecting with the Portal you are assigning',
                            'portal_name' => $portal_name,
                            'portal_id' => $update_user_table->portal_id
                        );

                        $assign_flag = false;
                    }
                }
            }

            $host=1;

            if ($host) {
                $data['title'] = "Portal Role assigned";
                $data['email'] = 'itsupport@frontierag.com';
                $get_portal_id = User::where('employee_id', $data['emp_id'])->first();
                $explode = explode(',', $get_portal_id->portal_id);
                $portal_names = self::fetch_portals($explode);
                $data['portal_names'] = $portal_names;
                $data['name'] = $get_portal_id->name;
                if (!empty($data["email"])) {

                    // return view('emails.UserLoginDetails', $data);
                    Mail::mailer('smtp')->send('emails.AssignPortal', $data, function ($message) use ($data) {
                        $message->to($data["email"], $data["email"])
                            ->from($address = 'do-not-reply@frontierag.com', $name = 'Frontiers No Reply')
                            ->subject($data["title"]);
                    });
                    // return $t;
                } else {
                    // dd("f");
                    // print_r("DS");
                    $mail_not_sent['mail_response'] = 'No Email Found';
                    $res = MailnotSent::create($mail_not_sent);
                }
            }

            return response()->json($result_array, 200);
          
        
        }
    }

    public function remove_portal_role(Request $request)
    {
        $data = $request->all();
        $details = Auth::user();

        $get_new_portal_user = User::where('employee_id', $data['emp_id'])->get();
        $check_portal_admin = User::where('employee_id', $details->employee_id)->first();
        // return $get_new_portal_user[0]->employee_id;

        $explode_request_portal_ids = explode(',', $data['portal_id']);
        $explode_portal_admin_ids =  explode(',', $check_portal_admin->portal_id);

        $array1_without_spaces = array_map('trim', $explode_portal_admin_ids);
        $commonValues = array_intersect($array1_without_spaces, $explode_request_portal_ids);

     
        if (empty($commonValues)) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'You don not have access for some portals',
                'portal_id' => $check_portal_admin->portal_id
            );
            return response()->json($result_array, 200);
        }



        // return $get_new_portal_user[0]->employee_id;
        if (empty($get_new_portal_user[0]->portal_id)) {
            $result_array = array(
                'status' => 'success',
                'msg' => 'No Portal has been assigned yet',
                'portal_id' => $get_new_portal_user[0]->portal_id
            );
            return response()->json($result_array, 200);
        }

        if ($get_new_portal_user[0]->status == 0) {
            $result_array = array(
                'status' => 'fail',
                'msg' => 'This User is Blocked'
            );


            return response()->json($result_array, 405);
        }


        if ($details->role_id != 2) {
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


        // if ($db_store) {
        //     $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $data['portal_id'], 'role_id' => $data['role_id'], 'user_assigned' => 1]);
        // }

        $assign_flag = false;
        $result_array = array();



        $explode_portal_ids = explode(',', $data['portal_id']);
        $portal_ids_size = sizeof($explode_portal_ids);
        $user_data = User::where('employee_id', $data['emp_id'])->first();
        $assign_portal = $user_data->portal_id;
        $explode_portal_ids_present = explode(',', $assign_portal);
        $present_size_portal_ids = sizeof($explode_portal_ids_present);


        $portal_data = array();
        for ($i = 0; $i < $portal_ids_size; $i++) {
            $portalDBdata = PortalDetails::where('id', $explode_portal_ids[$i])->first();
            $url = $portalDBdata->url;
            $portal_name = $portalDBdata->portal_name;
            if ($explode_portal_ids[$i] == 1) {
                $role = "ter user";
            } else {
                $role = "";
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url . '/api/remove_role',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('email' => $get_new_portal_user[0]->email, 'admin_email' => $details->email, 'password' => $get_new_portal_user[0]->password, 'role' => $role, 'name' => $get_new_portal_user[0]->name),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            // $response = "DS";
            // print_r($response);
            // exit;

            if ($response == 101) {
                $portal_data[$i] = $explode_portal_ids[$i];
            }
        }

        $get_user_data = User::where('employee_id', $data['emp_id'])->first();
        $portal_id = explode(',', $get_user_data->portal_id);


        $explode_user_portal_id = array_map('trim', $portal_id);
        // $commonValues = array_intersect($array1_without_spaces, $explode_request_portal_ids);

        $remove_ids = array_intersect($explode_user_portal_id, $portal_data);


        $user_detail['user_id'] = $data['emp_id'];
        $user_detail['role_id'] = '3';
        $user_detail['remove_date'] = date('d-m-Y');
        $user_detail['remarks'] = $data['remarks'];
        $user_detail['updated_by'] = $details->name;
        $user_detail['updated_id'] = $details->id;

        sort($portal_data);
        sort($explode_user_portal_id);

        // print_r($explode_user_portal_id);
        //   print_r($portal_data);
        //   print_r($remove_ids);
        //   exit;

        if ($portal_data == $explode_user_portal_id) {
            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => "", 'role_id' => 3, 'user_assigned' => 0]);
            $updated_portal_id = implode(', ', $portal_data);
            $user_detail['portal_id'] = $updated_portal_id;
            if (!empty($user_detail['portal_id'])) {
                $db_store = UserDetail::create($user_detail);
            }
        } else {
            $output = array_merge(array_diff($explode_user_portal_id, $portal_data), array_diff($portal_data, $explode_user_portal_id));
            $updated_portal_ids   = implode(', ', $output);
            $update_user_table = User::where('employee_id', $data['emp_id'])->update(['portal_id' => $updated_portal_ids, 'role_id' => 3, 'user_assigned' => 1]);
            $user_detail['portal_id'] = implode(', ', $remove_ids);
            if (!empty($user_detail['portal_id'])) {
                $db_store = UserDetail::create($user_detail);
            }
        }


        $updated_portal_details = User::where('employee_id', $data['emp_id'])->first();

        $result_array = array(
            'status' => 'success',
            'msg' => 'Portal Admin Removed Successfully...',
            'portal_id' => $updated_portal_details->portal_id
        );

        $host = 1;

        if ($host) {
            $data['title'] = "Portal Role Removed";
            $data['email'] = 'itsupport@frontierag.com';
            $get_portal_id = UserDetail::where('user_id', $data['emp_id'])->orderBy('id', 'desc')->first();
            $explode = explode(',', $get_portal_id->portal_id);
            $portal_names = self::fetch_portals($explode);
            $data['portal_names'] = $portal_names;
            $data['name'] = $get_portal_id->name;
            if (!empty($data["email"])) {

                // return view('emails.UserLoginDetails', $data);
                Mail::mailer('smtp')->send('emails.RemovePortal', $data, function ($message) use ($data) {
                    $message->to($data["email"], $data["email"])
                        ->from($address = 'do-not-reply@frontierag.com', $name = 'Frontiers No Reply')
                        ->subject($data["title"]);
                });
                // return $t;
            } else {
                // dd("f");
                // print_r("DS");
                $mail_not_sent['mail_response'] = 'No Email Found';
                $res = MailnotSent::create($mail_not_sent);
            }
        }
        return response()->json($result_array, 200);


        // // $db_store = UserDetail::create($user_detail);



        // if ($assign_flag) {
        // } else {
        //     return response()->json($result_array, 200);
        // }
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
