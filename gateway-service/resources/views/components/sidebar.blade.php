<div class="sidebar fixed h-screen transition-all duration-300 flex flex-col overflow-visible" style="background:#211F27;color:#ffffff;border-right:1px solid rgba(219,39,119,0.12);" 
    x-data="{ 
        isOpen: (localStorage.getItem('sidebarIsOpen') === null ? true : localStorage.getItem('sidebarIsOpen') === 'true'),
        init() {
            this.$watch('isOpen', value => {
                window.handleSidebarToggle && window.handleSidebarToggle(value);
                localStorage.setItem('sidebarIsOpen', value);
            });
        },
        toggle() {
            this.isOpen = !this.isOpen;
            localStorage.setItem('sidebarIsOpen', this.isOpen);
        }
    }" 
    :class="{ 'w-64': isOpen, 'w-16': !isOpen }">
    
    <!-- Toggle Button -->
    <button @click="toggle()" 
        class="absolute -right-3 top-6 bg-pink-500 text-white p-1 rounded-full shadow-lg hover:bg-pink-600 focus:outline-none z-50">
        <svg xmlns="http://www.w3.org/2000/svg" 
            class="h-4 w-4 transition-transform duration-300"
            :class="{ 'rotate-180': !isOpen }"
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    <!-- App Title -->
    <div class="p-4 border-b border-gray-500/30">
        <div class="flex items-center" :class="{ 'justify-center': !isOpen }">
            <img src="{{ rtrim(config('services.news_service.url'), '/') }}/img/EngliciousLogo.png" alt="Logo" class="w-8 h-8">
            <h1 class="text-xl font-bold ml-3 transition-opacity duration-300" :class="{ 'opacity-0': !isOpen }" style="font-family: 'IntegralCF demo', sans-serif;">Englicious</h1>
        </div>
    </div>

    <!-- Menu -->
    <div class="flex-1 py-4 px-3 overflow-y-auto no-scrollbar">
        <p class="text-xs font-medium text-gray-400 mb-2 px-2" x-show="isOpen">Menu</p>
        <ul class="space-y-1">
            <li class="relative">
                <a href="/" class="flex items-center px-2 py-2.5 rounded-lg transition-colors group" style="{{ request()->is('/') ? "background: rgba(219,39,119,0.06); color: #ef4567;" : "color: #ffffff;" }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1 -1 1h-14a1 1 0 0 1 -1 -1z"/></svg>
                    <span class="ml-3 text-sm transition-opacity duration-300" :class="{ 'opacity-0': !isOpen }">Home</span>
                </a>
            </li>
            <li class="relative">
                <a href="/news" title="News Management" class="flex items-center px-2 py-2.5 rounded-lg transition-colors group" style="{{ request()->is('news*') ? "background: rgba(219,39,119,0.06); color: #ef4567;" : "color: #ffffff;" }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M5 8c-1 0-2 .9-2 2v4a2 2 0 0 0 2 2h1v2h2v-2h6v2h2v-2h1a2 2 0 0 0 2-2v-4c0-1.1-.9-2-2-2H5zM19 6l-3 1V7a3 3 0 0 0-3-3H6v2h7c.6 0 1 .4 1 1v.1L19 6z"/></svg>
                    <span class="ml-3 text-sm transition-opacity duration-300" :class="{ 'opacity-0': !isOpen }">News Management</span>
                </a>
            </li> 
        </ul>
    </div>

    <!-- Footer label -->
    <div class="p-4 border-t border-gray-500/30">
        <div class="text-xs text-gray-400" x-show="isOpen">News Service</div>
    </div>

    <!-- Logout button at bottom-left -->
    <div class="absolute left-4 bottom-6">
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-pink-500 text-white hover:bg-pink-600 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                <span x-show="isOpen">Logout</span>
            </button>
        </form>
    </div>
</div>

<!-- Minimal styles fallback -->
<style>
  .sidebar { z-index: 40 }
</style>

<!-- Minimal styles fallback -->
<style>
  .sidebar { z-index: 40 }
</style>