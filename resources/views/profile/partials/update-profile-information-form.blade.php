<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <x-input-label for="about" :value="__('About me')" />
                <x-text-input id="about" name="about" type="text" class="mt-1 block w-full" :value="old('about', $user->about)"/>
                <x-input-error class="mt-2" :messages="$errors->get('about')" />
            </div>

            <div>
                <x-input-label for="birthdate" :value="__('Birth date')" />
                <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 block w-full" :value="old('birthdate', $user->birthdate)"/>
                <x-input-error class="mt-2" :messages="$errors->get('birthdate')" />
            </div>

            <div class="w-full">
                <x-input-label for="email" :value="__('Profile image')" />
                    <div class="flex justify-center items-center" id="imageDiv">
                        @if ($user->image !== null)
                            <img
                                src="/storage/profilePhotos/{{ $user->image }}"
                                alt="chirp-image"
                                class="rounded-full w-52 h-52 mb-2 object-cover"
                                id="imageOriginal"
                            >
                            <img id="preview" src="#" alt="your image" class="rounded-full w-52 h-52 mb-2 object-cover" style="display:none;"/>
                        @endif
                    </div>
                <input type="file" class="form-control" name="image" id="selectImage">
                <x-input-error class="mt-2" :messages="$errors->get('image')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        $(document).ready(function() {
            $(document).on('change', '#selectImage', function() {
                handleFileInputChange();
            });

            // $('#removeImageButton').click(function(event) {
            //     event.preventDefault();
            //     var imageElement = document.getElementById('image');
            //     imageElement.remove();
            //     var htmlCode = '<input type="file" class="form-control" name="image" id="selectImage">';
            //     $('#insertImage').html(htmlCode);
            //     var imageButton = document.getElementById('removeImageButton');
            //     imageButton.style.display = 'none';
            //     var selectImage = document.getElementById('selectImage');
            //     selectImage.style.display = 'block';
            // });

            function handleFileInputChange() {
                var input = document.getElementById('selectImage');
                var preview = document.getElementById('preview');

                var imageElement = document.getElementById('imageOriginal');
                if (imageElement) {
                    imageElement.remove();
                }

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
        </script>
</section>
