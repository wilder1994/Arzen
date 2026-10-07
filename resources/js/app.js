import './theme';
import './bootstrap';
import './dashboard';
import './realtime-portfolio-sync';
import './realtime-weapons-sync';
import './realtime-posts-workers-sync';
import './reports-incidents';
import './nav-notifications-realtime';
import { initAssignmentComboboxes } from './assignment-combobox';

document.addEventListener('DOMContentLoaded', () => {
    initAssignmentComboboxes();

    const vestForm = document.querySelector('[data-vest-form]');
    if (vestForm) {
        import('./vest-form.js').then(({ initVestForm }) => {
            initVestForm(vestForm);
        });
    }

    const vestFormPhotos = document.querySelector('[data-vest-form-photos]');
    if (vestFormPhotos) {
        import('./vest-form-photos.js').then(({ initVestFormPhotos }) => {
            initVestFormPhotos(vestFormPhotos);
        });
    }

    const alertsPage = document.querySelector('[data-alerts-page]');
    if (alertsPage) {
        import('./alerts-documents-modal.js').then(({ initAlertsDocumentsPage }) => {
            initAlertsDocumentsPage(alertsPage);
        });
    }
});

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
