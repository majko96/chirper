<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('chirps.store') }}" enctype="multipart/form-data">
            @csrf
            <label>
            <textarea
                name="message"
                placeholder="{{ __('What\'s on your mind?') }}"
                rows="5"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >{{ old('message') }}</textarea>
            </label>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <div class="max-w-4xl mx-auto flex  flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 mt-4">
                <div class="w-full sm:w-1/3">
                    <input type="file" class="form-control" name="image" id="selectImage">
                </div>
                <div class="flex items-center">
                    <label for="visible" class="mr-2">{{ __('Private') }}</label>
                    <input type="hidden" name="visible" value="0">
                    <input type="checkbox" name="visible" class="ml-2" value="1">
                </div>
            </div>
            <div>
                <img id="preview" src="#" alt="your image" class="mt-3 w-60" style="display:none;"/>
            </div>
            <div class="text-right">
                <x-primary-button class="mt-4 w-full sm:w-auto">{{ __('Chirp') }}</x-primary-button>
            </div>
        </form>
        <hr class="mt-4 mb-4"/>

        <div class="mt-6 mb-6">
            <div class="flex space-x-4 items-center">
                <div class="w-1/3">
                    <!-- Your first select with class "select2" -->
                    <select class="select2" style="width: 100%;"></select>
                </div>
                <div class="w-1/3">
                    <!-- Your second select with Tailwind styling -->
                    <div class="relative">
                        <select id="sort" class="block form-height appearance-none w-full bg-white border border-gray-300 hover:border-gray-500 px-4 py-2 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                            <option value="1">Latest</option>
                            <option value="2">Oldest</option>
                            <option value="3">Latest Edited</option>
                            <option value="4">Oldest Edited</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <x-secondary-button id="reset">{{ __('Reset') }}</x-secondary-button>
                <x-primary-button id="searchButton" class="ml-2">{{ __('Search') }}</x-primary-button>
            </div>

            <script type="text/javascript">
                var path = "{{ route('autocomplete') }}";
                $(document).ready(function () {
                    $("select.select2").select2({
                        ajax: {
                            url: path,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    query: params.term
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map(item => ({ id: item.id, text: item.name }))
                                };
                            },
                            cache: true
                        },
                        placeholder: 'Search user [name, email]',
                        minimumInputLength: 1
                    }).on('select2:unselecting', function() {
                        $(this).data('unselecting', true);
                    }).on('select2:opening', function(e) {
                        if ($(this).data('unselecting')) {
                            $(this).removeData('unselecting');
                            e.preventDefault();
                        }
                    });;

                });

                $('#reset').on('click', function () {
                    window.location.href = "{{ route('chirps.index') }}";
                })

                $('#searchButton').on('click', function () {
                    var selectedData = $("select.select2").select2('data')[0];
                    var sort = document.getElementById('sort');
                    var sortValue = sort.value;
                    var currentUrl = window.location.href;
                    var updatedUrl;

                    if (selectedData || sortValue) {
                        let queryParams = {};

                        if (selectedData) {
                            queryParams.userId = selectedData.id;
                            queryParams.userName = selectedData.text;
                        }

                        if (sortValue) {
                            let sortBy = 'created_at';
                            let order = 'desc';

                            switch (sortValue) {
                                case '2':
                                    order = 'asc';
                                    break;
                                case '3':
                                    sortBy = 'updated_at';
                                    break;
                                case '4':
                                    sortBy = 'updated_at';
                                    order = 'asc';
                                    break;
                            }

                            queryParams.sortBy = sortBy;
                            queryParams.order = order;
                        }

                        updatedUrl = updateQueryStringParameters(currentUrl, queryParams);
                    } else {
                        updatedUrl = removeQueryStringParameters(currentUrl, ['userId', 'userName', 'sortBy', 'order']);
                    }
                    window.location.href = updatedUrl;
                });

                function updateQueryStringParameters(uri, params) {
                    var updatedUrl = uri;
                    for (var key in params) {
                        if (params.hasOwnProperty(key)) {
                            updatedUrl = updateQueryStringParameter(updatedUrl, key, params[key]);
                        }
                    }
                    return updatedUrl;
                }

                function updateQueryStringParameter(uri, key, value) {
                    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
                    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
                    if (uri.match(re)) {
                        return uri.replace(re, '$1' + key + "=" + value + '$2');
                    } else {
                        return uri + separator + key + "=" + value;
                    }
                }

                function removeQueryStringParameters(uri, keys) {
                    var updatedUrl = uri;
                    for (var i = 0; i < keys.length; i++) {
                        var key = keys[i];
                        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
                        updatedUrl = updatedUrl.replace(re, function(match, p1, p2) {
                            return p2 === '&' ? p1 : '';
                        });
                    }
                    return updatedUrl;
                }

                var urlParams = new URLSearchParams(window.location.search);
                var userId = urlParams.get('userId');
                var userName = urlParams.get('userName');
                var sortByParamValue = urlParams.get('sortBy');
                var orderParamValue = urlParams.get('order');
                if (userId && userName) {
                    $("select.select2").val(userId).trigger('change');
                    $("select.select2").append(new Option(userName, userId, true, true)).trigger('change');
                }
                if (sortByParamValue && orderParamValue) {
                    if (sortByParamValue === 'created_at' && orderParamValue === 'desc') {
                        $('#sort').val(1);
                    }
                    if (sortByParamValue === 'created_at' && orderParamValue === 'asc') {
                        $('#sort').val(2);
                    }
                    if (sortByParamValue === 'updated_at' && orderParamValue === 'desc') {
                        $('#sort').val(3);
                    }
                    if (sortByParamValue === 'updated_at' && orderParamValue === 'asc') {
                        $('#sort').val(4);
                    }

                }

                function handleFileInputChange() {
                    var input = document.getElementById('selectImage');
                    var preview = document.getElementById('preview');

                    var file = input.files[0];

                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.style.display = 'none';
                    }
                }

                document.getElementById('selectImage').addEventListener('change', handleFileInputChange);
            </script>
        </div>

        <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @foreach ($chirps as $chirp)
                <div class="p-6 flex space-x-2">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span class="text-gray-800">{{ $chirp->user->name }}</span>&nbsp;
                                @unless ($chirp->created_at != ($chirp->updated_at))
                                    <span class="ml-2 text-gray-600">{{ $chirp->created_at->format('j M Y, H:i') }}</span>
                                @endunless
{{--                                <small class="text-sm text-gray-600"> &middot; {{ __('created') }}</small>--}}
{{--                                <small class="ml-2 text-sm text-gray-600">{{ $chirp->created_at->format('j M Y H:i') }}</small>--}}
                                @unless ($chirp->created_at->eq($chirp->updated_at))
                                    <span class="text-gray-600"> &middot; {{ __('edited') }}</span>
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
                        <p class="mt-4 text-lg text-gray-900 first-letter">{{ $chirp->message }}</p>
                        <div class="flex justify-center">
                            @if ($chirp->image !== null)
                                <img src="/storage/images/{{ $chirp->image }}" alt="chirp-image">
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            <div>
                {{ $chirps->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
