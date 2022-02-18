<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Robot;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    
    public function create_normal_order(Request $req){
        $robot = Robot::where('status', "ready")->orderBy('id', 'ASC')->get()->first();

        if($robot != null){
            $order = Order::create([
                'customer_name' => $req->name,
                'type' => "1",
                'processing_time' => "20",
                'status' => "processing",
                'robot_id' => $robot->id,
                'queue' => 99,
            ]);
            $robot->status = "processing";
            $robot->save();
        }else{
            $robot = Robot::where('status', "processing")->orderBy('updated_at', 'ASC')->get()->first();
            $order = Order::create([
                'customer_name' => $req->name,
                'type' => "1",
                'processing_time' => "20",
                'status' => "pending",
                'robot_id' => $robot->id,
                'queue' => 99,
            ]);
        } // end else


        $msg = "Order was created.";
        return response()->json(['msg'=>$msg, 'order'=>$order]);
    } // end create_order

    public function create_vip_order(Request $req){
        $robot = Robot::where('status', "ready")->orderBy('id', 'ASC')->get()->first();

        if($robot != null){
            $order = Order::create([
                'customer_name' => $req->name,
                'type' => "2",
                'processing_time' => "20",
                'status' => "processing",
                'robot_id' => $robot->id,
                'queue' => 888,
            ]);
            $robot->status = "processing";
            $robot->save();
        }else{
            $robot = Robot::where('status', "processing")->orderBy('updated_at', 'ASC')->get()->first();
            $order = Order::create([
                'customer_name' => $req->name,
                'type' => "2",
                'processing_time' => "20",
                'status' => "pending",
                'robot_id' => $robot->id,
                'queue' => 888,
            ]);
        } // end else

        $msg = "Order was created.";
        return response()->json(['msg'=>$msg, 'order'=>$order]);
    } // end create_order

    public function read_pending(Request $req){

        $msg = "<table class='table'>"; 

        $msg = $msg . "<tr>";   
            $msg = $msg . "<th style='width: 20%;'>ID</th>";   
            $msg = $msg . "<th  style='width: 20%;'>Customer Name</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Type</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Status</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Count Down (s)</th>";
            $msg = $msg . "<th  style='width: 20%;'>Queue</th>";
        $msg = $msg . "</tr>"; 
        
        // Start Processing Orders
        $processing_orders = Order::where('status', "processing")->orderBy('updated_at', 'ASC')->get();
        $processing_orders_count = $processing_orders->count();

        $count = 0;
        foreach ($processing_orders as $order) {
            $count = $count + 1;
            $order->queue = $count;
            $order->save();    
        }
        // End Processing Orders

        // Start VIP Orders
        $vip_orders = Order::where('status', "pending")
            ->where('type', "2")
            ->orderBy('updated_at', 'ASC')->get();
        $vip_orders_count_A = $processing_orders_count;

        foreach ($vip_orders as $order) {
            $vip_orders_count_A = $vip_orders_count_A + 1;
            $order->queue = $vip_orders_count_A;
            $order->save();
        }
        // End VIP Orders

        
        // Start Normal Orders
        $vip_orders_count_B = $vip_orders->count();
        $normal_orders = Order::where('status', "pending")
            ->where('type', "1")
            ->orderBy('updated_at', 'ASC')->get();
        $normal_orders_count = $processing_orders_count +  $vip_orders->count();

        foreach ($normal_orders as $order) {
            $normal_orders_count = $normal_orders_count + 1;
            $order->queue = $normal_orders_count;
            $order->save();
        }
        // End Normal Orders

        $orders = Order::where('status', "pending")
            ->orWhere('status', "processing")
            ->orderBy('queue', 'ASC')
            ->get();

        foreach ($orders as $order) {
            if($order->type == "2"){
                $order_type = "VIP Order";
            }else{
                $order_type = "Normal Order";
            }
            
            if($order->status == "pending"){
                $robot = Robot::where('status', "ready")->orderBy('updated_at', 'ASC')->get()->first();
                if($robot != null){
                    $order->robot_id = $robot->id;
                    $order->status = "processing";
                    if($order->save()){
                        $robot->status = "processing";
                        $robot->save();
                    }
                } 
            } 

            // $now = Carbon::now();
            // $dif = date_diff($now , $order->created_at);
            // $dif = $dif->format("%S");
            
            if($order->status == "processing"){
                $order->processing_time = $order->processing_time - 1;                    
                $order->save();
            }

            if($order->processing_time <= 0){
                $order->status = 'complete';

                if($order->save()){
                    $robot = Robot::where('id', $order->robot_id)->get()->first();
                    $robot->status = "ready";
                    $robot->save();
                }
            } // end deduct processing time

            if($order->status == "processing"){
                $msg = $msg . "<tr>";
                    $msg = $msg . "<td style='color: dodgerblue;'><b>" . $order->id . "</b></td>";
                    $msg = $msg . "<td style='color: dodgerblue;'><b>" . $order->customer_name . "</b></td>";
                    $msg = $msg . "<td style='color: dodgerblue;'><b>" . $order_type . "</b></td>";
                    $msg = $msg . "<td style='color: dodgerblue;'><b>" . $order->status . "</b></td>";
                    $msg = $msg . "<td style='color: dodgerblue;'><b>" . $order->processing_time. "</b></td>";
                    $msg = $msg . "<td style='color: dodgerblue;'><b>" . $order->queue . "</b></td>";
                $msg = $msg . "</tr>";
            }
            elseif($order->status == "complete"){
                $msg = $msg . "<tr>";
                    $msg = $msg . "<td style='color: red;'><b>" . $order->id . "</b></td>";
                    $msg = $msg . "<td style='color: red;'><b>" . $order->customer_name . "</b></td>";
                    $msg = $msg . "<td style='color: red;'><b>" . $order_type . "</b></td>";
                    $msg = $msg . "<td style='color: red;'><b>" . $order->status . "</b></td>";
                    $msg = $msg . "<td style='color: red;'><b>" . $order->processing_time. "</b></td>";
                    $msg = $msg . "<td style='color: red;'><b>" . $order->queue . "</b></td>";
                $msg = $msg . "</tr>";
            }
            elseif($order->type == "2"){
                $msg = $msg . "<tr>";
                    $msg = $msg . "<td style='color: orange;'><b>" . $order->id . "</b></td>";
                    $msg = $msg . "<td style='color: orange;'><b>" . $order->customer_name . "</b></td>";
                    $msg = $msg . "<td style='color: orange;'><b>" . $order_type . "</b></td>";
                    $msg = $msg . "<td style='color: orange;'><b>" . $order->status . "</b></td>";
                    $msg = $msg . "<td style='color: orange;'><b>" . $order->processing_time. "</b></td>";
                    $msg = $msg . "<td style='color: orange;'><b>" . $order->queue . "</b></td>";
                $msg = $msg . "</tr>";
            }
            else{
                $msg = $msg . "<tr>";
                    $msg = $msg . "<td>" . $order->id . "</td>";
                    $msg = $msg . "<td>" . $order->customer_name . "</td>";
                    $msg = $msg . "<td>" . $order_type . "</td>";
                    $msg = $msg . "<td>" . $order->status . "</td>";
                    $msg = $msg . "<td>" . $order->processing_time. "</td>";
                    $msg = $msg . "<td>" . $order->queue . "</td>";
                $msg = $msg . "</tr>";
            } // end else html

        } // end foreach
        $msg = $msg . "</table>";

        return response()->json(['msg' => $msg]);
    } // end read_pending

    public function read_complete(Request $req){
        $orders = Order::where('status', "complete")->orderBy('id', 'DESC')->get();

        $msg = "<table class='table'>"; 
        $msg = $msg . "<tr>";   
            $msg = $msg . "<th style='width: 25%;'>ID</th>";   
            $msg = $msg . "<th  style='width: 25%;'>Customer Name</th>"; 
            $msg = $msg . "<th  style='width: 25%;'>Type</th>"; 
            $msg = $msg . "<th  style='width: 25%;'>Status</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Robot</th>";
        $msg = $msg . "</tr>"; 
        
        $order_type = "Normal Order";

        foreach ($orders as $order) {
            if($order->type == "2"){
                $order_type = "VIP Order";
            }

            $msg = $msg . "<tr>";
                $msg = $msg . "<td>" . $order->id . "</td>";
                $msg = $msg . "<td>" . $order->customer_name . "</td>";
                $msg = $msg . "<td>" . $order_type . "</td>";
                $msg = $msg . "<td>" . $order->status . "</td>";
                $msg = $msg . "<td>" . $order->robot_id . "</td>";                
            $msg = $msg . "</tr>";
        }
        $msg = $msg . "</table>";

        return response()->json(['msg' => $msg, 'orders' => $orders]);
    } // end read_complete

    public function read_robot(Request $req){
        $orders = Order::where('status', "complete")->get();
        $robots = Robot::all();
        $msg = "<div class='row'>";
        
        foreach ($robots as $robot) {
            $msg = $msg ."<div class='col-sm-4 border rounded-3 border-primary mb-2'>";
            $msg = $msg . "<p>" . "Robot ID: " . $robot->id ."</p>";
            $msg = $msg . "<p>" . "Robot Name: " . $robot->name ."</p>";
            $msg = $msg . "<p>" . "Robot Status: " . $robot->status ."</p>";
            $msg = $msg . "</div>";
        } // end foreach

        $msg = $msg . "</div>";

        return response()->json(['msg' => $msg, 'orders' => $orders]);
    } // end read_complete

} // end OrderController    
