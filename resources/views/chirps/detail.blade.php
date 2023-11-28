<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white p-6">
        <div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a class="flex items-center" href="{{ route('profile.show', ['id' => $chirp->user->id]) }}">
                        @if ($chirp->user->image !== null)
                            <img src="/storage/profilePhotos/{{ $chirp->user->image }}" alt="user-image" class="rounded-full w-10 h-10 mr-2 object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
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
        <div class="mt-5 flex justify-center">
            @if ($chirp->image !== null)
                <img src="/storage/images/{{ $chirp->image }}" alt="chirp-image" id="image">
            @endif
        </div>
        <div class="pt-5">
            <p class="text-gray-800">{{ old('message', $chirp->message) }}</p>
        </div>
            <hr class="mt-5">
        <div class="flex items-center space-x-2 mt-5">
            <button class="like-button bg-gray-800 hover:bg-gray-700 text-white font-bold py-1 px-4 rounded" data-id="{{ $chirp->id }}" data-liked="{{ $chirp->isLikedByUser(Auth::id()) ? 'true' : 'false' }}">
                {{ $chirp->isLikedByUser(Auth::id()) === 'true' ? 'Dislike' : 'Like' }}
            </button>
            <span class="cursor-pointer" id="likes">
                <i class="far fa-thumbs-up mr-1"></i><span class="cursor-pointer" id="likes-count-{{ $chirp->id }}" data-user-list="{{ json_encode($chirp->likes->pluck('user')) }}">{{ $chirp->likesCount() }}</span>
            </span>
            <span id="comment-count-{{ $chirp->id }}"><i class="far fa-comment mr-1"></i>{{ $chirp->commentsCount() }}</span>
        </div>
        </div>

        <script>
            const userData = JSON.parse(document.getElementById('likes-count-{{ $chirp->id }}').getAttribute('data-user-list'));
            const profileRoute = "{{ route('profile.show', ['id' => '0']) }}";
            const route = profileRoute.slice(0, -1);

            const modalContent = userData.map(user => `<a href='${route}${user.id}' class="modal-link"><div class="flex items-center mt-3"><img src="/storage/profilePhotos/${user.image}" alt="${user['name']}" class="rounded-full w-10 h-10 mr-2 object-cover"> <span>${user.name}</span></div></a>`).join('');
            document.getElementById('likes').addEventListener('click', function () {
                Swal.fire({
                    title: 'Liked Users',
                    html: modalContent,
                    confirmButtonText: 'Close',
                });
            });
        </script>
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


        <p class="text-lg mt-5 mb-5">Comments</p>
        <div>
            <!-- Display existing comments for the chirp -->
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

            <!-- Form to add a new comment -->
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

</x-app-layout>