<x-app-layout>
    <div class="max-w-4xl mx-auto sm:p-6 lg:p-8">
        <div class="bg-white p-6">
        <div>
            <div class="flex items-center justify-between">
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
                        <span class="text-gray-600"> &middot; {{ __('edited') }}</span>
                        <span class="ml-2 text-gray-600">{{ $chirp->updated_at->format('j M Y H:i') }}</span>
                    @endunless
                    @unless ($chirp->visible == false)
                        <span class="ml-2 text-red-600">{{ __('Private') }}</span>
                    @endunless
                </div>
                <div>
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
            </div>
        </div>
        <div class="mt-4 flex justify-center">
            @if ($chirp->image !== null)
                <img src="/storage/images/{{ $chirp->image }}" alt="chirp-image" id="image">
            @endif
        </div>
        <div>
            <p class="mt-4 text-lg text-gray-900 first-letter">{{ $chirp->message }}</p>
        </div>
            <hr class="mt-4">
        <div class="flex items-center space-x-2 mt-5">
            <button class="like-button bg-gray-800 hover:bg-gray-700 text-white font-bold py-1 px-4 rounded" data-id="{{ $chirp->id }}" data-liked="{{ $chirp->isLikedByUser(Auth::id()) ? 'true' : 'false' }}">
                {{ $chirp->isLikedByUser(Auth::id()) === 'true' ? 'Dislike' : 'Like' }}
            </button>
            <span class="cursor-pointer" id="likeButton">
                <i class="far fa-thumbs-up mr-1"></i><span class="cursor-pointer" id="likes-count-{{ $chirp->id }}">{{ $chirp->likesCount() }}</span>
            </span>
            <span id="comment-count-{{ $chirp->id }}"><i class="far fa-comment mr-1"></i>{{ $chirp->commentsCount() }}</span>
        </div>
        </div>

        <script>
            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('.like-button').each(function () {
                    var chirpId = $(this).data('id');
                    var likeButton = $(this);
                    var isLiked = $(this).data('liked') === true;

                    updateButtonAppearance();

                    likeButton.on('click', function () {
                        $.ajax({
                            type: isLiked ? 'DELETE' : 'POST',
                            url: isLiked ? '/unlike/chirp/' + chirpId : '/like/chirp/' + chirpId,
                            dataType: 'json',
                            success: function (data) {
                                $('#likes-count-' + chirpId).text(data.like_count);
                                Swal.fire({
                                            position: "center",
                                            icon: "success",
                                            title: data.message,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                isLiked = !isLiked;
                                updateButtonAppearance();
                            },
                            error: function (data) {
                                alert(data.responseJSON.message);
                            }
                        });
                    });

                    function updateButtonAppearance() {
                        likeButton.text(isLiked ? 'Dislike' : 'Like');
                    }
                });
            });
        </script>

        <script>
            var chirpId = '{{$chirp->id}}'
            $(document).ready(function () {
                $('#likeButton').on('click', function () {
                    $.ajax({
                        url: '/users-likes/' + chirpId,
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            Swal.fire({
                                title: 'Liked users',
                                html: generateUserList(data),
                                confirmButtonText: 'Close',
                            });
                        },
                        error: function (error) {
                            console.error('Error fetching data:', error);
                        }
                    });
                });

                function generateUserList(users) {
                    const profileRoute = "{{ route('profile.show', ['id' => '0']) }}";
                    const route = profileRoute.slice(0, -1);
                    const defaultUserImage = "{{ asset('images/defaultUser.png') }}";
                    if (users && Array.isArray(users)) {
                        return users.map(user => `
                        <a href='${route}${user.id}' class="modal-link">
                            <div class="flex items-center mt-3">
                                <img
                                    src="${user.image ? '/storage/profilePhotos/' + user.image : defaultUserImage}"
                                    alt="${user.name}"
                                    class="rounded-full w-10 h-10 mr-2 object-cover"
                                >
                                <span>${user.name}</span>
                            </div>
                        </a>
                    `).join('');
                    } else {
                        console.error('userData is null or not an array');
                    }
                }
            });
        </script>


        <div class="p-6 md:p-0">
            <p class="text-lg mt-5 mb-5">Comments</p>
            <div>
                @if ($chirp->comments !== null)
                    <div class="max-w-4xl mx-auto">
                        @foreach($chirp->comments as $comment)
                            <div class="flex items-start justify-between mb-4 mt-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        @if ($comment->user->image !== null)
                                            <a href="{{ route('profile.show', ['id' => $comment->user->id]) }}">
                                                <img src="/storage/profilePhotos/{{ $comment->user->image }}" alt="{{ $comment->user->name }}" class="h-10 w-10 object-cover rounded-full">
                                            </a>
                                        @else
                                            <img src="{{ asset('images/defaultUser.png') }}" alt="{{ $comment->user->name }}" class="h-10 w-10 object-cover rounded-full">
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route('profile.show', ['id' => $comment->user->id]) }}">
                                            <p class="text-sm font-medium text-gray-800">{{ $comment->user->name }}</p>
                                        </a>
                                        <p class="text-gray-700">{{ $comment->content }}</p>
                                        <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @if ($comment->user->id === auth()->id())
                                    <div class="flex items-top">
                                        <form method="post" action="{{ route('chirp.removeComment', ['id' => $chirp->id, 'commentId' => $comment->id]) }}">
                                            @csrf
                                            <button type="submit" class=""><i class="fas fa-trash"></i></button>
                                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <hr>
                        @endforeach
                    </div>
                @endif

                <form method="post" action="{{ route('chirp.storeComment', ['id' => $chirp->id]) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <textarea
                        name="content"
                        placeholder="Add your comment..."
                        required
                        class="block mt-5 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    ></textarea>
                    <div class="flex justify-end">
                        <x-primary-button type="submit" class="mt-5">Add Comment</x-primary-button>
                    </div>
                </form>
        </div>
        </div>
    </div>

</x-app-layout>
