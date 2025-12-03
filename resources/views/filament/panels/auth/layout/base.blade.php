<div class="relative flex items-center justify-center min-h-screen">
    <div class="relative z-10 grid w-full max-w-5xl p-10 mx-auto overflow-hidden bg-white dark:bg-gray-900 lg:rounded-2xl max-w-{{ $this->getMaxWidth() ?? 'lg' }}" >
        <section class="grid auto-cols-fr gap-y-6">
            <header class="flex flex-col items-center fi-simple-header">
                <div class="flex justify-end w-full">
                    @include('theme-switcher')
                </div>

                <a href="/">
                    @include('banner')
                </a>

                <h1 class="text-3xl font-bold tracking-tight text-center fi-simple-header-heading text-gray-950 dark:text-white">
                    @yield('heading', $this->getHeading())
                </h1>

                <p class="mt-2 text-sm text-center text-gray-500 fi-simple-header-subheading dark:text-gray-400">
                    @yield('subheading', $this->getSubHeading())
                </p>
            </header>

            @yield('content')

            @hasSection('footer')
                <footer>
                    @yield('footer')
                </footer>
            @endif
        </section>
    </div>
    <x-filament-actions::modals />
</div>
