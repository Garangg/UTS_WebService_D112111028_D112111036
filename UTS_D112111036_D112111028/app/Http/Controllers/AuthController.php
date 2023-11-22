<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{

    public function register(Request $request){
        $input = $request->all();

        $validator = Validator::make($input,[
            'name' => 'required|string',
            'email' => 'required|email|unique:students',
            'password' => 'required|string|confirmed',
            'address' => 'required|string',
            'birthdate' => 'required|date',
            'phone_number' => 'required|string',
            'gender' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }

        $student = new Student;
        $student->name = $request->input('name');
        $student->email = $request->input('email');
        $student->password = app('hash')->make($request->input('password'));
        $student->address = $request->input('address');
        $student->birthdate = $request->input('birthdate');
        $student->phone_number = $request->input('phone_number');
        $student->gender = $request->input('gender');

        $student->save();

        return response()->json([
            'message' => 'Successfully created student!'
        ],201);
    }

    public function login(Request $request){
        $input = $request->all();

        $validator = Validator::make($input,[
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }

        $credentials = $request->only(['email','password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'],401);
        }
        return response()->json([
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 3600
        ]);
    }
}