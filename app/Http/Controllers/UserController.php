<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        $messages = [
            'name.required' => 'Lietotājavārds ir obligāts.',
            'name.unique' => 'Lietotājavārds jau ir aizņemts.',
            'email.required' => 'E-pasta adrese ir obligāta.',
            'email.email' => 'E-pasta adresei jābūt derīgai.',
            'email.unique' => 'E-pasta adrese jau ir aizņemta.',
            'password.required' => 'Parole ir obligāta.',
            'password.min' => 'Parolei jābūt vismaz :min rakstzīmēm garai.',
            'role.required' => 'Pieejas līmenis ir obligāts.',
        ];

        // Validate the request with custom error messages
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|min:1'
        ], $messages);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validācijas neizpildījās',
                'errors' => $validation->errors()
            ],422);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' =>  $request->role,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Lietotājs veiksmīgi izveidots',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $messages = [
            'name.required' => 'Lietotājvārds ir obligāts.',
            'name.exists' => 'Lietotājvārds neeksistē.',
            'password.required' => 'Parole ir obligāta.',
            'password.min' => 'Parolei jābūt vismaz :min rakstzīmēm garai.',
        ];

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255|exists:users,name',
            'password' => 'required|string|min:8',
        ], $messages);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validācijas neizpildījās',
                'errors' => $validation->errors()
            ], 422);
        }

        $user = User::where('name', $request->name)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            $cookie = cookie('sanctum_token', $token, 60 * 24, null, null, false, true);

            return response()->json([
                'status' => 200,
                'message' => 'Pieslēgšanās veiksmīga',
                'token' => $token,
                'role' => $user->role
            ])->cookie($cookie);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Nepareiza parole',
                'errors' => [
                    'password' => 'Nepareiza parole'
                ]
            ], 401);
        }
    }

    public function getAllUsers(Request $request)
    {
        $allUsers = User::all();

        if(count($allUsers) < 1){
            return response()->json([
                'status' => 200,
                'message' => 'Datu bāzē nav lietotāju',
            ], 200);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Veiksmīgi atrasti lietotāji',
            'users' => $allUsers
        ], 200);
    }
    public function deleteUser(Request $request)
    {
        $messages = [
            'id.exists' => 'ID Nepastāv.',
        ];

        $validation = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ], $messages);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Kļūda pārbaudot sniegtos datus',
                'errors' => $validation->errors()
            ], 422);
        }

        $id = $request->id;
        $user = User::find($id);

        if ($user) {
            $user->delete();
            $allUsers = User::all();

            return response()->json([
                'status' => 200,
                'message' => 'Veiksmīgi izdēsts lietotājs',
                'users' => $allUsers
            ], 200);
        }

        return response()->json([
            'status' => 404,
            'message' => 'Lietotājs nav atrasts',
        ], 404);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Atslēgšanās veiksmīga',
        ])->cookie('token', '', -1);
    }
    public function editUser(Request $request)
    {
        $messages = [
            'id.required' => 'ID ir obligāts.',
            'id.exists' => 'ID nepastāv.',
            'name.required' => 'Lietotāja vārds ir obligāts.',
            'name.unique' => 'Lietotāja vārds jau ir aizņemts.',
            'email.required' => 'E-pasta adrese ir obligāta.',
            'email.email' => 'E-pasta adresei jābūt derīgai.',
            'email.unique' => 'E-pasta adrese jau ir aizņemta.',
            'password.min' => 'Parolei jābūt vismaz :min rakstzīmēm garai.',
            'role.required' => 'Pieejas līmenis ir obligāt.',
        ];

        $validation = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore($request->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($request->id),
            ],
            'password' => 'sometimes|string|min:8',
            'role' => 'required|string|min:1',
        ], $messages);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validācijas neizpildījās',
                'errors' => $validation->errors()
            ], 422);
        }

        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Lietotājs nav atrasts',
            ], 404);
        }

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->role = $request->role;
        $user->save();

        $allUsers = User::all();

        return response()->json([
            'status' => 200,
            'message' => 'Lietotājs veiksmīgi atjaunināts',
            'user' => $user,
            'users' => $allUsers,
        ], 200);
    }

}
