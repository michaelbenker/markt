export default {
    content: [
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php", 
        "./resources/views/**/*.blade.php",
        "./resources/views/components/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
    safelist: [
        // Padding/Margin
        'p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'p-6', 'p-7', 'p-8', 'p-9', 'p-10',
        'm-1', 'm-2', 'm-3', 'm-4', 'm-5', 'm-6', 'm-7', 'm-8', 'm-9', 'm-10',
        // Width/Height
        'w-8', 'w-12', 'w-16', 'w-20', 'w-24', 'w-32', 'w-40', 'w-48',
        'h-8', 'h-12', 'h-16', 'h-20', 'h-24', 'h-32', 'h-40', 'h-48',
        // Max dimensions
        'max-w-[100vw]', 'max-h-[100vh]', 'max-w-[80vh]', 'max-h-[80vh]',
        // Grid
        'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4', 'grid-cols-5', 'grid-cols-6',
        'lg:grid-cols-6', 'md:grid-cols-4', 'sm:grid-cols-2',
        // Object fit
        'object-contain', 'object-cover', 'object-fill', 'object-none', 'object-scale-down',
        // Flex
        'flex-wrap', 'flex-nowrap', 'flex-col', 'flex-row',
        // Gap
        'gap-1', 'gap-2', 'gap-3', 'gap-4', 'gap-5', 'gap-6',
    ],
};
