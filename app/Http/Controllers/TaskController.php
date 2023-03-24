<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class TaskController extends Controller {
    
    public function index(Request $request){
        $query = DB::table('tasks');
        if ($request->has('user')) {
            $task = $query->where('assign_to','LIKE', $request->get('user'));
            return $task->get();
        }
        else if ($request->has('search')){
            $search = $query->where('title','LIKE', $request->get('search').'%')
                            ->orwhere('description', 'LIKE', '%'.$request->get('search').'%')
                            ->orwhere('status', 'LIKE', $request->get('search').'%')
                            ->orwhere('assign_to', 'LIKE', $request->get('search').'%');
            return $search->get();
        } else {
            return Task::all();
        }
        
    }

    public function test(Request $request){
        $task = User::where('email', $request->email)->with('tasks')->first();
        return $task;
    }

    public function get($id){
        $task = Task::where('id',$id)->first();
        return response()->json($task);
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
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
            'message' => 'Task created successfully',
            'task' => $task
        ], 200);

    }

    public function update(Request $request, $id){

        if (Task::where('task_id', $id)->exists()){

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'status' => 'required',
                'created_by' => 'required',
                'assign_to' => 'required',
            ]);
    
            if ($validator->fails()) {
                return array(
                    'success' => false,
                    'message' => $validator->errors()->all()
                );
            }

            $task = Task::find($id);
            $task->title = $request->title;
            $task->description = $request->description;
            $task->status = $request->status;
            $task->created_by = $request->created_by;
            $task->assign_to = $request->assign_to;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!'
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Task does not exist!'
            ], 200);
        }

    }

    public function assign(Request $request, $id){
        if (Task::where('task_id', $id)->exists()){

            $validator = Validator::make($request->all(), [
                'assign_to' => 'required',
            ]);
    
            if ($validator->fails()) {
                return array(
                    'success' => false,
                    'message' => $validator->errors()->all()
                );
            }

            $task = Task::where('task_id', $id);
            $task->assign_to = $request->assign_to;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!'
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Task does not exist!'
            ], 200);
        }
    }

    public function delete($id){
        if (Task::where('task_id', $id)->exists()){
            $task = Task::where('task_id', $id);
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully!'
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Task does not exist!'
            ], 200);
        }
    }


    public function deleteAll(){
        return Task::truncate();
        
    }

}
