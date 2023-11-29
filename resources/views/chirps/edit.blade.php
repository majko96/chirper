<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('chirps.update', $chirp) }}" enctype="multipart/form-data">
            @csrf
            @method('patch')

            <textarea
                name="message"
                rows="5"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >{{ old('message', $chirp->message) }}</textarea>

            <div class="mt-5">
                @if ($chirp->image !== null)
                    <img src="/storage/images/{{ $chirp->image }}" alt="chirp-image" id="image">
                @endif
            </div>
            <div class="mt-2 flex justify-between" id="insertImage">
                @if ($chirp->image !== null)
                    <x-secondary-button id="removeImageButton">{{ __('Remove image') }} </x-secondary-button>
                    <input type="hidden" name="image" id="imageInput" value="{{ $chirp->image }}">
{{--                    <input type="file" class="form-control" name="image" id="selectImage" style="display:none;">--}}
                @else
                     <input type="file" class="form-control" name="image" id="selectImage">
                @endif
                <div>
                    <label for="visible">{{ __('Private') }}</label>
                    <input type="hidden" name="visible" value="0">
                    <input
                        type="checkbox"
                        name="visible"
                        id="visible"
                        {{ $chirp->visible ? 'checked' : '' }}
                        value="1"
                        class="ml-2"
                    >
                </div>
            </div>
            <div>
                <img id="preview" src="#" alt="your image" class="mt-3 w-60" style="display:none;"/>
            </div>

            <x-input-error :messages="$errors->get('message')" class="mt-2" />
            <div>
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Oops!</strong>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="mt-4 space-x-2 text-right">
                <x-secondary-button onclick=onCancel();>{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button onclick="event.preventDefault(); deleteForm();">
                    {{ __('Delete') }}
                </x-danger-button>
                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </div>
        </form>
        <form method="POST" action="{{ route('chirps.destroy', $chirp) }}" id="deleteForm">
            @csrf
            @method('delete')
        </form>
    </div>
    <script>
        $(document).ready(function() {
            // Event delegation for dynamically added elements
            $(document).on('change', '#selectImage', function() {
                handleFileInputChange();
            });

            $('#removeImageButton').click(function(event) {
                event.preventDefault();
                var imageElement = document.getElementById('image');
                imageElement.remove();
                var htmlCode = '<input type="file" class="form-control" name="image" id="selectImage">';
                $('#insertImage').html(htmlCode);
                var imageButton = document.getElementById('removeImageButton');
                imageButton.style.display = 'none';
                var selectImage = document.getElementById('selectImage');
                selectImage.style.display = 'block';
            });

            function handleFileInputChange() {
                var input = document.getElementById('selectImage');
                var preview = document.getElementById('preview');

                $('#imageInput').remove();
                var file = input.files[0];

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                }
            }
        });

        function deleteForm() {
            document.getElementById('deleteForm').submit();
        }

        function onCancel() {
            window.location.href = '{{ route("chirps.index") }}';
        }
    </script>
</x-app-layout>
