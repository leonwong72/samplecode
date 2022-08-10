@push('styles')
    <style>
        .uploader{
            width: 100%;
            min-height: 150px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            border: 1px dashed #ced4da;
            font-size: 30px;
            padding: 5px;
        }

        .uploaded-file{
            width: 100%;
            height: auto;
        }

        .uploader.is-invalid{
            border-color: #f46a6a;
            color: #f46a6a;
        }
    </style>
@endpush

<div>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Course Information</h5>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="Title" value="{{ old('title') ?: $course->title }}">
                                @error('title')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Category</label>
                                <select class="form-select select-category @error('category_id') is-invalid @enderror" wire:model="category_id">
                                    <option value=""></option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : ($category_id == $category->id ? 'selected' : '') }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="text-danger" style="font-size: 80%">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Price</label>
                                <input type="number" min="0" step="0.01" class="form-control @error('price') is-invalid @enderror" wire:model="price" placeholder="0.00" value="{{ old('price') ?: '' }}">
                                @error('price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Course Image  <span class="text-danger small">** Only .png, .jpeg and .gif formatted images are allowed **</span></label>
                                <div class="uploader image-url">
                                    @if($image_url && is_string($image_url))
                                        <img src="{{ asset($image_url) }}" class="uploaded-file">
                                    @elseif($image_url)
                                        <img src="{{ $image_url->temporaryUrl() }}" class="uploaded-file">
                                    @else
                                        <i class="fas fa-plus"></i>
                                    @endif
                                </div>
                                <input type="file" class="d-none upload-image @error('image_url') is-invalid @enderror" wire:model="image_url" accept="image/jpeg,image/png,image/gif">
                                @error('image_url')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label>Description</label>
                                <textarea cols="30" rows="3" class="form-control @error('description') is-invalid @enderror" wire:model="description">{{ old('description') ?: '' }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title mb-4">Lessons</h5>

                        <div class="add-item">
                            <button type="button" class="btn btn-primary" wire:click="addLesson"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>

                    @foreach($lessons as $key => $lesson)
                        @if(!$loop->first)
                            <hr>
                        @endif
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label>Lesson Title</label>
                                    <input type="text" class="form-control @error('lessons.'.$key.'.title') is-invalid @enderror" placeholder="Lesson Title" wire:model="lessons.{{ $key }}.title" value="{{ old('lessons.'.$key.'.title') ?: '' }}">
                                    @error('lessons.'.$key.'.title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label>Lesson Start At</label>
                                    <input type="datetime-local" class="form-control @error('lessons.'.$key.'.start_at') is-invalid @enderror" wire:model="lessons.{{ $key }}.start_at" value="{{ old('lessons.'.$key.'.start_at') ?: '' }}">
                                    @error('lessons.'.$key.'.start_at')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label>Lesson End At</label>
                                    <input type="datetime-local" class="form-control @error('lessons.'.$key.'.end_at') is-invalid @enderror" wire:model="lessons.{{ $key }}.end_at" value="{{ old('lessons.'.$key.'.end_at') ?: '' }}">
                                    @error('lessons.'.$key.'.end_at')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <input type="checkbox" class="form-check-input @error('lessons.'.$key.'.is_recorded') is-invalid @enderror" wire:model="lessons.{{ $key }}.is_recorded" {{ $lessons[$key]['is_recorded'] ? 'checked' : '' }}>
                                    <label class="form-check-label">Is Recorded Video?</label>
                                    @error('lessons.'.$key.'.is_recorded')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            @if($lessons[$key]['is_recorded'])
                                <div class="col-12 mb-3">
                                    <label>Lesson Video <span class="text-danger small">** Only .mp4 and .mpeg formatted videos are allowed **</span></label>

                                    <div class="uploader video-url @error('lessons.'.$key.'.video_url') is-invalid @enderror">
                                        @if($lessons[$key]['video_url'] && is_string($lessons[$key]['video_url']))
                                            <video src="{{ asset($lessons[$key]['video_url']) }}" width="100%" height="auto" controls disablePictureInPicture controlsList="nodownload noplaybackrate"></video>
                                        @elseif($lessons[$key]['video_url'])
                                            <video src="{{ $lessons[$key]['video_url']->temporaryUrl() }}" width="100%" height="auto" controls disablePictureInPicture controlsList="nodownload noplaybackrate"></video>
                                        @else
                                            <i class="fas fa-plus"></i>
                                        @endif
                                    </div>
                                    <input type="file" class="d-none upload-video" wire:model="lessons.{{ $key }}.video_url" accept="video/mp4,video/mpeg">
                                    @error('lessons.'.$key.'.video_url')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Video Description</label>
                                    <textarea cols="30" rows="3" class="form-control" wire:model="lessons.{{ $key }}.video_description">{{ old('lessons.'.$key.'.video_description') ?: '' }}</textarea>
                                </div>
                            @else
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Lesson Mode</label>
                                        <select class="form-select select-mode @error('lessons.'.$key.'.is_online') is-invalid @enderror" wire:model="lessons.{{ $key }}.is_online" data-key="{{ $key }}">
                                            <option value=""></option>
                                            <option value="1" {{ $lessons[$key]['is_online'] == 1 ? 'selected' : '' }}>Online</option>
                                            <option value="0" {{ $lessons[$key]['is_online'] == 0 ? 'selected' : '' }}>Offline</option>
                                        </select>
                                        @error('lessons.'.$key.'.is_online')
                                        <div class="text-danger" style="font-size: 80%">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                                @if($lessons[$key]['is_online'])
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Online Lesson Link</label>
                                            <input type="text" class="form-control @error('lessons.'.$key.'.link') is-invalid @enderror" wire:model="lessons.{{ $key }}.link" placeholder="Link" value="{{ old('lessons.'.$key.'.link') ?: '' }}">
                                            @error('lessons.'.$key.'.link')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                @else
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Venue</label>
                                            <input type="text" class="form-control @error('lessons.'.$key.'.venue') is-invalid @enderror" wire:model="lessons.{{ $key }}.venue" placeholder="Venue" value="{{ old('lessons.'.$key.'.venue') ?: '' }}">
                                            @error('lessons.'.$key.'.venue')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        @if(count($lessons) > 1)
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-danger" wire:click="removeLesson({{ $key }})"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer text-end">
                    <div type="button" class="btn btn-primary" wire:click="submit">Submit</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $('.select-category').select2({
            placeholder: 'Select Category',
        });

        $('.image-url').click(function(){
            $('.upload-image').trigger('click')
        })

        $(document).on('click', '.video-url', function(){
            $('.upload-video').trigger('click')
        })

        $('.select-mode').select2({
            placeholder: 'Select Lesson Mode',
        })

        $(document).on('change', '.select-category', function (e) {
            @this.set('category_id', e.target.value);
        });

        $(document).on('change', '.select-mode', function (e) {
            @this.set('lessons.'+$(this).data('key')+'.is_online', e.target.value);
        });

        document.addEventListener("DOMContentLoaded", () => {
            Livewire.hook('message.processed', (message, component) => {
                $('.select-category').select2({
                    placeholder: 'Select Category',
                });

                $('.select-mode').select2({
                    placeholder: 'Select Lesson Mode',
                });
            })
        });
    </script>
@endpush
