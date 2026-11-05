<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTweetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tweet' => ['required_without:file', 'nullable', 'string', 'max:280'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm', 'max:51200'],
            'parent_id' => ['nullable', 'exists:tweets,id'],
            'quote_of_id' => ['nullable', 'exists:tweets,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('file')) {
                $file = $this->file('file');
                $ext = strtolower($file->getClientOriginalExtension());
                $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                // 5MB limit for images (5120 KB)
                if (in_array($ext, $imageExts) && $file->getSize() > 5120 * 1024) {
                    $validator->errors()->add('file', 'Image size cannot exceed 5MB.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tweet.required_without' => 'Please provide some text or a media file for your tweet.',
            'tweet.max' => 'Your tweet cannot exceed 280 characters.',
            'file.mimes' => 'The file must be an image (jpg, jpeg, png, webp, gif) or video (mp4, mov, webm).',
            'file.max' => 'The uploaded file must not be larger than 50MB.',
        ];
    }
}
