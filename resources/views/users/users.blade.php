<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="mt-6 mb-6">
            <div class="flex space-x-4 items-center">
                <div class="w-1/3">
                    <select class="select2" style="width: 100%;"></select>
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
                    window.location.href = "{{ route('users.index') }}";
                })

                $('#searchButton').on('click', function () {
                    var selectedData = $("select.select2").select2('data')[0];
                    var currentUrl = window.location.href;
                    var updatedUrl;

                    if (selectedData) {
                        let queryParams = {};

                        if (selectedData) {
                            queryParams.userId = selectedData.id;
                            queryParams.userName = selectedData.text;
                        }

                        updatedUrl = updateQueryStringParameters(currentUrl, queryParams);
                    } else {
                        updatedUrl = removeQueryStringParameters(currentUrl, ['userId', 'userName']);
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
                if (userId && userName) {
                    $("select.select2").val(userId).trigger('change');
                    $("select.select2").append(new Option(userName, userId, true, true)).trigger('change');
                }

                document.getElementById('selectImage').addEventListener('change', handleFileInputChange);
            </script>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white flex flex-wrap">
            @foreach ($users->chunk(2) as $userGroup)
                <div class="w-1/3"> <!-- Adjust width based on your design -->
                    @foreach ($userGroup as $user)
                        <div class="p-6 space-x-2">
                            <div class="text-center">
                                <a href="{{ route('profile.show', ['id' => $user->id]) }}" class="block mx-auto">
                                    @if ($user->image !== null)
                                        <img src="/storage/profilePhotos/{{ $user->image }}" alt="user-image" class="rounded-full w-10 h-10 mx-auto object-cover">
                                    @else
                                        <img src="{{ asset('images/defaultUser.png') }}" alt="{{ $user->name }}" class="rounded-full w-10 h-10 mx-auto object-cover">
                                    @endif
                                    <span class="text-gray-800 block">{{ $user->name }}</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>




</x-app-layout>
