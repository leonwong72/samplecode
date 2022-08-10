<?php

namespace App\Http\Livewire\Educator\Courses;

use App\Models\Category;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $course;
    public $categories = [];
    public $title;
    public $price;
    public $category_id;
    public $description;
    public $image_url;
    public $lessons = [];
    public $removed_lessons = [];

    public function mount($course)
    {
        $this->course = $course;
        $this->title = $course->title;
        $this->price = $course->price;
        $this->category_id = $course->category_id;
        $this->description = $course->description;
        $this->image_url = $course->image_url;

        $this->categories = Category::all();

        if(count($course->lessons) > 0){
            foreach($course->lessons as $lesson){
                $is_recorded = false;
                if(count($lesson->videos) > 0){
                    $is_recorded = true;
                }

                $this->lessons[] = [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'start_at' => $lesson->start_at,
                    'end_at' => $lesson->end_at,
                    'is_recorded' => $is_recorded,
                    'video_url' => $is_recorded ? $lesson->videos[0]->link : null,
                    'video_description' => $is_recorded ? $lesson->videos[0]->description : null,
                    'is_online' => $lesson->is_online,
                    'venue' => $lesson->venue,
                    'link' => $lesson->link
                ];
            }
        }else{
            $this->lessons[] = [
                'id' => null,
                'title' => null,
                'start_at' => null,
                'end_at' => null,
                'is_recorded' => false,
                'video_url' => null,
                'video_description' => null,
                'is_online' => null,
                'venue' => null,
                'link' => null
            ];
        }
    }

    public function addLesson()
    {
        $this->lessons[] = [
            'id' => null,
            'title' => null,
            'start_at' => null,
            'end_at' => null,
            'is_recorded' => false,
            'video_url' => null,
            'video_description' => null,
            'is_online' => null,
            'venue' => null,
            'link' => null
        ];
    }

    public function removeLesson($index)
    {
        if($this->lessons[$index]['id']){
            $this->removed_lessons[] = $this->lessons[$index]['id'];
        }

        array_splice($this->lessons, $index, 1);
    }

    public function submit()
    {
        $this->validate([
            'title' => 'required',
            'category_id' => 'required',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'image_url' => 'nullable',
            'description' => 'nullable',
            'lessons' => 'required|array',
            'lessons.*.title' => 'required',
            'lessons.*.start_at' => 'required',
            'lessons.*.end_at' => 'nullable',
            'lessons.*.is_recorded' => 'required',
            'lessons.*.video_url' => 'required_if:lessons.*.is_recorded,true|max:102400',
            'lessons.*.video_description' => 'nullable',
            'lessons.*.is_online' => 'required_if:lessons.*.is_recorded,false',
            'lessons.*.venue' => 'required_if:lessons.*.is_online,0',
            'lessons.*.link' => 'required_if:lessons.*.is_online,1'
        ], [
            'lessons.*.title.required' => 'Lesson Title is required.',
            'lessons.*.start_at.required' => 'Lesson Start At is required.',
            'lessons.*.is_recorded.required' => 'Lesson Is Recorded is required.',
            'lessons.*.video_url.required_if' => 'Lesson Video is required when Lesson Is Recorded is ticked.',
            'lessons.*.video_url.max' => 'Lesson Video cannot be greater than 100mb.',
            'lessons.*.is_online.required_if' => 'Lesson Mode is required when Lesson Is Recorded is not ticked.',
            'lessons.*.venue.required_if' => 'Lesson Venue is required when Lesson Mode is offline.',
            'lessons.*.link.required_if' => 'Lesson Link is required when Lesson Mode is online.',
        ]);

        $this->course->update([
            'title' => $this->title,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'description' => $this->description,
        ]);

        if($this->image_url && !is_string($this->image_url)){
            $image_url = Storage::disk('public')->put('courses/'.$this->course->id.'/images', $this->image_url);
            $this->course->update([
                'image_url' => $image_url
            ]);
        }

        if(count($this->removed_lessons) > 0){
            foreach($this->removed_lessons as $lesson_id){
                $lesson = Lesson::find($lesson_id);

                $lesson->videos()->delete();
            }
            $this->course->lessons()->whereIn('id', $this->removed_lessons)->delete();
        }

        foreach ($this->lessons as $lesson) {
            if($lesson['id']) {
                $old_lesson = Lesson::find($lesson['id']);
                $old_lesson->update([
                    'title' => $lesson['title'],
                    'start_at' => $lesson['start_at'],
                    'end_at' => $lesson['end_at'],
                    'is_online' => $lesson['is_online'] ? 1 : 0,
                    'venue' => $lesson['venue'] ?: null,
                    'link' => $lesson['link'] ?: null
                ]);

                if($lesson['is_recorded']){
                    if($lesson['video_url'] && !is_string($lesson['video_url'])) {
                        $video_url = Storage::disk('public')->put('courses/' . $this->course->id . '/videos', $lesson['video_url']);

                        $old_lesson->videos()->updateOrCreate([
                            'lesson_id' => $old_lesson->id
                        ], [
                            'link' => $video_url,
                            'description' => $lesson['video_description']
                        ]);
                    }
                }
            }else{
                $new_lesson = $this->course->lessons()->create([
                    'title' => $lesson['title'],
                    'start_at' => $lesson['start_at'],
                    'end_at' => $lesson['end_at'],
                    'is_online' => $lesson['is_online'] ? 1 : 0,
                    'venue' => $lesson['venue'] ?: null,
                    'link' => $lesson['link'] ?: null
                ]);

                if($lesson['is_recorded']){
                    if($lesson['video_url'] && !is_string($lesson['video_url'])) {
                        $video_url = Storage::disk('public')->put('courses/' . $this->course->id . '/videos', $lesson['video_url']);

                        $new_lesson->videos()->create([
                            'link' => $video_url,
                            'description' => $lesson['video_description']
                        ]);
                    }
                }
            }
        }

        request()->session()->flash('success', 'Course created successfully.');

        return redirect()->route('educator.courses.index');
    }

    public function render()
    {
        return view('livewire.educator.courses.edit');
    }
}
