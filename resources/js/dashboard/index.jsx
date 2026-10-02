import React from "react";
import { createRoot } from "react-dom/client";
import { Provider } from "react-redux";
import { store } from "./store";
import { hydrate } from "./analyticsSlice";
import App from "./App";

const el = document.getElementById("dashboard-root");

if (el) {
    // dashboard.blade.php sets window.__DASHBOARD_DATA__ before this script
    // runs, via a <script> tag rendered from the controller's data.
    const initialData = window.__DASHBOARD_DATA__ || {};
    store.dispatch(hydrate(initialData));

    createRoot(el).render(
        <Provider store={store}>
            <App />
        </Provider>,
    );
}