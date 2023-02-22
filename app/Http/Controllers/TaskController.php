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

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully'
        ], 200);

    }

    public function update(Request $request, $id){

        $existingTask = $request->auth->task()->find($id);
        if (!$existingTask instanceof Task) {
            return response()->json([
                'success' => false,
                'message' => 'Task does not exist!'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Task does exist!'
            ]);
        }

        // $task = Task::findOrFail($id);
        // $task->title = $request->title;
        // $task->description = $request->description;
        // $task->status = $request->status;
        // $task->created_by = $request->created_by;
        // $task->assign_to = $request->assign_to;
        // $task->save();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Task updated successfully!'
        // ], 200);

    }

}
