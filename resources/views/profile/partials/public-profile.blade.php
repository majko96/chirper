<section>
    <div class="flex flex-col lg:flex-row">
        <div class="w-full lg:w-1/4 mb-4 lg:mb-0">
            <div class="p-6 bg-white rounded-lg shadow-md text-center">
                @if ($user->image !== null)
                    <div class="w-full mb-4">
                        <div class="flex justify-center items-center" id="imageDiv">
                            <img
                                src="/storage/profilePhotos/{{ $user->image }}"
                                alt="profile-image"
                                class="rounded-full w-52 h-52 mb-2 object-cover"
                                id="imageOriginal"
                            >
                        </div>
                    </div>
                @else
                    <div class="w-full mb-4">
                        <div class="flex justify-center items-center" id="imageDiv">
                            <img
                                src="{{ asset('images/defaultUser.png') }}"
                                alt="{{ $user->name }}"
                                class="rounded-full w-52 h-52 mb-2 object-cover"
                            >
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <p class="text-xl font-semibold">{{ $user->name }}</p>
                </div>

                <div class="mb-2">
                    <span class="text-gray-600">{{ __('Email') }}:</span>
                    <p class="text-gray-800">{{ $user->email }}</p>
                </div>

                <div class="mb-2">
                    <span class="text-gray-600">{{ __('About me') }}:</span>
                    <p class="text-gray-800">{{ $user->about }}</p>
                </div>

                <div class="mb-2">
                    <span class="text-gray-600">{{ __('Birth date') }}:</span>
                    <p class="text-gray-800">{{ $user->birthdate }}</p>
                </div>

                @if ($user->id === auth()->id())
                    <div class="flex justify-end">
                        <a href="{{ route('profile.edit') }}">
                            <x-primary-button id="reset">{{ __('Edit') }}</x-primary-button>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex-1">
            <div class="grid grid-cols-1">
                <div class="ml-0 lg:ml-10">
                    @include('chirps.item')
                    <div class="mt-4">
                        {{ $chirps->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
