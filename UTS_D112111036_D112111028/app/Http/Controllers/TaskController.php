<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        $tasksQuery = Task::orderBy("id", "ASC");

        // Filter by student_id if the parameter exists in the request
        if ($request->has('student_id')) {
            $tasksQuery->where('student_id', $request->input('student_id'));
        }

        $tasks = $tasksQuery->paginate(10)->toArray();
        $response = [
            "total_count" => $tasks["total"],
            "limit" => $tasks["per_page"],
            "pagination" => [
                "next_page" => $tasks["next_page_url"],
                "current_page" => $tasks["current_page"]
            ],
            "data" => $tasks["data"],
        ];

        if ($acceptHeader === "application/json"){
            return response()->json($response,200);
        }elseif ($acceptHeader === "application/xml"){
            $xml = new \SimpleXMLElement('<task/>');
            foreach ($tasks['data'] as $item) {
                $xmlItem = $xml->addChild('tasks');
                $xmlItem->addChild('id', $item['id']);
                $xmlItem->addChild('student_id', $item['student_id']);
                $xmlItem->addChild('task_description', $item['task_description']);
                $xmlItem->addChild('deadline', $item['deadline']);
                $xmlItem->addChild('status', $item['status']);
                $xmlItem->addChild('created_at', $item['created_at']);
                $xmlItem->addChild('updated_at', $item['updated_at']);
            }
            return $xml->asXML();
        }else{
            return response('Not Acceptable!', 406);
        }
    }

    public function store(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml'){
            $contentTypeHeader = $request->header('Content-Type');

            if ($contentTypeHeader === 'application/json'){
                $input = $request->all();
                $validationRules = [
                    'student_id' => 'required',
                    'task_description' => 'required',
                    'deadline' => 'required',
                    'status' => 'required',  
                ];
                $validator = Validator::make($input, $validationRules);

                if ($validator->fails()){
                    return response()->json($validator->errors(), 400);
                }

                $task = Task::create($input);
                return response()->json($task, 200);
            }
            elseif ($contentTypeHeader === 'application/xml'){
                $xmldata = $request->getContent(); //mengambil data xml
                $xml = simplexml_load_string($xmldata); //mengubah string xml menjadi object

                if ($xml === false){
                    return response('Bad Request', 400);
                }else{
                    $task = Task::create([
                        'student_id' => $xml->student_id,
                        'task_description' => $xml->task_description,
                        'deadline' => $xml->deadline,
                        'status' => $xml->status,
                    ]);
                    if ($task->save()){
                        return $xml ->asXML();
                    }else{
                        return response('Internal Server Error', 500);
                    }
                }
            }
        }else{
            return response('Not Acceptable!', 406);
        }
    }

    public function show(Request $request,$id)
    {
        $acceptHeader = $request->header("Accept");
        if ($acceptHeader === "application/json"){
            $task = Task::find($id); //mencari post berdasarkan id

            if (!$task) {
                abort(404);
            }

            return response()->json($task, 200);
        }elseif ($acceptHeader === "application/xml"){
            $task = Task::find($id); //mencari task berdasarkan id

            if (!$task) {
                abort(404);
            }
            
            $xml = new \SimpleXMLElement('<task/>');
            $xmlItem = $xml->addChild('tasks');
                $xmlItem->addChild('id', $task['id']);
                $xmlItem->addChild('student_id', $task['student_id']);
                $xmlItem->addChild('task_description', $task['task_description']);
                $xmlItem->addChild('deadline', $task['deadline']);
                $xmlItem->addChild('status', $task['status']);
                $xmlItem->addChild('created_at', $task['created_at']);
                $xmlItem->addChild('updated_at', $task['updated_at']);

            return $xml->asXML();
        }else{
            return response('Not Acceptable!', 406);
        }
    }

    public function update(request $request,$id)
    {
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml'){
            $contentTypeHeader = $request->header('Content-Type');

            if ($contentTypeHeader === 'application/json'){
                $input = $request->all();
                $task = Task::find($id);

                if (!$task) {
                    abort(404);
                }

                $validationRules = [
                    'student_id' => 'required',
                    'task_description' => 'required',
                    'deadline' => 'required',
                    'status' => 'required',  
                ];
                $validator = Validator::make($input, $validationRules);

                if ($validator->fails()){
                    return response()->json($validator->errors(), 400);
                }

                $task->fill($input);
                $task->save();
                return response()->json($task, 200);
            }elseif ($contentTypeHeader === "application/xml"){
                $xmldata = $request->getContent(); //mengambil data xml
                $xml = simplexml_load_string($xmldata); //mengubah string xml menjadi object

                if ($xml === false){
                    return response('Bad Request', 400);
                }else{
                    $task = Task::find($id);
                    if (!$task) {
                        abort(404);
                    }
                    $task->fill([
                        'student_id' => $xml->student_id,
                        'task_description' => $xml->task_description,
                        'deadline' => $xml->deadline,
                        'status' => $xml->status,
                    ]);
                    if ($task->save()){
                        return $xml ->asXML();
                    }else{
                        return response('Internal Server Error', 500);
                    }
                }
            }else{
                return response('Not Acceptable!', 406);
            }
        }
    }

    public function destroy(Request $request,$id)
    {
        $acceptHeader = $request->header("Accept");
        if ($acceptHeader==="application/json" ){
            $task = Task::find($id);

            if (!$task) {
                abort(404);
            }

            $task->delete($id);
            $message = ['message' => 'deleted successfully', 'id' => $id];
            return response()->json($message, 200);
        }elseif ($acceptHeader==="application/xml"){
            $task = Task::find($id);

            if (!$task) {
                abort(404);
            }

            if($task->delete()){
                $xml = new \SimpleXMLElement('<message/>');
                $xml->addChild('message', 'deleted successfully');
                $xml->addChild('id', $id);

                return $xml->asXML();
            }else{
                return response('Internal Server Error', 500);
            }

        }else{
            return response('Not Acceptable!', 406);
        }
    }
}