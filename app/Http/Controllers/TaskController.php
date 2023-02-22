<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller {
    
    public function index(){
        return Task::all();
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'status' => 'required|in:active,inactive',
            'created_by' => 'required',
            'assign_to' => 'required',
        ]);

        if ($validator->fails()) {
            return array(
                'success' => false,
                'message' => $validator->errors()->all()
            );
        }
        
        $task = new Task;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = $request->status;
        $task->created_by = $request->created_by;
        $task->assign_to = $request->assign_to;
        $task->save();

        return response()->json(['success' => true], 200);

    }

}
