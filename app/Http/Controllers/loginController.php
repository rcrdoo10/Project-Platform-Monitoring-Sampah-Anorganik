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
        $user = akun::where('id_user', $request->id_user)->first();
        
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan!');
        }
        
        if (!Hash::check($request->password, $user->PASSWORD)) {
            return back()->with('error', 'Password salah!');
        }
        
        // ✅ TAMBAHKAN 'user_poin' KE SESSION
        session([
            'user_id' => $user->id_user,
            'user_nama' => $user->nama,
            'user_role' => $user->ROLE,
            'user_poin' => $user->total_poin ?? 0  // ← INI YANG PENTING
        ]);
        
        // Untuk nentuin tampilan
        switch ($user->ROLE) {
            case 'warga':
                return redirect('/wDashboard')->with('success', 'Login berhasil!');
            case 'kopma':
                return redirect('/kDashboard')->with('success', 'Login berhasil!');
            case 'petugas':
            case 'psl':
            case 'rektorat':
                return redirect('/laporan')->with('success', 'Login berhasil!');
            default:
                return redirect('/');
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}