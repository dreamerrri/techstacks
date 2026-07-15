{{-- resources/views/components/avatar-upload.blade.php

    Avatar with hover-camera upload button. Scoped by class, not ID, so
    it's safe to drop more than one on a page (or reuse across pages)
    without collisions — e.g. this profile page, plus an admin "edit
    employee" page later.

    Props:
        photoUrl     (string|null, optional) Current photo URL, or null for initials.
        initials     (string, required if no photo) Fallback initials.
        uploadRoute  (string, required) Where the file gets POSTed.
        fieldName    (string, optional) Form field name. Default: 'photo'.
        size         (string, optional) Tailwind size classes. Default: 'w-20 h-20'.

    Requires the companion script (see bottom of this file) included ONCE
    in your layout — not per-page — so every instance on any page works
    automatically without extra wiring.

    Example:
        <x-avatar-upload
            :photo-url="$user->profile_photo ? Storage::disk('s3')->temporaryUrl($user->profile_photo, now()->addHours(24)) : null"
            :initials="strtoupper(substr($user->name, 0, 1))"
            upload-route="{{ route('profile.photo') }}"
        />
--}}
@props([
    'photoUrl'    => null,
    'initials'    => '?',
    'uploadRoute',
    'fieldName'   => 'photo',
    'size'        => 'w-20 h-20',
])

<div class="avatar-upload relative flex-shrink-0" data-upload-route="{{ $uploadRoute }}" data-field-name="{{ $fieldName }}">
    <div class="avatar-circle {{ $size }} rounded-full bg-gray-100 border-2 border-gray-200 overflow-hidden flex items-center justify-center cursor-pointer text-3xl font-bold text-base-content/60">
        @if($photoUrl)
            <img class="avatar-img w-full h-full object-cover" src="{{ $photoUrl }}" alt="">
        @else
            <span class="avatar-initials">{{ $initials }}</span>
        @endif
    </div>
    <label class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-white border-2 border-gray-200 flex items-center justify-center cursor-pointer shadow-sm">
        <i class="icon-[ph--camera-fill] text-[10px] text-base-content/80"></i>
        <input type="file" accept="image/*" class="avatar-file-input hidden">
    </label>
</div>

{{--
    ADD THIS ONCE to your layout (e.g. layouts/app.blade.php, near your
    other global scripts) — NOT per page. One script wires up every
    .avatar-upload widget on the page automatically.

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.avatar-upload').forEach(function (widget) {
            const input  = widget.querySelector('.avatar-file-input');
            const circle = widget.querySelector('.avatar-circle');
            const route  = widget.dataset.uploadRoute;
            const field  = widget.dataset.fieldName;

            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                // Live preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    let img = circle.querySelector('.avatar-img');
                    const initials = circle.querySelector('.avatar-initials');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'avatar-img w-full h-full object-cover';
                        circle.innerHTML = '';
                        circle.appendChild(img);
                    }
                    if (initials) initials.style.display = 'none';
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Actual upload — this was the missing piece before
                const formData = new FormData();
                formData.append(field, file);

                fetch(route, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(res => res.json())
                .then(data => {
                    window.notyf?.success(data.message ?? 'Photo updated.');
                })
                .catch(() => {
                    window.notyf?.error('Failed to upload photo.');
                });
            });
        });
    });
    </script>
--}}