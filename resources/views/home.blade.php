@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.min.js" integrity="sha256-ngFW3UnAN0Tnm76mDuu7uUtYEcG3G5H1+zioJw3t+68=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/vee-validate@2.2.15/dist/vee-validate.min.js" integrity="sha256-m+taJnCBUpRECKCx5pbA0mw4ckdM2SvoNxgPMeUJU6E=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.js" integrity="sha256-bd8XIKzrtyJ1O5Sh3Xp3GiuMIzWC42ZekvrMMD4GxRg=" crossorigin="anonymous"></script>
<div class="container">
    <div class="row justify-content-center" id="divbox">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>
                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif
                    <button v-on:click="redirect_to_child('easemy_lr')">Redirect to EASEMY_LR</button>
                    <button v-on:click="redirect_to_child('ter')">Redirect to TER</button>
                    {{ __('You are logged in!') }}
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
            api_token: "",
            chld_name: "",
            redirect_url: "",
            url: "",
        },
        created: function() {
            // alert(this.got_details)
            //   alert('hello');
        },
        methods: {
            redirect_to_child: function(type) {


                axios.post('/createAccessToken')
                    .then(response => {
                        this.api_token = response.data;
                        console.log(this.api_token)
                        this.redirect_to_child_portal(this.api_token, type);
                    })
                    .catch(response => {
                        // List errors on response...
                    });
            },
            redirect_to_child_portal: function(token, child_name) {
                const config = {
                    headers: {
                        "Access-Control-Allow-Origin": "*",
                    }
                }


                if (child_name == 'ter') {
                    this.url = 'http://localhost:8080/api/connect_user';
                    this.redirect_url = 'http://localhost:8080/api/login_user/';

                } else if (child_name == "easemy_lr") {
                    this.url = 'http://localhost:8081/api/connect_user';
                    this.redirect_url = 'http://localhost:8081/api/login_user/';


                }

                axios.post(this.url, {
                        'access_token': this.api_token
                    }, config)
                    .then(response => {
                        console.log(response.data)
                        if (response.data == "0") {
                            alert("User Doesn't exists in this portal")
                            // swal('error', "User Doesn't exists in this portal", "error")
                            return 1;
                        }
                        // return 1;
                        // this.get_session_data();
                        //   window.location.href="http://localhost:8080/home";
                        window.location.href = this.redirect_url + "" + response.data;


                    })
                    .catch(response => {
                        // List errors on response...
                    });
            },


        }



    })
</script>
@endsection