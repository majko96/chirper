@foreach ($chirps as $chirp)
    <div class="p-6 flex space-x-2 bg-white shadow-sm rounded-lg mb-4">
        <div class="flex-1">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a class="flex items-center" href="{{ route('profile.show', ['id' => $chirp->user->id]) }}">
                        @if ($chirp->user->image !== null)
                            <img src="/storage/profilePhotos/{{ $chirp->user->image }}" alt="user-image" class="rounded-full w-10 h-10 mr-2 object-cover">
                        @else
                            <img src="{{ asset('images/defaultUser.png') }}" alt="{{ $chirp->user->name }}" class="rounded-full w-10 h-10 mr-2 object-cover">
                        @endif

                        <span class="text-gray-800">{{ $chirp->user->name }}</span>&nbsp;
                    </a>
                    @unless ($chirp->created_at != ($chirp->updated_at))
                        <span class="ml-2 text-gray-600">{{ $chirp->created_at->format('j M Y, H:i') }}</span>
                    @endunless
                    @unless ($chirp->created_at->eq($chirp->updated_at))
                        <span class="text-gray-600 hidden sm:block"> &middot; {{ __('edited') }}</span>
                        <span class="ml-2 text-gray-600">{{ $chirp->updated_at->format('j M Y H:i') }}</span>
                    @endunless
                    @unless ($chirp->visible == false)
                        <span class="ml-2 text-red-600">{{ __('Private') }}</span>
                    @endunless
                </div>
                @if ($chirp->user->is(auth()->user()))
                    <x-dropdown>
                        <x-slot name="trigger">
                            <button>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('chirps.edit', $chirp)">
                                {{ __('Edit') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('chirps.destroy', $chirp) }}">
                                @csrf
                                @method('delete')
                                <x-dropdown-link :href="route('chirps.destroy', $chirp)" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Delete') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endif
            </div>
            <a href="{{ route('chirp.detail', ['id' => $chirp->id]) }}">
                <div class="flex justify-center mt-4">
                    @if ($chirp->image !== null)
                        <img src="/storage/images/{{ $chirp->image }}" alt="chirp-image">
                    @endif
                </div>
                <p class="mt-4 text-lg text-gray-900 first-letter text-container">{{ $chirp->message }}</p>
                <div class="mt-4">
                    <span id="likes-count-{{ $chirp->id }}"><i class="far fa-thumbs-up"></i> {{ $chirp->likesCount() }}</span>
                    <span id="likes-count-{{ $chirp->id }}"><i class="far fa-comment ml-2"></i> {{ $chirp->commentsCount() }}</span>
                </div>
            </a>
        </div>
    </div>
@endforeach
