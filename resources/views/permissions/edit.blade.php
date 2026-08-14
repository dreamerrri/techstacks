@extends('layouts.app')

@section('title', 'Edit Permission')


@section('content')

    <div class="mb-5">
        <a href="{{ route('permissions.show', $permission) }}" class="back-link text-subtle no-underline text-sm hover:text-success">
            <i class="icon-[ph--arrow-left-fill]"></i> Back to Permission
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-base-content mb-6 flex items-center gap-2">
            <i class="icon-[ph--pencil-fill] text-error"></i> Edit — {{ $permission->name }}
        </h2>

        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf @method('PUT')

            <div class="mb-8">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-faint border-b-2 border-error/20 pb-2 mb-4">
                    <i class="icon-[ph--info-fill] text-error"></i> Permission Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="fieldset">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-subtle">
                            Permission Name <span class="text-error">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $permission->name) }}"
                               class="input input-bordered w-full" required>
                        @error('name') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="fieldset">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-subtle">
                            Slug <span class="text-error">*</span>
                        </label>
                        <input type="text" name="slug" value="{{ old('slug', $permission->slug) }}"
                               class="input input-bordered w-full" required>
                        <p class="text-faint text-xs mt-1">Dot-notation, lowercase (used in middleware)</p>
                        @error('slug') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="fieldset">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-subtle">
                            Module <span class="text-error">*</span>
                        </label>
                        <input type="text" name="module" value="{{ old('module', $permission->module) }}"
                               class="input input-bordered w-full"
                               placeholder="e.g. Employees, Payroll, Users" required>
                        <p class="text-faint text-xs mt-1">Groups permissions on the roles page</p>
                        @error('module') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="fieldset md:col-span-2">
                        <label class="label text-xs font-semibold uppercase tracking-wider text-subtle">Description</label>
                        <textarea name="description" rows="2"
                                  class="textarea textarea-bordered w-full">{{ old('description', $permission->description) }}</textarea>
                        @error('description') <p class="label text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $permission->is_active) ? 'checked' : '' }}
                               class="checkbox checkbox-error">
                        <span class="font-semibold text-muted text-sm">Active</span>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 flex-wrap pt-4 border-t border-base-300">
                <button type="submit" class="btn  btn-error">
                    <i class="icon-[ph--floppy-disk-fill]"></i> Update Permission
                </button>
                <a href="{{ route('permissions.show', $permission) }}" class="btn btn-soft btn-success">Cancel</a>
            </div>
        </form>
    </div>

@endsection