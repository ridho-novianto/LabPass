<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dosen & Aslab - LabPass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-8 max-w-md w-full space-y-6">
        
        <div class="text-center space-y-1">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center font-bold text-white text-xl mx-auto mb-3 shadow-md shadow-emerald-600/20">
                LP
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Masuk Control Center</h1>
            <p class="text-xs text-slate-500">Otoritas Verifikasi APD & Jadwal Laboratorium</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Quick 1-Click Role Login for Evaluation -->
        <div class="space-y-2.5">
            <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Masuk Cepat Demo:</span>
            
            <form action="{{ route('login.post') }}" method="POST" class="space-y-2">
                @csrf
                @foreach($users as $user)
                    <button type="submit" name="user_id" value="{{ $user->id }}"
                            class="w-full p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-500 bg-slate-50 hover:bg-emerald-50/40 text-left transition flex items-center justify-between group">
                        <div>
                            <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition">{{ $user->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $user->email }}</div>
                        </div>
                        <span class="text-[10px] font-bold uppercase font-mono px-2 py-0.5 rounded-full {{ $user->role === 'dosen' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $user->role }}
                        </span>
                    </button>
                @endforeach
            </form>
        </div>

        <div class="relative flex py-1 items-center">
            <div class="flex-grow border-t border-slate-200"></div>
            <span class="flex-shrink mx-3 text-[11px] font-semibold text-slate-400 uppercase">Atau Email & Password</span>
            <div class="flex-grow border-t border-slate-200"></div>
        </div>

        <!-- Standard Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Institusi</label>
                <input type="email" name="email" value="dosen@labpass.test" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" value="password" required
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm">
                Masuk ke Dashboard &rarr;
            </button>
        </form>

        <div class="pt-2 text-center border-t border-slate-100">
            <a href="{{ route('verifikasi.index') }}" class="text-xs text-slate-500 hover:text-slate-900 font-medium">
                &larr; Kembali ke Portal Mahasiswa
            </a>
        </div>

    </div>
</body>
</html>
