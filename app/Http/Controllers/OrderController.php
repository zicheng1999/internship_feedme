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
                'processing_time' => "10",
                'status' => "pending",
                'robot_id' => $robot->id
            ]);
            $robot->status = "processing";
            $robot->save();
        }else{
            $order = Order::create([
                'customer_name' => $req->name,
                'type' => "1",
                'processing_time' => "10",
                'status' => "pending",
            ]);
        }


        $msg = "Order was created.";
        return response()->json(['msg'=>$msg, 'order'=>$order]);
    } // end create_order

    public function create_vip_order(Request $req){
        $order = Order::create([
            'customer_name' => $req->name,
            'type' => "2",
            'processing_time' => "10",
            'status' => "pending",
        ]);

        $msg = "Order was created.";
        return response()->json(['msg'=>$msg, 'order'=>$order]);
    } // end create_order

    public function read_pending(Request $req){
        $orders = Order::where('status', "pending")->get();
        
        $msg = "<table class='table'>"; 
        $msg = $msg . "<tr>";   
            $msg = $msg . "<th style='width: 20%;'>ID</th>";   
            $msg = $msg . "<th  style='width: 20%;'>Customer Name</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Type</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Status</th>"; 
            $msg = $msg . "<th  style='width: 20%;'>Dif in Seconds</th>";
            $msg = $msg . "<th  style='width: 20%;'>Robot</th>";
        $msg = $msg . "</tr>"; 
        
        $order_type = "Normal Order";

        foreach ($orders as $order) {
            if($order->type == "2"){
                $order_type = "VIP Order";
            }
            
            if($order->robot_id == null){
                $robot = Robot::where('status', "ready")->orderBy('id', 'ASC')->get()->first();
                if($robot != null){
                    $order->robot_id = $robot->id;
                    $robot->status = "processing";
                    $robot->save();
                } 
            } 

            $now = Carbon::now();
            $dif = date_diff($now , $order->created_at);
            $dif = $dif->format("%S");

            if($dif >= 10){
                $order->status = 'complete';

                if($order->save()){
                    $robot = Robot::where('id', $order->robot_id)->get()->first();
                    $robot->status = "ready";
                    $robot->save();
                }
            }
            
            $msg = $msg . "<tr>";
                $msg = $msg . "<td>" . $order->id . "</td>";
                $msg = $msg . "<td>" . $order->customer_name . "</td>";
                $msg = $msg . "<td>" . $order_type . "</td>";
                $msg = $msg . "<td>" . $order->status . "</td>";
                $msg = $msg . "<td>" . $dif . "</td>";
                $msg = $msg . "<td>" . $order->robot_id . "</td>";
            $msg = $msg . "</tr>";
        }
        $msg = $msg . "</table>";

        return response()->json(['msg' => $msg, 'orders' => $orders]);
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
