<div>
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                <div class="flex items-center">
                    <i class="fa-solid fa-bell fa-xl"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium leading-4 bg-red-500 text-white">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </div>
            </button>
        </x-slot>

        <x-slot name="content">
            @forelse(auth()->user()->unreadNotifications as $notification)
                <x-dropdown-link :href="route('chirp.detail', $notification->data['post_id'])">
                    @php
                        $userId = $notification->data['user_id'];
                        $user = App\Models\User::find($userId);
                        $userName = $user ? $user->name : 'Unknown User';
                    @endphp
                    {{ $notification->data['message'] }} by {{ $userName }}
                </x-dropdown-link>
            @empty
                <p class="block px-4 py-2 text-sm text-gray-700">No new notifications</p>
            @endforelse
        </x-slot>
    </x-dropdown>
</div>
