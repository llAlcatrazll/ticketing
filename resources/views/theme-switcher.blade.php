<div
    x-data="{ theme: null }"
    x-init="
        $watch('theme', () => {
            $dispatch('theme-changed', theme)
        })

        theme = localStorage.getItem('theme') || @js(filament()->getDefaultThemeMode()->value)
    "
    class="grid grid-flow-col fi-theme-switcher gap-x-1"
>
    <button
        aria-label="{{ __("filament-panels::layout.actions.theme_switcher.light.label") }}"
        type="button"
        x-on:click="(theme = @js('light'))"
        x-tooltip="{
            content: @js('light'),
            theme: $store.theme,
        }"
        class="flex justify-center p-2 transition duration-75 rounded-md outline-none fi-theme-switcher-btn hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
        x-bind:class="
            theme === @js('light')
                ? 'fi-active bg-gray-50 text-primary-500 dark:bg-white/5 dark:text-primary-400'
                : 'text-gray-400 hover:text-gray-500 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:text-gray-400'
        "
    >
        <x-filament::icon
            :alias="'panels::theme-switcher.' . 'light' . '-button'"
            icon="heroicon-m-sun"
            class="w-5 h-5"
        />
    </button>

    <button
        aria-label="{{ __("filament-panels::layout.actions.theme_switcher.dark.label") }}"
        type="button"
        x-on:click="(theme = @js('dark'))"
        x-tooltip="{
            content: @js('dark'),
            theme: $store.theme,
        }"
        class="flex justify-center p-2 transition duration-75 rounded-md outline-none fi-theme-switcher-btn hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
        x-bind:class="
            theme === @js('dark')
                ? 'fi-active bg-gray-50 text-primary-500 dark:bg-white/5 dark:text-primary-400'
                : 'text-gray-400 hover:text-gray-500 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:text-gray-400'
        "
    >
        <x-filament::icon
            :alias="'panels::theme-switcher.' . 'dark' . '-button'"
            icon="heroicon-m-moon"
            class="w-5 h-5"
        />
    </button>

    <button
        aria-label="{{ __("filament-panels::layout.actions.theme_switcher.system.label") }}"
        type="button"
        x-on:click="(theme = @js('system'))"
        x-tooltip="{
            content: @js('system'),
            theme: $store.theme,
        }"
        class="flex justify-center p-2 transition duration-75 rounded-md outline-none fi-theme-switcher-btn hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5"
        x-bind:class="
            theme === @js('system')
                ? 'fi-active bg-gray-50 text-primary-500 dark:bg-white/5 dark:text-primary-400'
                : 'text-gray-400 hover:text-gray-500 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:text-gray-400'
        "
    >
        <x-filament::icon
            :alias="'panels::theme-switcher.' . 'system' . '-button'"
            icon="heroicon-m-computer-desktop"
            class="w-5 h-5"
        />
    </button>
</div>
