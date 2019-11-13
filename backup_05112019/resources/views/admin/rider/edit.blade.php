@extends('admin.template')

@section('main')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" ng-controller="destination_admin">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Edit Rider
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ url(LOGIN_USER_TYPE.'/rider') }}">Riders</a></li>
        <li class="active">Edit</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- right column -->
        <div class="col-md-8 col-sm-offset-2">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Rider Form</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            {!! Form::open(['url' => 'admin/edit_rider/'.$result->id, 'class' => 'form-horizontal']) !!}
              <div class="box-body">
              <span class="text-danger">(*)Fields are Mandatory</span>
                <div class="form-group">
                  <label for="input_first_name" class="col-sm-3 control-label">First Name<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::text('first_name', $result->first_name, ['class' => 'form-control', 'id' => 'input_first_name', 'placeholder' => 'First Name']) !!}
                    <span class="text-danger">{{ $errors->first('first_name') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_last_name" class="col-sm-3 control-label">Last Name<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::text('last_name', $result->last_name, ['class' => 'form-control', 'id' => 'input_last_name', 'placeholder' => 'Last Name']) !!}
                    <span class="text-danger">{{ $errors->first('last_name') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_email" class="col-sm-3 control-label">Email<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::text('email', $result->email, ['class' => 'form-control', 'id' => 'input_email', 'placeholder' => 'Email']) !!}
                    <span class="text-danger">{{ $errors->first('email') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_password" class="col-sm-3 control-label">Password</label>
                  <div class="col-sm-6">
                    {!! Form::text('password', '', ['class' => 'form-control', 'id' => 'input_password', 'placeholder' => 'Password']) !!}
                    <span class="text-danger">{{ $errors->first('password') }}</span>
                  </div>
                </div>
               <!--  <div class="form-group">
                  <label for="input_dob" class="col-sm-3 control-label">D.O.B<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::text('dob', $result->dob_dmy, ['class' => 'form-control', 'id' => 'input_dob', 'placeholder' => 'DOB', 'autocomplete' => 'off']) !!}
                    <span class="text-danger">{{ $errors->first('dob') }}</span>
                  </div>
                </div> -->
                
                {!! Form::hidden('user_type','Rider', ['class' => 'form-control', 'id' => 'user_type', 'placeholder' => 'Select']) !!}
                 <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Country Code<em class="text-danger">*</em></label>

                  <div class="col-sm-6">
                    <select class='form-control' id = 'input_country_code' name='country_code' >
                        @foreach($country_code_option as $country_code)
                          <option value="{{@$country_code->phone_code}}" {{ ($country_code->phone_code == $result->country_code) ? 'Selected' : ''}} >{{$country_code->long_name}}</option>
                        @endforeach
                      </select>
                    <span class="text-danger">{{ $errors->first('country_code') }}</span>
                  </div>
                </div> 
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Mobile Number </label>

                  <div class="col-sm-6">
                   
                     {!! Form::text('mobile_number',$result->mobile_number, ['class' => 'form-control', 'id' => 'mobile_number', 'placeholder' => 'Mobile Number']) !!}
                    <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
                  </div>
                </div>
               
              <!--   <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Status<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    {!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $result->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']) !!}
                    <span class="text-danger">{{ $errors->first('status') }}</span>
                  </div>
                </div> -->

                 <div class="form-group">
                  <label for="input_password" class="col-sm-3 control-label">Home Location</label>
                  <div class="col-sm-6">
                    {!! Form::text('home_location', @$location->home, ['class' => 'form-control', 'id' => 'input_home_location', 'placeholder' => 'Home Location']) !!}
                    <span class="text-danger">{{ $errors->first('home_location') }}</span>
                  </div>
                </div>
                  {!! Form::hidden('home_latitude',@$location->home_latitude, ['class' => 'form-control', 'id' => 'home_latitude', 'placeholder' => 'Select']) !!}
                    {!! Form::hidden('home_longitude',@$location->home_longitude, ['class' => 'form-control', 'id' => 'home_longitude', 'placeholder' => 'Select']) !!}
                <div class="form-group">
                  <label for="input_password" class="col-sm-3 control-label">Work Location</label>
                  <div class="col-sm-6">
                    {!! Form::text('work_location', @$location->work, ['class' => 'form-control', 'id' => 'input_work_location', 'placeholder' => 'Work Location']) !!}
                    <span class="text-danger">{{ $errors->first('work_location') }}</span>
                  </div>
                </div>
                {!! Form::hidden('work_latitude',@$location->work_latitude, ['class' => 'form-control', 'id' => 'work_latitude', 'placeholder' => 'Select']) !!}
                {!! Form::hidden('work_longitude',@$location->work_longitude, ['class' => 'form-control', 'id' => 'work_longitude', 'placeholder' => 'Select']) !!}
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <button type="submit" class="btn btn-info pull-right" name="submit" value="submit">Submit</button>
                <button type="submit" class="btn btn-default pull-left" name="cancel" value="cancel">Cancel</button>
              </div>
              <!-- /.box-footer -->
            </form>
          </div>
          <!-- /.box -->
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  @endsection
  @push('scripts')
<script>
  $('#input_dob').datepicker({ 'format': 'dd-mm-yyyy'});
</script>
@endpush
