<?php

/**
 * Analysis-only stubs for editor diagnostics in mixed workspace setup.
 * This file is not loaded by Laravel runtime.
 */

namespace Illuminate\View {
    class View {
        public function __construct(...$args) {}
    }
}

namespace Illuminate\Http {
    class RedirectResponse {}

    class Request {
        public function validate(array $rules): array
        {
            return [];
        }

        public function only(...$keys): array
        {
            return [];
        }

        public function session()
        {
            return new class {
                public function put($key, $value): void {}
                public function flush(): void {}
                public function regenerateToken(): void {}
            };
        }
    }
}

namespace Illuminate\Console\Scheduling {
    class Schedule {}
}

namespace Illuminate\Support\Facades {
    class DB {
        public static function table(string $table)
        {
            return new class {
                public function where(string $column, $value)
                {
                    return $this;
                }

                public function first()
                {
                    return null;
                }
            };
        }
    }
}

namespace Illuminate\Foundation\Exceptions {
    class Handler {
        protected function reportable(callable $callback): void {}
    }
}

namespace Illuminate\Foundation\Console {
    class Kernel {
        protected function load(string $path): void {}
    }
}

namespace {
    function session()
    {
        return new class {
            public function has(string $key): bool
            {
                return false;
            }
        };
    }

    function redirect()
    {
        return new class {
            public function route(string $name)
            {
                return $this;
            }
        };
    }

    function view(string $name)
    {
        return new \Illuminate\View\View();
    }

    function back()
    {
        return new class {
            public function withErrors(array $errors)
            {
                return $this;
            }

            public function withInput(array $input)
            {
                return $this;
            }
        };
    }

    function base_path(string $path = ''): string
    {
        return $path;
    }
}

namespace App\Http\Controllers {
    function session()
    {
        return \session();
    }

    function redirect()
    {
        return \redirect();
    }

    function view(string $name)
    {
        return \view($name);
    }

    function back()
    {
        return \back();
    }
}

namespace App\Console {
    function base_path(string $path = ''): string
    {
        return \base_path($path);
    }
}
