<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                function ($attribute, $value, $fail) {
                    // Verificar que el nombre de usuario no exista con ningún dominio
                    $username = explode('@', $value)[0];
                    if (User::where('email', 'LIKE', $username . '@%')->where('id', '!=', $this->user()->id)->exists()) {
                        $fail('El nombre de usuario ya está en uso.');
                    }
                },
            ],
        ];
    }
}
