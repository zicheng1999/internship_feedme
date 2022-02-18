<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Robot;

class RobotController extends Controller
{
    public function create_robot(Request $req){
        $robot = Robot::create([
            'name' => $req->input('name'),
            'status' => "ready",
        ]);

        $msg = "Robot was created.";
        return response()->json(['msg'=> $msg, 'robot' => $robot]);
    } // end create_robot
    
    public function remove_robot(Request $req){

        //$last = Robot::find();
        $last = Robot::latest()->first();
        $last_temp = $last;
        $last->delete(); 

        $msg = "Robot was removed.";
        return response()->json(['msg'=> $msg, 'robot'=> $last_temp]);
    } // end create_robot

} // end RobotController
