import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import './bootstrap';

const appElement = document.getElementById('app');

if (appElement) {
    createRoot(appElement).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>,
    );
}
