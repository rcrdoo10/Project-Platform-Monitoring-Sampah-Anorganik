<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\akun;
use Illuminate\Support\Facades\Hash;

class loginController extends Controller
{
    public function index()
    {
        if (session('user_id')) {
            $role = session('user_role');
            if ($role == 'warga') return redirect('/wDashboard');
            if ($role == 'kopma') return redirect('/kDashboard');
            return redirect('/laporan');
        }
        return view('login');
    }

    public function autentikasi(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'password' => 'required'
        ]);
        
        $user = akun::where('id_user', $request->id_user)->first();
        
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan!');
        }
        
        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah!');
        }
        
        $request->session()->regenerate();
        
        session([
            'user_id' => $user->id_user,
            'user_nama' => $user->nama,
            'user_role' => $user->role,
            'user_poin' => $user->total_poin ?? 0
        ]);
        
        $request->session()->save();
        
        if ($user->role == 'warga') {
            return redirect()->intended('/wDashboard');
        } elseif ($user->role == 'kopma') {
            return redirect()->intended('/kDashboard');
        } else {
            return redirect()->intended('/laporan');
        }
    }

    public function logout(Request $request)
    {
        session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}