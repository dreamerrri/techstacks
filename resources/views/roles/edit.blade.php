@extends('layouts.app')

@section('title', 'Edit Role')


@section('content')

    <div class="mb-5">
        <a href="{{ route('roles.show', $role) }}" class="back-link text-base-content/60 no-underline text-sm hover:text-success">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Role
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-base-content mb-6 flex items-center gap-2">
            <i class="icon-[ph--pencil-fill] text-error"></i> Edit — {{ $role->name }}
        </h2>

        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf @method('PUT')

            {{-- Role Details --}}
            <div class="mb-8">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-error/20 pb-2 mb-4">
                    <i class="icon-[ph--info-fill] text-error"></i> Role Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="fieldset">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-base-content/60">
                            Role Name <span class="text-error">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}"
                               class="input input-bordered w-full" required>
                        @error('name') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="fieldset">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-base-content/60">
                            Slug <span class="text-error">*</span>
                        </label>
                        <input type="text" name="slug" value="{{ old('slug', $role->slug) }}"
                               class="input input-bordered w-full"
                               placeholder="e.g. admin, hr, employee" required>
                        <p class="text-base-content/40 text-xs mt-1">Lowercase, no spaces (used in code)</p>
                        @error('slug') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="fieldset md:col-span-2">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-base-content/60">Description</label>
                        <textarea name="description" rows="2"
                                  class="textarea textarea-bordered w-full">{{ old('description', $role->description) }}</textarea>
                        @error('description') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                               class="checkbox checkbox-error">
                        <span class="font-semibold text-base-content/80 text-sm">Active</span>
                    </div>
                </div>
            </div>

            {{-- Permissions --}}
            <div class="mb-8">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-base-content/40 border-b-2 border-error/20 pb-2 mb-4">
                    <i class="icon-[ph--key-fill] text-error"></i> Permissions
                </h3>

                @if($permissions->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="bg-base-200 border border-base-300 rounded-xl p-4">
                                <div class="text-xs font-bold text-base-content/60 uppercase tracking-widest mb-3">
                                    {{ ucfirst($module) }}
                                </div>
                                @foreach($modulePermissions as $permission)
                                    <label class="flex items-center gap-2 cursor-pointer text-xs text-base-content/80 mb-2">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}
                                               class="checkbox checkbox-error checkbox-xs">
                                        {{ $permission->name }}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-base-content/40 text-sm m-0">No permissions available.</p>
                @endif
                @error('permissions') <p class="label text-error text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 flex-wrap pt-4 border-t border-base-300">
                <button type="submit" class="btn  btn-error">
                    <i class="icon-[ph--floppy-disk-fill]"></i> Update Role
                </button>
                <a href="{{ route('roles.show', $role) }}" class="btn btn-soft btn-success">Cancel</a>
            </div>
        </form>
    </div>

@endsection