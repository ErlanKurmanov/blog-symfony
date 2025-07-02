import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import inject from "@rollup/plugin-inject";


/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        /* react(), // if you're using React */
        inject({
            $: 'jquery',
            jQuery: 'jquery',
        }),
        symfonyPlugin(),
    ],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js",
                // react: "./assets/react.js",
                reactions: "./assets/reactions-jquery.js",
                infiniteScroll: "./assets/infiniteScroll.js",
                follow: "./assets/follow.js"

            },
        }
    },
});
