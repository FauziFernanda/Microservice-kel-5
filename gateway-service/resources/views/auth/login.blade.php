<x-layouts.auth title="Login">

    <h1
        class="text-3xl font-bold text-center bg-gradient-to-r from-pink-500 to-orange-400 bg-clip-text text-transparent mb-2">
        Welcome Back
    </h1>
    <p class="text-center text-gray-400 mb-6">Login ke akun kamu</p>

    <form class="space-y-4" method="POST" action="/login">
        <?php echo csrf_field(); ?>

        <?php if(session('status')): ?>
            <div class="p-3 rounded bg-green-500 text-white text-center"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

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

        <input type="email" placeholder="Email" name="email" required
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-pink-500 outline-none">


        <input type="password" placeholder="Password" name="password" required
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-orange-400 outline-none">


        <button type="submit"
            class="w-full py-3 rounded-lg font-semibold text-black bg-gradient-to-r from-pink-500 to-orange-400 hover:opacity-90 transition">
            Login
        </button>
    </form>


    <p class="text-center text-gray-400 mt-6 text-sm">
        Belum punya akun?
        <a href="/register" class="text-pink-500 hover:underline">Register</a>
    </p>

</x-layouts.auth>