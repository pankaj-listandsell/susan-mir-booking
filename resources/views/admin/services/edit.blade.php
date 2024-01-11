@extends('layouts.app')

@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1>Edit Service</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active">Edit Service</li>
                </ol>
              </div>
            </div>
          </div><!-- /.container-fluid -->
        </section>
    
        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6">
                <div class="card card-primary">
                  <div class="card-header">
                    <h3 class="card-title">Edit Service</h3>
                  </div>
                
                  <form id="editServiceForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                            <div id="name-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" name="price" class="form-control" value="{{ $service->price }}" required>
                            <div id="price-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="max_people">Max People:</label>
                            <input type="number" name="max_people" class="form-control" value="{{ $service->max_people }}" required>
                            <div id="max_people-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" class="form-control">{{ $service->description }}</textarea>
                        </div>

                        <button type="button" id="editServiceBtn" class="btn btn-primary">Edit</button>
                    </div>
                </form>
                </div>
                <!-- /.card -->
              </div>
          
            </div>
            <!-- /.row -->
          </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
      </div>
      @endsection
      @section('js')
      <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" ></script>
    <script>
        
        $(document).ready(function () {
            $('#editServiceBtn').click(function () {
                $.ajax({
                    type: 'POST',
                    url: '{{ route("services.update", $service->id) }}',
                    data: $('#editServiceForm').serialize(),
                    success: function (res) {
                    if(res.status == 'failed'){
                        if (res.errors.name) {
                            $('#name-error').show().text(res.errors.name);
                        } else {
                            $('#name-error').hide();
                        }

                        if (res.errors.price) {
                            $('#price-error').show().text(res.errors.price);
                        } else {
                            $('#price-error').hide();
                        }
                        if (res.errors.max_people) {
                            $('#max_people-error').show().text(res.errors.max_people);
                        } else {
                            $('#max_people-error').hide();
                        }
                    }
                    if(res.status == 200){
                        location.href="{{ route('services.index')}}";
                        toastr.success("Service Updated",'Success',{timeOut: 5000});
                    }
                },
                error: function (data) {
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
                });
            });
        });
    </script>
@endsection