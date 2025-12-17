<x-layouts.auth title="Register">

    <h1
        class="text-3xl font-bold text-center bg-gradient-to-r from-pink-500 to-orange-400 bg-clip-text text-transparent mb-2">
        Create Account
    </h1>
    <p class="text-center text-gray-400 mb-6">Daftar akun baru</p>


    <form class="space-y-4" method="POST" action="/register">
        <?php echo csrf_field(); ?>

        <?php if(session('error')): ?>
            <div class="p-3 rounded bg-red-500 text-white text-center"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="p-3 rounded bg-red-800 text-white">
                <ul class="list-disc list-inside text-sm">
                    <?php foreach($errors->all() as $e): ?>
                        <li><?php echo e($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <input type="text" placeholder="Nama Lengkap" name="name" required value="<?php echo e(old('name')); ?>"
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-pink-500 outline-none">


        <input type="email" placeholder="Email" name="email" required value="<?php echo e(old('email')); ?>"
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-orange-400 outline-none">


        <input type="password" placeholder="Password" name="password" required
              class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-pink-500 outline-none" id="password" minlength="6">


        <button type="submit"
            class="w-full py-3 rounded-lg font-semibold text-black bg-gradient-to-r from-pink-500 to-orange-400 hover:opacity-90 transition">
            Register
        </button>
    </form>

        <script>
            // minimal client-side check so users see immediate feedback
            (function(){
                const form = document.querySelector('form[action="/register"]');
                if (!form) return;
                const password = document.getElementById('password');
                form.addEventListener('submit', function(e){
                    if (password && password.value.length < 6) {
                        e.preventDefault();
                        alert('Password harus minimal 6 karakter');
                        password.focus();
                    }
                });
            })();
        </script>


    <p class="text-center text-gray-400 mt-6 text-sm">
        Sudah punya akun?
        <a href="/login" class="text-pink-500 hover:underline">Login</a>
    </p>


</x-layouts.auth>