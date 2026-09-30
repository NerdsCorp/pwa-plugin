<?php

declare(strict_types=1);

namespace PwaPlugin\Http\Requests;

use App\Http\Requests\Api\Application\ApplicationApiRequest;
use App\Models\User;
use App\Services\Acl\Api\AdminAcl;

class SendPwaNotificationRequest extends ApplicationApiRequest
{
    protected ?string $resource = User::RESOURCE_NAME;

    protected int $permission = AdminAcl::WRITE;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:300'],
            'url' => [
                'sometimes',
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    if (
                        !is_string($value)
                        || !str_starts_with($value, '/')
                        || str_starts_with($value, '//')
                        || str_contains($value, '\\')
                        || preg_match('/[\x00-\x1F\x7F]/', $value)
                    ) {
                        $fail('The notification URL must be a relative path on this panel.');
                    }
                },
            ],
            'icon' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'badge' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'tag' => ['sometimes', 'nullable', 'string', 'max:255'],
            'require_interaction' => ['sometimes', 'boolean'],
        ];
    }
}
