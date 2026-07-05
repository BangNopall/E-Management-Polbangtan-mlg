{{-- Start: HeadNav --}}
<div class="py-2 px-6 bg-white flex items-center shadow-md shadow-black/5 sticky top-0 left-0 z-30">
    <button type="button" class="text-lg text-gray-600 md:hidden sidebar-toggle">
        <i class="ri-menu-line"></i>
    </button>
    <ul class="flex-col text-sm ml-4 md:ml-0">
            <li class="text-gray-600 font-medium"><p id="liveDateTime"></p></li>
    </ul>
    <ul class="ml-auto flex items-center">
        <li class="dropdown ml-3">
            <button type="button" class="dropdown-toggle flex items-center">
                @if (auth()->user()->image)
                    <img src="{{ asset('storage/images/' . auth()->user()->image) }}" alt=""
                        class="w-8 h-8 rounded block object-cover align-middle">
                @else
                    <img src="https://placehold.co/32x32" alt=""
                        class="w-8 h-8 rounded block object-cover align-middle">
                @endif
            </button>
            <ul
                class="dropdown-menu shadow-md shadow-black/5 z-30 hidden py-1.5 rounded-md bg-white border border-gray-100 w-full max-w-[140px]">
                <li>
                    <a href="/profil"
                        class="flex items-center text-[13px] py-1.5 px-4 text-gray-600 hover:text-utama hover:bg-gray-50">Profil</a>
                </li>

                @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 4)
                    <li>
                        <form action="{{ route('auth.logout') }}" method="post"
                            class="flex items-center text-[13px] py-1.5 px-4 text-gray-600 hover:text-utama hover:bg-gray-50">
                            @method('DELETE')
                            @csrf
                            <button>Logout</button>
                        </form>
                    </li>
                @endif
            </ul>
        </li>
    </ul>
</div>
{{-- end: HeadNav --}}
