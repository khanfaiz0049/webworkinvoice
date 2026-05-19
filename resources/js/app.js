import './bootstrap';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import ApexCharts from 'apexcharts';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.lucide = { createIcons, icons };

// Initialize Lucide Icons
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

Alpine.start();
