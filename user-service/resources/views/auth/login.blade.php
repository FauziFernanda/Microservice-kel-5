<x-layouts.auth title="Login">


    <h1
        class="text-3xl font-bold text-center bg-gradient-to-r from-pink-500 to-orange-400 bg-clip-text text-transparent mb-2">
        Welcome Back
    </h1>
    <p class="text-center text-gray-400 mb-6">Login ke akun kamu</p>


    <form class="space-y-4" id="loginForm">
        <input type="email" placeholder="Email" name="email"
            class="w-full px-4 py-3 rounded-lg bg-gray-800 text-white border border-gray-700 focus:ring-2 focus:ring-pink-500 outline-none">


        <input type="password" placeholder="Password" name="password"
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

<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const data = {
            email: this.email.value,
            password: this.password.value
        };

        const res = await fetch('http://127.0.0.1:8000/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (res.ok) {
            localStorage.setItem('token', result.token);
            alert('Login berhasil');
        } else {
            alert(result.message);
        }
    });
</script>
