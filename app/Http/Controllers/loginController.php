<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\akun;
use Illuminate\Support\Facades\Hash;

class loginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function autentikasi(Request $request)
    {
        // Validasi
        $request->validate([
            'id_user' => 'required',
            'password' => 'required'
        ]);

        // Cari user
        $user = akun::where('id_user', $request->id_user)->first();

        // Cek user dan password
        if ($user && Hash::check($request->password, $user->PASSWORD)) {
            session([
                'user_id' => $user->id_user,
                'user_nama' => $user->nama,
                'user_role' => $user->ROLE,
                'user_poin' => $user->total_poin
            ]);

            return redirect('/')->with('success', 'Login berhasil!');
        }

        return back()->with('error', 'ID User atau password salah!');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login')->with('success', 'Logout berhasil!');
    }
}