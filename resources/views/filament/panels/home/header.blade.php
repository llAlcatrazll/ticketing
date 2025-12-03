<header id="header" class="group">
    <nav class="fixed z-20 w-full overflow-hidden border-b border-gray-100 dark:border-gray-900 backdrop-blur-2xl">
        <div class="max-w-6xl px-6 m-auto ">
            <div class="flex flex-wrap items-center justify-between py-2 sm:py-4">
                <div class="flex items-center justify-between w-full lg:w-auto">
                    <a href="/">
                        @include('banner')
                    </a>
                    <div class="flex lg:hidden">
                        <button id="menu-btn" aria-label="open menu" class="btn variant-ghost sz-md icon-only relative z-20 -mr-2.5 block cursor-pointer lg:hidden">
                            <svg class="text-gray-950 dark:text-gray-50 m-auto size-6 transition-[transform,opacity] duration-300 group-data-[state=active]:rotate-180 group-data-[state=active]:scale-0 group-data-[state=active]:opacity-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"></path>
                            </svg>
                            <svg class="text-gray-950 dark:text-gray-50 absolute inset-0 m-auto size-6 -rotate-180 scale-0 opacity-0 transition-[transform,opacity] duration-300 group-data-[state=active]:rotate-0 group-data-[state=active]:scale-100 group-data-[state=active]:opacity-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="w-full group-data-[state=active]:h-fit h-0 lg:w-fit flex-wrap justify-end items-center space-y-8 lg:space-y-0 lg:flex lg:h-fit md:flex-nowrap">
                    <div class="mt-6 dark:text-gray-200 md:-ml-4 lg:pr-4 lg:mt-0">
                        @include('theme-switcher')
                    </div>
                    <div class="flex flex-col items-center w-full gap-2 pt-6 pb-4 space-y-2 border-t lg:pb-0 lg:flex-row lg:space-y-0 lg:w-fit lg:border-l lg:border-t-0 lg:pt-0 lg:pl-2 dark:border-gray-800">
                        @guest
                            <x-filament::button tag="a" href="{{ route('filament.auth.auth.login') }}" class="lg:ml-2" outlined>
                                Login
                            </x-filament::button>

                            <x-filament::button tag="a" href="{{ route('filament.auth.auth.register') }}" >
                                Sign up
                            </x-filament::button>
                        @else
                            <x-filament::button tag="a" href="{{ route('filament.auth.auth.register') }}" >
                                Continue
                            </x-filament::button>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
