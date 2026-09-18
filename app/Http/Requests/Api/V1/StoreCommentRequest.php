<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->parent_id) {
                $parentComment = Comment::find($this->parent_id);
                if ($parentComment && $parentComment->post_id !== $this->route('post')->id) {
                    $validator->errors()->add('parent_id', 'The parent comment does not belong to this post.');
                }
            }
        });
    }
}
