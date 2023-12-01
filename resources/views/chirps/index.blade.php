<x-app-layout>
    <div class="max-w-4xl mx-auto sm:p-6 lg:p-8 pb-4">
        <form method="POST" action="{{ route('chirps.store') }}" enctype="multipart/form-data" class="p-4 md:p-0">
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
                <div class="w-full sm:w-1/3 ">
                    <input
                        type="file"
                        name="image"
                        id="selectImage"
                        class="block w-full text-sm text-gray-500
                              file:me-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-grey-800 file:text-white
                              hover:file:bg-grey-700
                              file:disabled:opacity-50 file:disabled:pointer-events-none
                              dark:file:bg-gray-800
                              dark:hover:file:bg-gray-700"
                    >
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
                <x-primary-button class="mt-4 w-full sm:w-auto">{{ __('Add new') }}</x-primary-button>
            </div>
        </form>
        <hr class="mt-4 mb-4"/>

        <div class="mt-6 mb-6 p-4 md:p-0">
            <div class="flex space-x-4 items-center">
                <div class="w-1/3">
                    <select class="select2" style="width: 100%;"></select>
                </div>
                <div class="w-1/3">
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

            <div class="text-right mt-4">
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

        <div class="mt-6">
            @include('chirps.item')
            <div class="mt-4">
                {{ $chirps->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
