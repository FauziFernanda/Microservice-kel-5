<div class="sidebar fixed h-screen bg-[#211F27] text-white border-r border-pink-500/20 transition-all duration-300 flex flex-col overflow-visible" 
    x-data="{ 
        isOpen: (localStorage.getItem('sidebarIsOpen') === null ? true : localStorage.getItem('sidebarIsOpen') === 'true'),
        init() {
            this.$watch('isOpen', value => {
                window.handleSidebarToggle(value);
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

    <!-- Judul Aplikasi -->
    <div class="p-4 border-b border-gray-500/30">
        <div class="flex items-center" :class="{ 'justify-center': !isOpen }">
            <img src="{{ asset('img/EngliciousLogo.png') }}" alt="Logo" class="w-8 h-8">
            <h1 class="text-xl font-bold ml-3 transition-opacity duration-300" :class="{ 'opacity-0': !isOpen }" style="font-family: 'IntegralCF demo', sans-serif;">Englicious</h1>
        </div>
    </div>

    <!-- Menu Navigasi -->
    <div class="flex-1 py-4 px-3 overflow-y-auto no-scrollbar">
        <p class="text-xs font-medium text-gray-400 mb-2 px-2" x-show="isOpen">Menu</p>
        <ul class="space-y-1">
            <li class="relative">
                <a href="{{ route('home') }}" class="flex items-center px-2 py-2.5 rounded-lg transition-colors {{ request()->is('/') ? 'bg-pink-500/10 text-pink-500' : 'hover:bg-pink-500/10 hover:text-pink-500' }} group">
                    <i class="fi fi-rs-home text-lg"></i>
                    <span class="ml-3 text-sm transition-opacity duration-300" :class="{ 'opacity-0': !isOpen }">Home</span>
                </a>
            </li>
            <li class="relative">
                <a href="{{ route('admin.news.index') }}" title="News Management" class="flex items-center px-2 py-2.5 rounded-lg transition-colors hover:bg-pink-500/10 hover:text-pink-500 {{ request()->routeIs('admin.news.*') ? 'bg-pink-500/10 text-pink-500' : 'text-white' }} group">
                    <i class="fi fi-rr-bullhorn text-lg"></i>
                    <span class="ml-3 text-sm transition-opacity duration-300" :class="{ 'opacity-0': !isOpen }">News Management</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Info Footer -->
    <div class="p-4 border-t border-gray-500/30">
        <div class="text-xs text-gray-400 text-center" x-show="isOpen">
            News Service
        </div>
    </div>
</div>

<style>
  html.light .sidebar,
  html.light .bg-[#101014] {
    background-color: #fff !important;
  }
  html.light .bg-[#2A2A32],
  html.light .bg-[#1E1E24],
  html.light .bg-[#2A2A2A],
  html.light .bg-[#211F27]:not(.sidebar) {
    background-color: #f9a8d4 !important;
  }
  html.light .bg-pink-500\/10,
  html.light .bg-pink-500\/20,
  html.light .bg-pink-500,
  html.light .hover\:bg-pink-500\/10:hover {
    background-color: #f9a8d4 !important;
  }
  html.light .text-white,
  html.light .text-gray-400,
  html.light .text-pink-500,
  html.light .text-gray-600,
  html.light .text-gray-700 {
    color: #111 !important;
  }
  html.light .border-white\/20,
  html.light .border-pink-500\/20,
  html.light .border-gray-700 {
    border-color: #f9a8d4 !important;
  }
  html.light .shadow-lg,
  html.light .shadow {
    box-shadow: 0 1px 3px 0 #0000000d, 0 1px 2px 0 #0000001a !important;
  }
  /* Table header and row backgrounds */
  html.light th,
  html.light thead,
  html.light .table-header,
  html.light .table-row-secondary {
    background-color: #f9a8d4 !important;
    color: #111 !important;
  }
  html.light .rounded-xl,
  html.light .rounded-lg {
    background-color: #fff !important;
  }
</style>
