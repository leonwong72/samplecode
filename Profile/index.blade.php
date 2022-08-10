@extends('admin.layouts.app')

@push('styles')

@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0 font-size-18">Profile</h4>
                    <ol class="breadcrumb m-0 mt-3">
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card card-custom gutter-b">
                <div class="card-body">
                    <h4 class="card-title">Personal Information</h4>

                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <tbody>
                            <tr>
                                <th scope="row">Full Name :</th>
                                <td>
                                    <a href="#" class="editable" data-type="text" data-pk="{{ auth()->id() }}" data-name="name" data-url="{{ route('admin.profile.update') }}" data-original-title="Your Name">{{ auth()->user()->name }}</a>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Username :</th>
                                <td>{{ auth()->user()->username }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Code :</th>
                                <td>{{ auth()->user()->code ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Identity Card :</th>
                                <td>
                                    <a href="#" class="editable" data-type="tel" data-pk="{{ auth()->id() }}" data-name="identity_card" data-url="{{ route('admin.profile.update') }}" data-original-title="Your Identity Card">{{ auth()->user()->identity_card }}</a>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Phone Number :</th>
                                <td>
                                    <a href="#" class="editable" data-type="tel" data-pk="{{ auth()->id() }}" data-name="phone" data-url="{{ route('admin.profile.update') }}" data-original-title="Your Phone Number">{{ auth()->user()->phone }}</a>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">E-mail :</th>
                                <td>
                                    @if(auth()->user()->email && auth()->user()->email_verified_at)
                                        <a href="#" class="change-email" data-type="email" data-pk="{{ auth()->id() }}" data-name="email" data-url="{{ route('admin.profile.update') }}" data-original-title="Your E-mail">{{ auth()->user()->email }}</a>
                                        <span class="badge badge-soft-warning change-email-button" style="cursor: pointer">Change email</span>
                                    @elseif(!auth()->user()->email_verified_at)
                                        <a href="#" class="editable" data-type="email" data-pk="{{ auth()->id() }}" data-name="email" data-url="{{ route('admin.profile.update') }}" data-original-title="Your E-mail">{{ auth()->user()->email }}</a>

                                        @if(auth()->user()->email)
                                            <button type="button" class="btn btn-warning waves-effect btn-label waves-light btn-sm verify-email ms-3"><i class="fas fa-exclamation-triangle label-icon"></i> Not Verified</button>

                                            <div class="modal fade verify-email-modal" data-bs-backdrop="static" data-bs-keyboard="false">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('admin.profile.verify') }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="myModalLabel">Email Verification OTP</h5>
                                                            </div>

                                                            <div class="modal-body">
                                                                <div class="loader text-center">
                                                                    <div class="spinner-border text-primary">
                                                                        <span class="sr-only">Loading...</span>
                                                                    </div>
                                                                </div>

                                                                <div class="otp-inputs">
                                                                    <div class="d-flex justify-content-between">
                                                                        <input class="otp" type="text" onkeyup="tabChange(1)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(2)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(3)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(4)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(5)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(6)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(7)" maxlength="1" name="otps[]">
                                                                        <input class="otp" type="text" onkeyup="tabChange(8)" maxlength="1" name="otps[]">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer otp-inputs">
                                                                <button type="submit" class="btn btn-primary waves-effect waves-light">Verify</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Role :</th>
                                <td>{{ auth()->user()->is_super ? 'Superadmin' : 'Admin' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card card-custom gutter-b">
                <form action="{{ route('admin.profile.update-password') }}" method="post">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Change Password</h4>
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="old_password">Old Password</label>
                            <input type="password" class="form-control @error('old_password') is-invalid @enderror" id="old_password" name="old_password" placeholder="Enter old password">
                            @error('old_password')
                            <div class="invalid-feedback">
                                <span>{{ $message }}</span>
                            </div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" placeholder="Enter new password">
                            @error('new_password')
                            <div class="invalid-feedback">
                                <span>{{ $message }}</span>
                            </div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="new_password_confirmation">New Password Confirmation</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="Enter new password confirmation">
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $.fn.editable.defaults.ajaxOptions = {type: "PUT", accepts: "application/json", };
        // $.fn.editable.defaults.
        $.fn.editable.defaults.mode = 'inline';
        $.fn.editable.defaults.params = function (params) {
            params._token = '{{ csrf_token() }}';

            return params;
        };
        $('.editable').editable({
            error: function(error) {
                console.log(error)
                $(".editable-error-block").html('<span class="text-danger">'+error.responseJSON.message+'</span>')
            }
        });
        $('.change-email-button').click(function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will need to verify your email again if your email is already verified!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {
                    $('.change-email').editable('enable');
                    $(this).remove();
                }
            })
        });

        $(document).on('click','.verify-email',function() {
            $('.verify-email-modal').modal('show');
            $('.loader').show();
            $('.otp-inputs').hide();
            $.ajax({
                type:'GET',
                url:'{{ route('admin.profile.send-verify-email') }}',
                success:function(data) {
                    if(data.success){
                        $('.loader').hide();
                        $('.otp-inputs').show();
                    }
                }
            });
        });
        let tabChange = function(val){
            let ele = document.querySelectorAll('.otp-inputs .otp');

            if(ele[val-1].value != ''){
                ele[val].focus()
            }else if(ele[val-1].value == ''){
                ele[val-2].focus()
            }
        }
    </script>
@endpush
