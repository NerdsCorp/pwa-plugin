<?php

declare(strict_types=1);

// PHPStan bootstrap file - provides stubs for Laravel/Filament classes
// These classes will be available at runtime when the plugin is loaded

namespace Illuminate\Routing {
    class Controller {}
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
        public function getSettingsForm(): array;

        public function saveSettings(array $data): void;
    }
}
