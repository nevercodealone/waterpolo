const {content} = require("tailwindcss/lib/plugins");
module.exports = {
    purge: {
        enabled: true,
        content: ['./templates/**/*.twig'],
    },
    theme: {
        extend: {}
    },
    variants: {},
    plugins: [
        require('@tailwindcss/aspect-ratio'),
    ],
}
