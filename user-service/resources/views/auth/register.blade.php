<x-layouts.auth title="Register">


    <h1
        class="text-3xl font-bold text-center bg-gradient-to-r from-pink-500 to-orange-400 bg-clip-text text-transparent mb-2">
        Create Account
    </h1>
    <p class="text-center text-gray-400 mb-6">Daftar akun baru</p>


    <form class="space-y-4" id="registerForm">
        <input type="text" placeholder="Nama Lengkap" name="name" required
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-pink-500 outline-none">


        <input type="email" placeholder="Email" name="email" required
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-orange-400 outline-none">


        <input type="password" placeholder="Password" name="password" required
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-pink-500 outline-none">


        <button type="submit"
            class="w-full py-3 rounded-lg font-semibold text-black bg-gradient-to-r from-pink-500 to-orange-400 hover:opacity-90 transition">
            Register
        </button>
    </form>


    <p class="text-center text-gray-400 mt-6 text-sm">
        Sudah punya akun?
        <a href="/login" class="text-pink-500 hover:underline">Login</a>
    </p>


</x-layouts.auth>

<script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = e.target;

        const data = {
            name: form.name.value,
            email: form.email.value,
            password: form.password.value
        };

        const res = await fetch('http://127.0.0.1:8000/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (res.ok) {
            alert('Register berhasil');
            console.log(result);
        } else {
            alert(result.message ?? 'Register gagal');
            console.error(result);
        }
    });
</script>
