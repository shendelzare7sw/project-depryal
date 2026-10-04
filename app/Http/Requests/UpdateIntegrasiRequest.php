<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notifikasi_email' => $this->boolean('notifikasi_email'),
            'hapus_telegram_bot_token' => $this->boolean('hapus_telegram_bot_token'),
            'hapus_smtp_password' => $this->boolean('hapus_smtp_password'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'telegram_bot_token' => ['nullable', 'string', 'max:100', 'regex:/^\d{5,}:[A-Za-z0-9_-]{30,}$/'],
            'hapus_telegram_bot_token' => ['boolean'],
            'notifikasi_email' => ['boolean'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'required_with:smtp_host', 'integer', 'between:1,65535'],
            'smtp_enkripsi' => ['nullable', 'in:tls,ssl'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'hapus_smtp_password' => ['boolean'],
            'smtp_dari_alamat' => ['nullable', 'required_with:smtp_host', 'email', 'max:255'],
            'smtp_dari_nama' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['telegram_bot_token.regex' => 'Format token bot tidak sesuai (contoh: 123456789:AAH...). Salin utuh dari @BotFather.'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'telegram_bot_token' => 'token bot Telegram', 'smtp_host' => 'host SMTP', 'smtp_port' => 'port SMTP',
            'smtp_enkripsi' => 'enkripsi', 'smtp_username' => 'username SMTP', 'smtp_password' => 'kata sandi SMTP',
            'smtp_dari_alamat' => 'alamat pengirim', 'smtp_dari_nama' => 'nama pengirim',
        ];
    }
}
