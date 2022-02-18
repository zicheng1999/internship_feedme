<!DOCTYPE html>
<html lang="en">
<head>
  <title>MacDonoald Cooking Bots</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container-fluid p-5 bg-primary text-white text-center">
  <h1>McDonald's</h1>
  <p>Automated Cooking Bots</p> 
  <h2 id="msg">Msg will be shown here</h2>
</div>
  
<div class="container mt-5">
  <div class="row">

    <div class="col-sm-2"> </div>

    <div class="col-sm-4">
        <div class="card text-center">
                <div class="card-header">
                    Robot
                </div>
                <div class="card-body">
                    <label for="robot" class="form-label">Robot Name</label>
                    <input type="text" class="form-control" id="robot_name" name="name">
                    <br>

                    <!-- <form action="/remove/robot" method="post"> -->
                        <!-- <input type="hidden" name="_token" value="{{ csrf_token() }}" /> -->
                        <!-- <input type="submit"  class="btn btn-danger" value="- Bot"> -->
                        <button id="btnRemoveRobot" class="btn btn-danger">- Bot</button>
                    <!-- </form> -->
                    
                    <a href="#" class="btn btn-success" id="btnCreateRobot">+ Bot</a>

                </div>
            </div>
    </div> <!-- end col-sm-4 -->

    <div class="col-sm-4">
        <div class="card text-center">
            <div class="card-header">
                Order
            </div>
            <div class="card-body">
            <label for="robot" class="form-label">Customer Name</label>
                                       
                     <input type="text" class="form-control" id="customer_name" name="name"><br>
                     <button class="btn btn-success" id="btnNormalOrder">New Normal Order</button>
                     <button class="btn btn-success" id="btnVipOrder">New VIP Order</button>
            </div>
        </div>
    </div> <!-- end col-sm-4 -->

    <div class="col-sm-2"></div>
  </div> <!-- end row --> <br>

  <div class="row">
    <div class="col-sm-12">
            <div class="card text-center">
                <div class="card-header">
                    Processing
                </div>
                <div class="card-body" id="divProcessing">

                </div>
            </div>
        </div> <!-- end col-sm-12 -->
  </div> <!-- end row -->

  <br>

  <div class="row">
    <div class="col-sm-6">
            <div class="card text-center">
                <div class="card-header">
                    Pending
                </div>
                <div class="card-body" id="divPending">

                </div>
            </div>
        </div> <!-- end col-sm-6 -->

        <div class="col-sm-6">
            <div class="card text-center">
                <div class="card-header">
                    Complete
                </div>
                <div class="card-body">
                    <div id="divComplete"></div>
                </div>
            </div>
        </div> <!-- end col-sm-6 -->

  </div> <!-- end row -->

  </div> <!-- container mt-5 -->
</div> <!-- container-fluid -->

<br><br>

</body>
</html>

<script>
$(document).ready(function(){

$("#btnCreateRobot").click(function(){
    var name = $('#robot_name').val();
    var _token   = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: "/create/robot",
        type:"POST",
        data:{
            name:name,
            _token: _token
        },
        success:function(response){
            if(response){
                $('#msg').text("Robot with the name <" + response.robot.name + "> was created.");
                loadRobot();
            }else{
                $('#msg').text("There is an error.");
            }
        } // end success
    }); // end ajax
}); // end btnCreateRobot

$("#btnRemoveRobot").click(function(){  
    var id = 1; // todo
    var _token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: "/remove/robot",
        type:"POST",
        data:{
            id:id,
            _token: _token
        },
        success:function(response){
            if(response){
                $('#msg').text("Robot with the id <" + response.robot.id + "> was removed.");
                loadRobot();
            } // end if
            else{
                $('#msg').text("There is an error.");
            }
        } // end success
    }); // end ajax

    // Swal.fire({
    //     title: 'Are you sure?',
    //     text: "You won't be able to revert this!",
    //     icon: 'warning',
    //     showCancelButton: true,
    //     confirmButtonColor: '#3085d6',
    //     cancelButtonColor: '#d33',
    //     confirmButtonText: 'Yes, delete it!'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 url: "/remove/robot",
    //                 type:"GET",
    //                 data:{
    //                     id:id,
    //                     _token: _token
    //                 },
    //                 success:function(response){
    //                     if(response){
    //                         Swal.fire(
    //                         'Deleted!',
    //                         'Your file has been deleted.',
    //                         'success'
    //                         ) // end Swal.fire inner
    //                     } // end if
    //                     else{
    //                         $('#msg').text("There is an error.");
    //                     }
    //                 } // end success
    //             }); // end ajax
    //         } // end if
    //     }) // end Swal.fire
  }); // end btnRemoveRobot

$("#btnNormalOrder").click(function(){
    $("#btnNormalOrder").prop('disabled', true); 

    if($("#customer_name").val() == ""){
        alert("Customer Name must be filled in.");
        $('#msg').text("Customer Name must be filled in.");
        $("#btnNormalOrder").prop('disabled', false); 
        return;
    }

    var name = $('#customer_name').val();
    var _token   = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: "/create/normal_order",
        type:"POST",
        data:{
            name:name,
            _token: _token
        },
        success:function(response){
            if(response){
                loadPending();
                $('#msg').text("Order with the ID <" + response.order.id + "> was created.");
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'New normal order has been created',
                    showConfirmButton: false,
                    timer: 800
                }) // end Swal.fire
                setTimeout(function(){$("#btnNormalOrder").removeAttr("disabled")}, 1000);
            }else{
                $('#msg').text("There is an error.");
                $("#btnNormalOrder").removeAttr("disabled");
            }
        } // end success
    }); // end ajax
}); // end btnNormalOrder

$("#btnVipOrder").click(function(){
    $("#btnVipOrder").prop('disabled', true); 

    if($("#customer_name").val() == ""){
        alert("Customer Name must be filled in.");
        $('#msg').text("Customer Name must be filled in.");
        $("#btnVipOrder").prop('disabled', false); 
        return;
    }

    var name = $('#customer_name').val();
    var _token   = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: "/create/vip_order",
        type:"POST",
        data:{
            name:name,
            _token: _token
        },
        success:function(response){
            if(response){
                loadPending();
                $('#msg').text("Order with the ID <" + response.order.id + "> was created.");
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'New VIP order has been created',
                    showConfirmButton: false,
                    timer: 800
                }) // end Swal.fire
                setTimeout(function(){$("#btnVipOrder").removeAttr("disabled")}, 1000);
            }else{
                $('#msg').text("There is an error.");
                $("#btnVipOrder").removeAttr("disabled");
            }
        } // end success
    }); // end ajax
}); // end btnVipOrder

//--Load Pending--// 
function loadPending(){
    // alert("Load Pending");
    // count = count + 1;
    // $('#divPending').html(count);

    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: "/read/pending",
        type:"POST",
        data:{
            _token: _token
        },
        success:function(response){
            if(response){
                $('#divPending').html(response.msg);
            }else{
                $('#msg').text("There is an error.");
            }
        } // end success
    }); // end ajax
} // end loadPending

    loadPending();

    setInterval(function(){
        loadPending();
    }, 1200);
//--End Load Pending--// 

//--Load Complete--// 
function loadComplete(){
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: "/read/complete",
        type:"POST",
        data:{
            _token: _token
        },
        success:function(response){
            if(response){
                $('#divComplete').html(response.msg);
            }else{
                $('#msg').text("There is an error.");
            }
        } // end success
    }); // end ajax
} // end loadComplete

    loadComplete();

    setInterval(function(){
        loadComplete();
    }, 1200);
//--End Load Complete--// 

//--Load Robot--// 
function loadRobot(){
    // alert("Load Pending");
    // count = count + 1;
    // $('#divPending').html(count);

    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: "/read/robot",
        type:"POST",
        data:{
            _token: _token
        },
        success:function(response){
            if(response){
                $('#divProcessing').html(response.msg);
            }else{
                $('#msg').text("There is an error.");
            }
        } // end success
    }); // end ajax
} // end loadPending

    loadRobot();

    setInterval(function(){
        loadRobot();
    }, 1200);
//--End Load Pending--// 

}); // end doc ready
</script>
