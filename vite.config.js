import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/main.jsx",
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],

    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,

        hmr: {
            host: "localhost",
            port: 5173,
        },

        proxy: {
            "/api": {
                target: "http://127.0.0.1:8000",
                changeOrigin: true,
                secure: false,
            },
        },

        watch: {
            ignored: [
                "**/storage/framework/views/**",
            ],
        },
    },

    preview: {
        host: "0.0.0.0",
        port: 4173,
    },
});