@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.min.js" integrity="sha256-ngFW3UnAN0Tnm76mDuu7uUtYEcG3G5H1+zioJw3t+68=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/vee-validate@2.2.15/dist/vee-validate.min.js" integrity="sha256-m+taJnCBUpRECKCx5pbA0mw4ckdM2SvoNxgPMeUJU6E=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.js" integrity="sha256-bd8XIKzrtyJ1O5Sh3Xp3GiuMIzWC42ZekvrMMD4GxRg=" crossorigin="anonymous"></script>

<div class="container">
    <div class="row justify-content-center" id="divbox">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Login</div>

                <div class="card-body">
               
           
                      

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">Login ID</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email"   v-model="login_id">

                             
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password"  v-model="password">

                             
                            </div>
                        </div>

         

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary" @click="login()">
                                  Login
                                </button>

                            </div>
                        </div>
                  
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    new Vue({
                el: '#divbox',
                // components: {
                //   ValidationProvider
                // },
                data: {
                    login_id:"",
                    password:"",
                
                },
                created: function() {
                    // alert(this.got_details)
                    //   alert('hello');
                },
                methods: {
                    login: function() {
                        axios.post('/sigin',{
                        'login': this.login_id,
                        'password':this.password

                        })
                            .then(response => {
                             if(response.data)
                             {
                            //   window.location.href="http://localhost:8000/login_home";

                             }
                            })
                            .catch(response => {
                                // List errors on response...
                            });
                    },
                    redirect_to_child_portal:function(token,child_name)
                    {
                        const config = {
                            headers: {
                                "Access-Control-Allow-Origin": "*",
                            }
                        }
                    
                       
                        if(child_name == 'ter')
                        {
                            this.url='http://localhost:8080/api/connect_user';
                            this.redirect_url ='http://localhost:8080/api/login_user/';

                        }else if(child_name == "easemy_lr"){
                            this.url='http://localhost:8081/api/connect_user';
                            this.redirect_url ='http://localhost:8081/api/login_user/';


                        }



                        axios.post(this.url, {
                        'access_token': this.api_token
                    },config)
                            .then(response => {
                                // console.log(response.data)
                                // return 1;
                                // this.get_session_data();
                            //   window.location.href="http://localhost:8080/home";
                              window.location.href=this.redirect_url+""+response.data;
                           

                            })
                            .catch(response => {
                                // List errors on response...
                            });
                    },
            

                }



                })
</script>
@endsection
