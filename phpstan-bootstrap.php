<?php

declare(strict_types=1);

// PHPStan bootstrap file - provides stubs for Laravel, Filament, and Pelican classes
// These classes will be available at runtime when the plugin is loaded

namespace Illuminate\Routing {
    class Controller {}
}

namespace Illuminate\Foundation\Http {
    class FormRequest
    {
        public function validated(): array {}
    }
}

namespace Illuminate\Database\Eloquent {
    class Model
    {
        public static function query() {}

        public function morphTo() {}

        public function delete() {}

        public function getKey() {}
    }
}

namespace Illuminate\Support {
    class ServiceProvider
    {
        protected function loadMigrationsFrom(string $path) {}

        protected function loadViewsFrom(string $path, string $namespace) {}
    }
}

namespace Illuminate\Foundation\Support\Providers {
    use Illuminate\Support\ServiceProvider;

    class RouteServiceProvider extends ServiceProvider
    {
        protected function routes(callable $callback) {}
    }
}

namespace Illuminate\Contracts\Queue {
    interface ShouldQueue {}
}

namespace Illuminate\Foundation\Bus {
    trait Dispatchable
    {
        public static function dispatch(mixed ...$arguments) {}
    }
}

namespace Illuminate\Queue {
    trait InteractsWithQueue {}

    trait SerializesModels {}
}

namespace Illuminate\Bus {
    trait Queueable {}
}

namespace Filament\Pages {
    class Page {}
}

namespace Filament\Schemas\Contracts {
    interface HasSchemas {}
}

namespace Filament\Forms\Concerns {
    trait InteractsWithForms {}
}

namespace Filament\Contracts {
    interface Plugin {}
}

namespace App\Contracts\Plugins {
    interface HasPluginSettings
    {
        public function getSettingsFormData(): array;

        public function getSettingsForm(): array;

        public function saveSettings(array $data): void;
    }
}

namespace App\Traits {
    trait EnvironmentWriterTrait
    {
        public function writeToEnvironment(array $values = []): void {}
    }
}

namespace App\Models {
    use Illuminate\Database\Eloquent\Model;

    class User extends Model
    {
        public const RESOURCE_NAME = 'users';
    }
}

namespace App\Services\Acl\Api {
    class AdminAcl
    {
        public const WRITE = 2;
    }
}

namespace App\Http\Requests\Api\Application {
    use Illuminate\Foundation\Http\FormRequest;

    abstract class ApplicationApiRequest extends FormRequest
    {
        protected ?string $resource;
        protected int $permission = 0;

        public function rules(): array {}
    }
}
