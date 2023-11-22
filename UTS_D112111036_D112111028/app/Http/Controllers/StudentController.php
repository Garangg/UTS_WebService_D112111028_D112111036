<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        $students = Student::OrderBy("id", "ASC")->paginate(10)->toArray();
            $response = [
                "total_count" => $students["total"],
                "limit" => $students["per_page"],
                "pagination" => [
                    "next_page" => $students["next_page_url"],
                    "current_page" => $students["current_page"]
                ],
                "data" => $students["data"],
            ];

        if ($acceptHeader === "application/json"){
            return response()->json($response,200);
        }elseif ($acceptHeader === "application/xml"){
            $xml = new \SimpleXMLElement('<students/>');
            foreach ($students['data'] as $item) {
                $xmlItem = $xml->addChild('student');
                $xmlItem->addChild('id', $item['id']);
                $xmlItem->addChild('name', $item['name']);
                $xmlItem->addChild('email', $item['email']);
                $xmlItem->addChild('password', $item['password']);
                $xmlItem->addChild('address', $item['address']);
                $xmlItem->addChild('birthdate', $item['birthdate']);
                $xmlItem->addChild('phone_number', $item['phone_number']);
                $xmlItem->addChild('gender', $item['gender']);
                $xmlItem->addChild('is_admin', $item['is_admin']);
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
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'address' => 'required',
                    'birthdate' => 'required',
                    'phone_number' => 'required',
                    'gender' => 'required',
                ];
                $validator = Validator::make($input, $validationRules);
                

                if ($validator->fails()){
                    return response()->json($validator->errors(), 400);
                }
                $student = Student::create($input);
                
                return response()->json($student, 200);
            }
            elseif ($contentTypeHeader === 'application/xml'){
                $xmldata = $request->getContent(); //mengambil data xml
                $xml = simplexml_load_string($xmldata); //mengubah string xml menjadi object

                if ($xml === false){
                    return response('Bad Request', 400);
                }else{
                    $student = Student::create([
                        'name' => $xml->name,
                        'email' => $xml->email,
                        'password' => $xml->password,
                        'address' => $xml->address,
                        'birthdate' => $xml->birthdate,
                        'phone_number' => $xml->phone_number,
                        'gender' => $xml->gender,
                    ]);
                    if ($student->save()){
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
            $student = Student::find($id); //mencari post berdasarkan id

            if (!$student) {
                abort(404);
            }

            return response()->json($student, 200);
        }elseif ($acceptHeader === "application/xml"){
            $student = Student::find($id); //mencari student berdasarkan id

            if (!$student) {
                abort(404);
            }
            
            $xml = new \SimpleXMLElement('<student/>');
            $xmlItem = $xml->addChild('student');
                $xmlItem->addChild('id', $student['id']);
                $xmlItem->addChild('name', $student['name']);
                $xmlItem->addChild('email', $student['email']);
                $xmlItem->addChild('password', $student['password']);
                $xmlItem->addChild('address', $student['address']);
                $xmlItem->addChild('birthdate', $student['birthdate']);
                $xmlItem->addChild('phone_number', $student['phone_number']);
                $xmlItem->addChild('gender', $student['gender']);
                $xmlItem->addChild('created_at', $student['created_at']);
                $xmlItem->addChild('updated_at', $student['updated_at']);

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
                $student = Student::find($id);

                if (!$student) {
                    abort(404);
                }

                $validationRules = [
                    'name' => 'required|min:3',
                    'email' => 'required|email|unique:students,email',
                    'password' => 'required|min:6',
                    'address' => 'required',
                    'birthdate' => 'required',
                    'phone_number' => 'required',
                    'gender' => 'required',
                ];
                $validator = Validator::make($input, $validationRules);

                if ($validator->fails()){
                    return response()->json($validator->errors(), 400);
                }

                $student->fill($input);
                $student->save();
                return response()->json($student, 200);
            }elseif ($contentTypeHeader === "application/xml"){
                $xmldata = $request->getContent(); //mengambil data xml
                $xml = simplexml_load_string($xmldata); //mengubah string xml menjadi object

                if ($xml === false){
                    return response('Bad Request', 400);
                }else{
                    $student = Student::find($id);
                    if (!$student) {
                        abort(404);
                    }
                    $student->fill([
                        'name' => $xml->name,
                        'email' => $xml->email,
                        'password' => $xml->password,
                        'address' => $xml->address,
                        'birthdate' => $xml->birthdate,
                        'phone_number' => $xml->phone_number,
                        'gender' => $xml->gender
                    ]);
                    if ($student->save()){
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
            $student = Student::find($id);

            if (!$student) {
                abort(404);
            }

            $student->delete($id);
            $message = ['message' => 'deleted successfully', 'student_id' => $id];
            return response()->json($message, 200);
        }elseif ($acceptHeader==="application/xml"){
            $student = Student::find($id);

            if (!$student) {
                abort(404);
            }

            if($student->delete()){
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
